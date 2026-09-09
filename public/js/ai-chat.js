(function () {
    'use strict';

    const widget = document.querySelector('[data-ai-widget]');
    if (!widget) return;

    const panel = widget.querySelector('[data-ai-panel]');
    const openButton = widget.querySelector('[data-ai-open]');
    const closeButton = widget.querySelector('[data-ai-close]');
    const minimizeButton = widget.querySelector('[data-ai-minimize]');
    const form = widget.querySelector('[data-ai-form]');
    const input = widget.querySelector('[data-ai-input]');
    const sendButton = widget.querySelector('[data-ai-send]');
    const messages = widget.querySelector('[data-ai-messages]');
    const welcome = widget.querySelector('[data-ai-welcome]');
    const endpoint = widget.dataset.aiEndpoint || '/ai/chat';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const conversationHistory = [];
    let isRequesting = false;

    function setOpen(isOpen) {
        widget.classList.toggle('is-open', isOpen);
        panel.setAttribute('aria-hidden', String(!isOpen));
        openButton.setAttribute('aria-expanded', String(isOpen));
        openButton.setAttribute('aria-label', isOpen ? 'AI Assistant sedang terbuka' : 'Buka AI Assistant');
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
        wrapper.className = `simgos-ai-message ${type}`;
        if (type === 'ai') {
            wrapper.innerHTML = `<div class="simgos-ai-message-avatar" aria-hidden="true"><i class="fa-solid fa-robot"></i></div><div><div class="simgos-ai-bubble"></div><div class="simgos-ai-message-meta">AI Assistant · baru saja</div></div>`;
            wrapper.querySelector('.simgos-ai-bubble').innerHTML = formatAiText(content);
        } else {
            wrapper.innerHTML = `<div><div class="simgos-ai-bubble"></div><div class="simgos-ai-message-meta">Anda · baru saja</div></div>`;
            wrapper.querySelector('.simgos-ai-bubble').textContent = content;
        }
        messages.appendChild(wrapper);
        scrollMessages();
    }

    function addSuggestions(items) {
        if (!items || !items.length) return;
        const title = document.createElement('div');
        title.className = 'simgos-ai-suggestion-title';
        title.textContent = 'Pertanyaan lain';
        const container = document.createElement('div');
        container.className = 'simgos-ai-suggestions';
        items.forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'simgos-ai-suggestion';
            button.dataset.aiQuestion = item;
            button.textContent = item;
            container.appendChild(button);
        });
        messages.append(title, container);
    }

    function addTyping() {
        const typing = document.createElement('div');
        typing.className = 'simgos-ai-typing';
        typing.dataset.aiTyping = 'true';
        typing.innerHTML = '<div class="simgos-ai-message-avatar" aria-hidden="true"><i class="fa-solid fa-robot"></i></div><div><div class="simgos-ai-typing-bubble"><span></span><span></span><span></span></div><div class="simgos-ai-typing-label">AI Assistant sedang mengetik...</div></div>';
        messages.appendChild(typing);
        scrollMessages();
        return typing;
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value;
        return element.innerHTML;
    }

    function formatAiText(value) {
        return escapeHtml(String(value || '')).replace(/\n/g, '<br>');
    }

    async function sendToGemini(question) {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                message: question,
                history: conversationHistory.slice(-12),
            }),
        });

        let result = {};
        try {
            result = await response.json();
        } catch (error) {
            throw new Error('Respons server tidak valid.');
        }

        if (!response.ok || !result.ok) {
            throw new Error(result.message || 'AI tidak dapat memberikan jawaban.');
        }

        conversationHistory.push(
            { role: 'user', content: question },
            { role: 'assistant', content: result.answer }
        );

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
            const answer = await sendToGemini(cleanQuestion);
            typing.remove();
            addMessage('ai', answer);
            addSuggestions([
                'Lihat kunjungan per poli',
                'Bandingkan dengan periode sebelumnya',
            ]);
        } catch (error) {
            typing.remove();
            addMessage('ai', 'Maaf, AI sedang tidak dapat diakses. Silakan coba lagi.');
            console.error('AI chat error:', error);
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
        const questionButton = event.target.closest('[data-ai-question]');
        if (questionButton) submitQuestion(questionButton.dataset.aiQuestion || questionButton.textContent);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && widget.classList.contains('is-open')) setOpen(false);
    });
})();
