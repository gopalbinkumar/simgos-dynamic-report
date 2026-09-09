<div class="simgos-ai" data-ai-widget data-ai-endpoint="{{ route('ai.chat') }}">
    <button type="button" class="simgos-ai-fab" data-ai-open aria-label="Buka SIMGOS AI Assistant" aria-expanded="false">
        <span class="simgos-ai-fab-sparkle" aria-hidden="true"><i class="fa-regular fa-comment"></i></span>
        <span class="simgos-ai-tooltip" role="tooltip">Tanya AI</span>
    </button>

    <section class="simgos-ai-panel" data-ai-panel aria-label="SIMGOS AI Assistant" aria-hidden="true">
        <header class="simgos-ai-header">
            <div class="simgos-ai-header-identity">
                <div class="simgos-ai-avatar simgos-ai-avatar-header" aria-hidden="true"><i
                        class="fa-regular fa-comment"></i></div>
                <div>
                    <h2>AI Assistant</h2>
                    <span class="simgos-ai-status"><span></span> Online</span>
                </div>
            </div>
            <div class="simgos-ai-header-actions">
                <button type="button" class="simgos-ai-icon-button" data-ai-minimize
                    aria-label="Minimalkan SIMGOS AI Assistant" style="display: none"><i
                        class="fa-solid fa-minus"></i></button>
                <button type="button" class="simgos-ai-icon-button" data-ai-close
                    aria-label="Tutup SIMGOS AI Assistant"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </header>

        <div class="simgos-ai-messages" data-ai-messages aria-live="polite" aria-atomic="false">
            <div class="simgos-ai-welcome" data-ai-welcome>
                <h3>Asisten analitik siap membantu</h3>
                <p>Tanyakan tentang kunjungan, pelayanan, keuangan, atau indikator rumah sakit.</p>
                <span class="simgos-ai-prompt-label">Apa yang ingin Anda ketahui?</span>
                <div class="simgos-ai-welcome-actions">
                    <button type="button" class="simgos-ai-quick-button"
                        data-ai-question="Berapa jumlah kunjungan bulan ini?"><i class="fa-solid fa-chart-column"></i>
                        Ringkasan kunjungan</button>
                    <button type="button" class="simgos-ai-quick-button"
                        data-ai-question="Poli mana yang paling banyak dikunjungi?"><i class="fa-solid fa-hospital"></i>
                        Kunjungan per poli</button>
                    <button type="button" class="simgos-ai-quick-button"
                        data-ai-question="Berikan ringkasan indikator rumah sakit."><i
                            class="fa-solid fa-chart-line"></i> Statistik & indikator</button>
                    <button type="button" class="simgos-ai-quick-button"
                        data-ai-question="Bagaimana tren kunjungan bulan ini?"><i
                            class="fa-solid fa-arrow-trend-up"></i> Tren kunjungan</button>
                </div>
            </div>
        </div>

        <div class="simgos-ai-composer-area">
            <div class="simgos-ai-quick-strip" data-ai-quick-strip aria-label="Pertanyaan cepat">
                <button type="button" class="simgos-ai-chip" data-ai-question="Berapa kunjungan hari ini?">Kunjungan
                    hari ini</button>
                <button type="button" class="simgos-ai-chip" data-ai-question="Poli mana yang paling ramai?">Poli
                    teramai</button>
                <button type="button" class="simgos-ai-chip" data-ai-question="Bagaimana tren bulan ini?">Tren bulan
                    ini</button>
                <button type="button" class="simgos-ai-chip" data-ai-question="Berikan ringkasan pendapatan bulan ini.">Ringkasan
                    keuangan</button>
            </div>
            <form class="simgos-ai-composer" data-ai-form novalidate>
                <label class="simgos-ai-sr-only" for="simgos-ai-input">Tulis pertanyaan untuk SIMGOS AI</label>
                <input id="simgos-ai-input" type="text" data-ai-input autocomplete="off"
                    placeholder="Tulis pertanyaan..." maxlength="500">
                <button type="submit" class="simgos-ai-send" data-ai-send aria-label="Kirim pertanyaan" disabled><i
                        class="fa-solid fa-arrow-up"></i></button>
            </form>
            <div class="simgos-ai-disclaimer"><i class="fa-solid fa-shield-halved"></i> Respons AI melalui server aplikasi</div>
        </div>
    </section>
</div>
