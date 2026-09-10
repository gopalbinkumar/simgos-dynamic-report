<div class="simgos-chatbot" data-chatbot-widget data-chatbot-endpoint="{{ route('chatbot.send') }}">
    <button type="button" class="simgos-chatbot-fab" data-chatbot-open aria-label="Buka SIMGOS Chatbot" aria-expanded="false">
        <span class="simgos-chatbot-fab-sparkle" aria-hidden="true"><i class="fa-regular fa-comment"></i></span>
        <span class="simgos-chatbot-tooltip" role="tooltip">Tanya Chatbot</span>
    </button>

    <section class="simgos-chatbot-panel" data-chatbot-panel aria-label="SIMGOS Chatbot" aria-hidden="true">
        <header class="simgos-chatbot-header">
            <div class="simgos-chatbot-header-identity">
                <div class="simgos-chatbot-avatar simgos-chatbot-avatar-header" aria-hidden="true"><i
                        class="fa-regular fa-comment"></i></div>
                <div>
                    <h2>Chatbot</h2>
                    <span class="simgos-chatbot-status"><span></span> Online</span>
                </div>Hk
            </div>
            <div class="simgos-chatbot-header-actions">
                <button type="button" class="simgos-chatbot-icon-button" data-chatbot-minimize
                    aria-label="Minimalkan SIMGOS Chatbot" style="display: none"><i
                        class="fa-solid fa-minus"></i></button>
                <button type="button" class="simgos-chatbot-icon-button" data-chatbot-close
                    aria-label="Tutup SIMGOS Chatbot"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </header>

        <div class="simgos-chatbot-messages" data-chatbot-messages aria-live="polite" aria-atomic="false">
            <div class="simgos-chatbot-welcome" data-chatbot-welcome>
                <h3>Asisten analitik siap membantu</h3>
                <p>Tanyakan tentang kunjungan, pelayanan, keuangan, atau indikator rumah sakit.</p>
                <span class="simgos-chatbot-prompt-label">Apa yang ingin Anda ketahui?</span>
                <div class="simgos-chatbot-welcome-actions">
                    <button type="button" class="simgos-chatbot-quick-button"
                        data-chatbot-question="Berapa jumlah kunjungan bulan ini?"><i class="fa-solid fa-chart-column"></i>
                        Ringkasan kunjungan</button>
                    <button type="button" class="simgos-chatbot-quick-button"
                        data-chatbot-question="Poli mana yang paling banyak dikunjungi?"><i class="fa-solid fa-hospital"></i>
                        Kunjungan per poli</button>
                    <button type="button" class="simgos-chatbot-quick-button"
                        data-chatbot-question="Berikan ringkasan indikator rumah sakit."><i
                            class="fa-solid fa-chart-line"></i> Statistik & indikator</button>
                    <button type="button" class="simgos-chatbot-quick-button"
                        data-chatbot-question="Bagaimana tren kunjungan bulan ini?"><i
                            class="fa-solid fa-arrow-trend-up"></i> Tren kunjungan</button>
                </div>
            </div>
        </div>

        <div class="simgos-chatbot-composer-area">
            <div class="simgos-chatbot-quick-strip" data-chatbot-quick-strip aria-label="Pertanyaan cepat">
                <button type="button" class="simgos-chatbot-chip" data-chatbot-question="Berapa kunjungan hari ini?">Kunjungan
                    hari ini</button>
                <button type="button" class="simgos-chatbot-chip" data-chatbot-question="Poli mana yang paling ramai?">Poli
                    teramai</button>
                <button type="button" class="simgos-chatbot-chip" data-chatbot-question="Bagaimana tren bulan ini?">Tren bulan
                    ini</button>
                <button type="button" class="simgos-chatbot-chip" data-chatbot-question="Berikan ringkasan pendapatan bulan ini.">Ringkasan
                    keuangan</button>
            </div>
            <form class="simgos-chatbot-composer" data-chatbot-form novalidate>
                <label class="simgos-chatbot-sr-only" for="simgos-chatbot-input">Tulis pertanyaan untuk SIMGOS Chatbot</label>
                <input id="simgos-chatbot-input" type="text" data-chatbot-input autocomplete="off"
                    placeholder="Tulis pertanyaan..." maxlength="500">
                <button type="submit" class="simgos-chatbot-send" data-chatbot-send aria-label="Kirim pertanyaan" disabled><i
                        class="fa-solid fa-arrow-up"></i></button>
            </form>
            <div class="simgos-chatbot-disclaimer"><i class="fa-solid fa-shield-halved"></i> Respons berbasis data melalui server aplikasi</div>
        </div>
    </section>
</div>
