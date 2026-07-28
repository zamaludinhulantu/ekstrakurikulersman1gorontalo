<div class="network-status-banner" id="networkStatusBanner" hidden>
    <span class="network-status-banner__text" id="networkStatusText">Anda sedang offline.</span>
    <button type="button" class="btn btn-light btn-sm" id="networkRetryButton">Coba lagi</button>
</div>

<div class="pwa-toast-stack">
    <div class="pwa-toast" id="installPromptCard" hidden>
        <div>
            <strong>Pasang aplikasi</strong>
            <div class="small text-muted">Simpan aplikasi ke layar utama untuk akses yang lebih cepat.</div>
        </div>
        <div class="pwa-toast__actions">
            <button type="button" class="btn btn-primary btn-sm" id="installAppButton">Pasang Aplikasi</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="dismissInstallButton">Nanti</button>
        </div>
    </div>

    <div class="pwa-toast" id="iosInstallCard" hidden>
        <div>
            <strong>Pasang di iPhone / iPad</strong>
            <div class="small text-muted">Buka menu Bagikan lalu pilih Tambahkan ke Layar Utama.</div>
        </div>
        <div class="pwa-toast__actions">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="dismissIosInstallButton">Tutup</button>
        </div>
    </div>

    <div class="pwa-toast" id="appUpdateCard" hidden>
        <div>
            <strong>Versi baru aplikasi tersedia.</strong>
            <div class="small text-muted">Perbarui sekarang untuk memakai aset dan fitur terbaru.</div>
        </div>
        <div class="pwa-toast__actions">
            <button type="button" class="btn btn-primary btn-sm" id="refreshAppButton">Perbarui sekarang</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="dismissAppUpdateButton">Nanti</button>
        </div>
    </div>
</div>
