(function () {
    'use strict';

    const widget = document.querySelector('[data-chatbot-widget]');
    if (!widget) return;

    const panel = widget.querySelector('[data-chatbot-panel]');
    const openButton = widget.querySelector('[data-chatbot-open]');
    const closeButton = widget.querySelector('[data-chatbot-close]');
    const minimizeButton = widget.querySelector('[data-chatbot-minimize]');
    const form = widget.querySelector('[data-chatbot-form]');
    const input = widget.querySelector('[data-chatbot-input]');
    const sendButton = widget.querySelector('[data-chatbot-send]');
    const messages = widget.querySelector('[data-chatbot-messages]');
    const quickStrip = widget.querySelector('[data-chatbot-quick-strip]');
    const welcome = widget.querySelector('[data-chatbot-welcome]');
    const endpoint = widget.dataset.chatbotEndpoint || '/chatbot';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const historyStorageKey = 'simgos-chatbot-history-v1';
    const maxHistoryMessages = 100;
    let conversation = loadHistory();
    let isRequesting = false;

    function setOpen(isOpen) {
        widget.classList.toggle('is-open', isOpen);
        panel.setAttribute('aria-hidden', String(!isOpen));
        openButton.setAttribute('aria-expanded', String(isOpen));
        openButton.setAttribute('aria-label', isOpen ? 'Chatbot sedang terbuka' : 'Buka Chatbot');
        if (isOpen) window.setTimeout(() => input.focus(), 200);
    }

    // Keep scrolling inside the widget from chaining to the main page.
    panel.addEventListener('wheel', (event) => {
        event.stopPropagation();
        if (!messages.contains(event.target) && !quickStrip?.contains(event.target)) {
            event.preventDefault();
        }
    }, { passive: false });

    messages.addEventListener('wheel', (event) => {
        event.stopPropagation();
    }, { passive: true });

    // Mouse wheel vertikal pada quick questions digeser menjadi scroll horizontal.
    quickStrip?.addEventListener('wheel', (event) => {
        if (Math.abs(event.deltaY) > Math.abs(event.deltaX)) {
            quickStrip.scrollLeft += event.deltaY;
            event.preventDefault();
        }
        event.stopPropagation();
    }, { passive: false });

    // Clicking outside an open widget closes it.
    document.addEventListener('pointerdown', (event) => {
        if (widget.classList.contains('is-open') && !widget.contains(event.target)) {
            setOpen(false);
        }
    });

    function scrollMessages() {
        messages.scrollTop = messages.scrollHeight;
    }

    function loadHistory() {
        try {
            const stored = JSON.parse(window.localStorage.getItem(historyStorageKey) || '[]');

            if (!Array.isArray(stored)) return [];

            return stored
                .filter((item) => item
                    && (item.role === 'user' || item.role === 'chatbot')
                    && typeof item.content === 'string'
                    && item.content.trim() !== '')
                .slice(-maxHistoryMessages);
        } catch (error) {
            // Storage may be disabled in private browsing or by browser policy.
            return [];
        }
    }

    function saveHistory() {
        try {
            window.localStorage.setItem(
                historyStorageKey,
                JSON.stringify(conversation.slice(-maxHistoryMessages))
            );
        } catch (error) {
            // The chatbot should continue working even when storage is unavailable.
        }
    }

    function addMessage(type, content, persist = true) {
        const messageContent = String(content || '');
        const wrapper = document.createElement('div');
        wrapper.className = `simgos-chatbot-message ${type}`;
        if (type === 'chatbot') {
            wrapper.innerHTML = `<div class="simgos-chatbot-message-avatar" aria-hidden="true"><i class="fa-solid fa-robot"></i></div><div><div class="simgos-chatbot-bubble"></div><div class="simgos-chatbot-message-meta">Chatbot · baru saja</div></div>`;
            wrapper.querySelector('.simgos-chatbot-bubble').innerHTML = formatChatbotText(messageContent);
        } else {
            wrapper.innerHTML = `<div><div class="simgos-chatbot-bubble"></div><div class="simgos-chatbot-message-meta">Anda · baru saja</div></div>`;
            wrapper.querySelector('.simgos-chatbot-bubble').textContent = messageContent;
        }
        messages.appendChild(wrapper);

        if (persist) {
            conversation.push({ role: type, content: messageContent });
            conversation = conversation.slice(-maxHistoryMessages);
            saveHistory();
        }

        scrollMessages();
    }

    function restoreHistory() {
        if (conversation.length === 0) return;

        welcome?.remove();
        conversation.forEach((item) => addMessage(item.role, item.content, false));
        scrollMessages();
    }

    function addTyping() {
        const typing = document.createElement('div');
        typing.className = 'simgos-chatbot-typing';
        typing.dataset.chatbotTyping = 'true';
        typing.innerHTML = '<div class="simgos-chatbot-message-avatar" aria-hidden="true"><i class="fa-solid fa-robot"></i></div><div><div class="simgos-chatbot-typing-bubble"><span></span><span></span><span></span></div><div class="simgos-chatbot-typing-label">Chatbot sedang mengetik...</div></div>';
        messages.appendChild(typing);
        scrollMessages();
        return typing;
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value;
        return element.innerHTML;
    }

    function formatChatbotText(value) {
        return escapeHtml(String(value || '')).replace(/\n/g, '<br>');
    }

    restoreHistory();

    async function sendMessage(question) {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                message: question,
            }),
        });

        let result = {};
        try {
            result = await response.json();
        } catch (error) {
            throw new Error('Respons server tidak valid.');
        }

        if (!response.ok || !result.ok) {
            throw new Error(result.message || 'Chatbot tidak dapat memberikan jawaban.');
        }

        return result.answer;
    }

    async function submitQuestion(question) {
        const cleanQuestion = question.trim();
        if (!cleanQuestion || isRequesting) return;

        isRequesting = true;
        welcome?.remove();
        addMessage('user', cleanQuestion);
        input.value = '';
        sendButton.disabled = true;
        const typing = addTyping();

        try {
            const answer = await sendMessage(cleanQuestion);
            typing.remove();
            addMessage('chatbot', answer);
        } catch (error) {
            typing.remove();
            addMessage('chatbot', 'Maaf, Chatbot sedang tidak dapat diakses. Silakan coba lagi.');
            console.error('Chatbot error:', error);
        } finally {
            isRequesting = false;
            sendButton.disabled = input.value.trim() === '';
            scrollMessages();
        }
    }

    openButton.addEventListener('click', () => setOpen(true));
    closeButton.addEventListener('click', () => setOpen(false));
    minimizeButton?.addEventListener('click', () => setOpen(false));
    input.addEventListener('input', () => { sendButton.disabled = input.value.trim() === ''; });
    form.addEventListener('submit', (event) => { event.preventDefault(); submitQuestion(input.value); });
    widget.addEventListener('click', (event) => {
        const questionButton = event.target.closest('[data-chatbot-question]');
        if (questionButton) submitQuestion(questionButton.dataset.chatbotQuestion || questionButton.textContent);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && widget.classList.contains('is-open')) setOpen(false);
    });
})();
