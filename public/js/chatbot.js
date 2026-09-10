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
    const welcome = widget.querySelector('[data-chatbot-welcome]');
    const endpoint = widget.dataset.chatbotEndpoint || '/chatbot';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
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
        if (!messages.contains(event.target)) event.preventDefault();
    }, { passive: false });

    messages.addEventListener('wheel', (event) => {
        event.stopPropagation();
    }, { passive: true });

    // Clicking outside an open widget closes it.
    document.addEventListener('pointerdown', (event) => {
        if (widget.classList.contains('is-open') && !widget.contains(event.target)) {
            setOpen(false);
        }
    });

    function scrollMessages() {
        messages.scrollTop = messages.scrollHeight;
    }

    function addMessage(type, content) {
        const wrapper = document.createElement('div');
        wrapper.className = `simgos-chatbot-message ${type}`;
        if (type === 'chatbot') {
            wrapper.innerHTML = `<div class="simgos-chatbot-message-avatar" aria-hidden="true"><i class="fa-solid fa-robot"></i></div><div><div class="simgos-chatbot-bubble"></div><div class="simgos-chatbot-message-meta">Chatbot · baru saja</div></div>`;
            wrapper.querySelector('.simgos-chatbot-bubble').innerHTML = formatChatbotText(content);
        } else {
            wrapper.innerHTML = `<div><div class="simgos-chatbot-bubble"></div><div class="simgos-chatbot-message-meta">Anda · baru saja</div></div>`;
            wrapper.querySelector('.simgos-chatbot-bubble').textContent = content;
        }
        messages.appendChild(wrapper);
        scrollMessages();
    }

    function addSuggestions(items) {
        if (!items || !items.length) return;
        const title = document.createElement('div');
        title.className = 'simgos-chatbot-suggestion-title';
        title.textContent = 'Pertanyaan lain';
        const container = document.createElement('div');
        container.className = 'simgos-chatbot-suggestions';
        items.forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'simgos-chatbot-suggestion';
            button.dataset.chatbotQuestion = item;
            button.textContent = item;
            container.appendChild(button);
        });
        messages.append(title, container);
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
            addSuggestions([
                'Lihat kunjungan per poli',
                'Bandingkan dengan periode sebelumnya',
            ]);
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
