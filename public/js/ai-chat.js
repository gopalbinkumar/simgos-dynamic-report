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

    const responses = [
        {
            match: ['kunjungan', 'bulan'],
            answer: 'Berdasarkan simulasi data SIMGOS, terdapat <strong>18.245 kunjungan</strong> pada periode bulan ini.',
            insight: { label: 'Total Kunjungan', value: '18.245', change: '8,4% dibanding periode sebelumnya' },
            suggestions: ['Lihat kunjungan per poli', 'Bandingkan bulan sebelumnya'],
        },
        {
            match: ['poli', 'ramai'],
            answer: 'Dalam simulasi laporan, <strong>Poli Penyakit Dalam</strong> memiliki jumlah kunjungan tertinggi pada periode yang dipilih.',
            suggestions: ['Berapa kunjungan Poli Jantung?', 'Lihat tren semua poli'],
        },
        {
            match: ['dokter'],
            answer: 'Saya dapat membantu membuat ringkasan dokter setelah sumber data dokter dihubungkan. Untuk saat ini, ini masih simulasi UI.',
            suggestions: ['Kembali ke ringkasan kunjungan', 'Lihat data per poli'],
        },
        {
            match: ['tren', 'hari', 'bulan'],
            answer: 'Tren simulasi menunjukkan aktivitas kunjungan meningkat secara bertahap pada minggu terakhir periode berjalan.',
            insight: { label: 'Perubahan Tren', value: '+8,4%', change: 'dibanding periode sebelumnya' },
            suggestions: ['Tampilkan ringkasan bulan ini', 'Poli mana yang paling ramai?'],
        },
    ];

    function setOpen(isOpen) {
        widget.classList.toggle('is-open', isOpen);
        panel.setAttribute('aria-hidden', String(!isOpen));
        openButton.setAttribute('aria-expanded', String(isOpen));
        openButton.setAttribute('aria-label', isOpen ? 'SIMGOS AI Assistant sedang terbuka' : 'Buka SIMGOS AI Assistant');
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
            wrapper.innerHTML = `<div class="simgos-ai-message-avatar" aria-hidden="true"><i class="fa-solid fa-wand-magic-sparkles"></i></div><div><div class="simgos-ai-bubble">${content}</div><div class="simgos-ai-message-meta">SIMGOS AI · baru saja</div></div>`;
        } else {
            wrapper.innerHTML = `<div><div class="simgos-ai-bubble"></div><div class="simgos-ai-message-meta">Anda · baru saja</div></div>`;
            wrapper.querySelector('.simgos-ai-bubble').textContent = content;
        }
        messages.appendChild(wrapper);
        scrollMessages();
    }

    function addInsight(insight) {
        const card = document.createElement('div');
        card.className = 'simgos-ai-insight';
        card.innerHTML = `<div class="simgos-ai-insight-label">${insight.label}</div><div class="simgos-ai-insight-value">${insight.value}</div><div class="simgos-ai-insight-change"><i class="fa-solid fa-arrow-trend-up"></i>${insight.change}</div>`;
        messages.appendChild(card);
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
        typing.innerHTML = '<div class="simgos-ai-message-avatar" aria-hidden="true"><i class="fa-solid fa-wand-magic-sparkles"></i></div><div><div class="simgos-ai-typing-bubble"><span></span><span></span><span></span></div><div class="simgos-ai-typing-label">SIMGOS AI sedang mengetik...</div></div>';
        messages.appendChild(typing);
        scrollMessages();
        return typing;
    }

    function getResponse(question) {
        const normalized = question.toLowerCase();
        return responses.find((item) => item.match.some((word) => normalized.includes(word))) || {
            answer: 'Saya memahami pertanyaan Anda. Pada tahap ini SIMGOS AI masih menggunakan respons simulasi, tetapi widget ini siap dihubungkan ke sumber data dan layanan AI pada tahap berikutnya.',
            suggestions: ['Berapa jumlah kunjungan bulan ini?', 'Poli mana yang paling banyak dikunjungi?'],
        };
    }

    function submitQuestion(question) {
        const cleanQuestion = question.trim();
        if (!cleanQuestion) return;
        welcome?.remove();
        addMessage('user', cleanQuestion);
        input.value = '';
        sendButton.disabled = true;
        const typing = addTyping();
        const response = getResponse(cleanQuestion);
        window.setTimeout(() => {
            typing.remove();
            addMessage('ai', response.answer);
            if (response.insight) addInsight(response.insight);
            addSuggestions(response.suggestions);
            sendButton.disabled = true;
            scrollMessages();
        }, 950);
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
