@once
    @push('styles')
        <style>
            .article-workspace {
                display: grid;
                gap: 0.9rem;
                min-width: 0;
            }

            .article-surface {
                border: 1px solid #dbe5f0;
                border-radius: 18px;
                background: rgba(255, 255, 255, 0.96);
                box-shadow: 0 10px 22px rgba(16, 35, 63, 0.05);
                min-width: 0;
                overflow: hidden;
            }

            .article-surface--padded {
                padding: 0.95rem;
            }

            .article-surface--soft {
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            }

            .article-tab-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.9rem;
                flex-wrap: wrap;
            }

            .article-tab-nav {
                display: inline-flex;
                flex-wrap: wrap;
                gap: 0.35rem;
                padding: 0.3rem;
                border-radius: 999px;
                border: 1px solid #dbe5f0;
                background: #eef4ff;
                max-width: 100%;
            }

            .article-tab-nav a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 42px;
                padding: 0.55rem 1rem;
                border-radius: 999px;
                color: #4b6580;
                font-weight: 800;
                text-decoration: none;
                text-align: center;
                white-space: nowrap;
                line-height: 1.2;
            }

            .article-tab-nav a.active {
                color: #fff;
                background: linear-gradient(135deg, #2f68ff 0%, #4d8aff 100%);
                box-shadow: 0 10px 20px rgba(47, 104, 255, 0.18);
            }

            .article-summary-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 0.85rem;
            }

            .article-summary-card {
                padding: 1rem 1.05rem;
                border: 1px solid #dbe5f0;
                border-radius: 16px;
                background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
                min-width: 0;
            }

            .article-summary-card .label {
                display: block;
                color: #6b7f95;
                font-size: 0.75rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .article-summary-card .value {
                display: block;
                margin-top: 0.4rem;
                color: #163252;
                font-size: 1.7rem;
                font-weight: 900;
                line-height: 1.05;
            }

            .article-summary-card .hint {
                display: block;
                margin-top: 0.35rem;
                color: #7b8ea4;
                font-size: 0.8rem;
            }

            .article-filter-card {
                padding: 0.95rem;
                border: 1px solid #dbe5f0;
                border-radius: 18px;
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            }

            .article-filter-grid {
                display: grid;
                grid-template-columns: minmax(0, 2.4fr) repeat(3, minmax(170px, 1fr)) repeat(2, minmax(150px, 0.9fr));
                gap: 0.85rem;
                align-items: end;
            }

            .article-filter-field {
                min-width: 0;
            }

            .article-filter-field label,
            .article-form-field label,
            .article-upload-field__label,
            .article-seo-card__label {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                margin-bottom: 0.42rem;
                color: #48607b;
                font-size: 0.82rem;
                font-weight: 800;
            }

            .article-filter-field .form-control,
            .article-filter-field .form-select,
            .article-filter-field .btn,
            .article-filter-actions .btn {
                min-width: 0;
                min-height: 48px;
                width: 100%;
            }

            .article-filter-actions {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
                align-self: end;
            }

            .article-filter-actions .btn {
                white-space: nowrap;
            }

            .article-filter-mobile-toggle {
                display: none;
            }

            .article-list-card__head,
            .article-editor-shell__head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.95rem 0.95rem 0;
                flex-wrap: wrap;
            }

            .article-list-card__head h2,
            .article-editor-shell__head h2 {
                margin: 0;
                color: #163252;
                font-size: 1.08rem;
                font-weight: 800;
            }

            .article-list-card__head p,
            .article-editor-shell__head p {
                margin: 0.25rem 0 0;
                color: #6b7f95;
                font-size: 0.84rem;
            }

            .article-table-wrap {
                min-width: 0;
            }

            .article-table-thumb,
            .article-card-thumb {
                width: 78px;
                height: 58px;
                border-radius: 12px;
                object-fit: cover;
                border: 1px solid #dbe5f0;
                background: linear-gradient(135deg, #edf4ff 0%, #d9e8ff 100%);
                display: block;
                flex-shrink: 0;
            }

            .article-placeholder-thumb {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #2f68ff;
                font-size: 1.1rem;
                font-weight: 800;
            }

            .article-title-cell {
                min-width: 0;
                display: grid;
                gap: 0.35rem;
            }

            .article-title-cell__title {
                color: #163252;
                font-weight: 800;
                line-height: 1.35;
            }

            .article-title-cell__excerpt {
                color: #6b7f95;
                font-size: 0.84rem;
                line-height: 1.55;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .article-inline-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 0.45rem;
            }

            .article-inline-meta span {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                min-height: 28px;
                padding: 0.28rem 0.58rem;
                border-radius: 999px;
                background: #eef4ff;
                color: #48607b;
                font-size: 0.74rem;
                font-weight: 700;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .article-status-note {
                margin-top: 0.45rem;
                color: #6b7f95;
                font-size: 0.78rem;
                line-height: 1.5;
            }

            .article-form-layout {
                display: grid;
                gap: 1rem;
            }

            .article-form-shell {
                display: grid;
                gap: 0.95rem;
                min-width: 0;
            }

            .article-form-error-summary {
                padding: 0.95rem 1rem;
                border: 1px solid #f0c6c6;
                border-radius: 18px;
                background: #fff6f6;
                color: #8d2f2f;
            }

            .article-form-error-summary h3 {
                margin: 0 0 0.5rem;
                font-size: 0.98rem;
                font-weight: 800;
            }

            .article-form-error-summary ul {
                margin: 0;
                padding-left: 1.1rem;
                display: grid;
                gap: 0.25rem;
                font-size: 0.84rem;
            }

            .article-form-section {
                padding: 0.95rem;
                border: 1px solid #dbe5f0;
                border-radius: 16px;
                background: #fff;
            }

            .article-form-section__title {
                margin: 0;
                color: #163252;
                font-size: 1rem;
                font-weight: 800;
            }

            .article-form-section__caption {
                margin: 0.2rem 0 1rem;
                color: #6b7f95;
                font-size: 0.84rem;
            }

            .article-form-grid {
                display: grid;
                grid-template-columns: repeat(12, minmax(0, 1fr));
                gap: 0.85rem;
                min-width: 0;
            }

            .article-col-12 { grid-column: span 12; min-width: 0; }
            .article-col-8 { grid-column: span 8; min-width: 0; }
            .article-col-6 { grid-column: span 6; min-width: 0; }
            .article-col-4 { grid-column: span 4; min-width: 0; }
            .article-col-3 { grid-column: span 3; min-width: 0; }

            .article-form-field {
                min-width: 0;
            }

            .article-form-field .form-control,
            .article-form-field .form-select {
                min-height: 48px;
            }

            .article-form-field textarea.form-control {
                min-height: 108px;
            }

            .article-form-field textarea.article-editor-textarea {
                min-height: 340px;
                border-top-left-radius: 0;
                border-top-right-radius: 0;
            }

            .article-field-hint,
            .article-char-counter,
            .article-upload-note,
            .article-schedule-note {
                margin-top: 0.38rem;
                color: #7b8ea4;
                font-size: 0.76rem;
                line-height: 1.5;
            }

            .article-field-hint strong,
            .article-char-counter strong {
                color: #48607b;
            }

            .article-editor-toolbar {
                display: flex;
                flex-wrap: wrap;
                gap: 0.45rem;
                padding: 0.7rem;
                border: 1px solid #dbe5f0;
                border-bottom: 0;
                border-radius: 14px 14px 0 0;
                background: #f7fbff;
            }

            .article-editor-toolbar button {
                min-height: 38px;
                padding: 0.45rem 0.72rem;
                border: 1px solid #cfe0ff;
                border-radius: 12px;
                background: #fff;
                color: #1849cb;
                font-size: 0.8rem;
                font-weight: 700;
            }

            .article-editor-toolbar button:hover,
            .article-editor-toolbar button:focus-visible {
                background: #edf4ff;
                outline: 0;
            }

            .article-upload-field {
                display: grid;
                gap: 0.7rem;
            }

            .article-upload-shell {
                display: grid;
                gap: 0.8rem;
                padding: 0.9rem;
                border: 1px dashed #b7cdf6;
                border-radius: 16px;
                background: #f9fbff;
            }

            .article-upload-shell.is-active {
                border-color: #7ea8ff;
                background: #edf4ff;
            }

            .article-upload-topline {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                flex-wrap: wrap;
            }

            .article-upload-actions {
                display: flex;
                gap: 0.65rem;
                flex-wrap: wrap;
            }

            .article-upload-filename {
                color: #163252;
                font-size: 0.84rem;
                font-weight: 700;
                min-width: 0;
                flex: 1 1 220px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .article-thumbnail-preview {
                width: 100%;
                max-height: 240px;
                object-fit: cover;
                border-radius: 16px;
                border: 1px solid #dbe5f0;
                background: #eef4ff;
            }

            .article-upload-preview[hidden] {
                display: none !important;
            }

            .article-publish-grid {
                display: grid;
                gap: 0.85rem;
            }

            .article-publish-top {
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr);
                gap: 0.85rem;
                align-items: end;
            }

            .article-switch-card {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                min-height: 48px;
                padding: 0.75rem 0.95rem;
                border: 1px solid #dbe5f0;
                border-radius: 16px;
                background: #f9fbff;
            }

            .article-switch-card__copy strong,
            .article-seo-card__header strong {
                display: block;
                color: #163252;
                font-size: 0.88rem;
                font-weight: 800;
            }

            .article-switch-card__copy span,
            .article-seo-card__header span {
                display: block;
                margin-top: 0.15rem;
                color: #6b7f95;
                font-size: 0.76rem;
                line-height: 1.45;
            }

            .article-status-panel {
                padding: 0.85rem 0.95rem;
                border: 1px solid #dbe5f0;
                border-radius: 16px;
                background: #f9fbff;
            }

            .article-status-badges {
                display: flex;
                gap: 0.45rem;
                flex-wrap: wrap;
                margin-top: 0.6rem;
            }

            .article-status-badges span {
                display: inline-flex;
                align-items: center;
                min-height: 28px;
                padding: 0.28rem 0.55rem;
                border-radius: 999px;
                background: #eef4ff;
                color: #48607b;
                font-size: 0.74rem;
                font-weight: 700;
            }

            .article-schedule-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 0.85rem;
            }

            .article-schedule-card {
                padding: 0.95rem;
                border: 1px solid #dbe5f0;
                border-radius: 16px;
                background: #fff;
            }

            .article-schedule-card__grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 0.8rem;
                align-items: start;
            }

            .article-schedule-card__field {
                min-width: 0;
            }

            .article-schedule-card__field label {
                display: block;
                margin-bottom: 0.35rem;
                color: #38506d;
                font-size: 0.78rem;
                font-weight: 700;
                line-height: 1.35;
            }

            .article-schedule-card__field .form-control {
                width: 100%;
                min-width: 0;
            }

            .article-schedule-card__title {
                margin: 0 0 0.12rem;
                color: #163252;
                font-size: 0.9rem;
                font-weight: 800;
            }

            .article-schedule-card__subtitle {
                margin: 0 0 0.85rem;
                color: #6b7f95;
                font-size: 0.76rem;
                line-height: 1.55;
            }

            .article-seo-card {
                border: 1px solid #dbe5f0;
                border-radius: 18px;
                background: #fff;
                overflow: hidden;
            }

            .article-seo-card__toggle {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.85rem;
                padding: 0.95rem 1rem;
                border: 0;
                background: rgba(248, 251, 255, 0.72);
                text-align: left;
            }

            .article-seo-card__toggle:hover,
            .article-seo-card__toggle:focus-visible {
                background: #f3f8ff;
                outline: 0;
            }

            .article-seo-card__toggle[aria-expanded="true"] {
                background: #f3f8ff;
            }

            .article-seo-card__icon {
                color: #6480a2;
                font-size: 0.95rem;
                transition: transform 0.18s ease;
            }

            .article-seo-card__toggle[aria-expanded="true"] .article-seo-card__icon {
                transform: rotate(180deg);
            }

            .article-seo-card__body {
                padding: 1rem;
                border-top: 1px solid #e7eef7;
            }

            .article-seo-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.85rem;
            }

            .article-seo-grid .article-col-full {
                grid-column: 1 / -1;
            }

            .article-seo-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                flex-wrap: wrap;
                margin-top: 0.42rem;
            }

            .article-form-error {
                margin-top: 0.35rem;
                color: #ab3737;
                font-size: 0.78rem;
                line-height: 1.45;
            }

            .article-form-field .is-invalid,
            .article-upload-shell.is-invalid,
            .article-seo-card.is-invalid {
                border-color: #e39b9b !important;
            }

            .article-action-bar {
                position: sticky;
                bottom: 0;
                z-index: 6;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.9rem;
                flex-wrap: wrap;
                padding: 0.95rem 1rem;
                margin-top: 1rem;
                border: 1px solid #dbe5f0;
                border-radius: 18px;
                background: rgba(255, 255, 255, 0.96);
                box-shadow: 0 -8px 22px rgba(16, 35, 63, 0.05);
            }

            .article-action-bar__back {
                flex: 0 0 auto;
            }

            .article-action-bar__group {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 0.75rem;
                flex: 1 1 auto;
                flex-wrap: wrap;
            }

            .article-action-bar .btn {
                min-height: 46px;
                white-space: nowrap;
            }

            .article-empty-state {
                padding: 2rem 1rem;
            }

            @media (max-width: 1535.98px) {
                .article-filter-grid {
                    grid-template-columns: minmax(0, 2fr) repeat(3, minmax(160px, 1fr));
                }
            }

            @media (max-width: 1199.98px) {
                .article-summary-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .article-filter-grid {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .article-filter-search {
                    grid-column: span 3;
                }

                .article-filter-actions {
                    grid-column: 2 / span 2;
                }

                .article-form-grid,
                .article-seo-grid,
                .article-publish-top,
                .article-schedule-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .article-col-8,
                .article-col-6,
                .article-col-4,
                .article-col-3 {
                    grid-column: span 6;
                }
            }

            @media (max-width: 991.98px) {
                .article-filter-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .article-filter-search,
                .article-filter-actions {
                    grid-column: span 2;
                }

                .article-list-card__head,
                .article-editor-shell__head {
                    padding: 0.95rem 0.95rem 0;
                }

            }

            @media (max-width: 767.98px) {
                .article-surface--padded,
                .article-filter-card,
                .article-form-section,
                .article-seo-card__body,
                .article-action-bar {
                    padding: 0.9rem;
                }

                .article-tab-nav {
                    width: 100%;
                    justify-content: space-between;
                }

                .article-tab-nav a,
                .article-tab-header > .btn {
                    flex: 1 1 0;
                }

                .article-summary-grid,
                .article-filter-grid,
                .article-form-grid,
                .article-seo-grid,
                .article-publish-top,
                .article-schedule-grid {
                    grid-template-columns: 1fr;
                }

                .article-summary-card,
                .article-filter-search,
                .article-filter-actions,
                .article-col-8,
                .article-col-6,
                .article-col-4,
                .article-col-3 {
                    grid-column: span 1;
                }

                .article-filter-mobile-toggle {
                    display: inline-flex;
                    width: 100%;
                }

                .article-filter-collapse:not(.show) {
                    display: none;
                }

                .article-filter-actions {
                    grid-template-columns: 1fr 1fr;
                }

                .article-upload-topline,
                .article-action-bar,
                .article-action-bar__group {
                    flex-direction: column;
                    align-items: stretch;
                }

                .article-action-bar__group .btn,
                .article-action-bar__back,
                .article-action-bar__back .btn,
                .article-upload-actions .btn {
                    width: 100%;
                }

                .article-seo-toolbar {
                    flex-direction: column;
                    align-items: stretch;
                }
            }

            @media (max-width: 479.98px) {
                .article-filter-actions {
                    grid-template-columns: 1fr;
                }

                .article-tab-nav a {
                    white-space: normal;
                }
            }

            .article-confirm-overlay {
                position: fixed;
                inset: 0;
                z-index: 1200;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
                background: rgba(15, 23, 42, 0.56);
                backdrop-filter: blur(10px);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transition: opacity 0.2s ease, visibility 0.2s ease;
            }

            .article-confirm-overlay.is-open {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }

            .article-confirm-dialog {
                width: min(100%, 30rem);
                border-radius: 1.75rem;
                background: #fff;
                box-shadow: 0 28px 70px rgba(15, 23, 42, 0.24);
                border: 1px solid rgba(59, 130, 246, 0.12);
                overflow: hidden;
                transform: translateY(12px) scale(0.98);
                transition: transform 0.2s ease;
            }

            .article-confirm-overlay.is-open .article-confirm-dialog {
                transform: translateY(0) scale(1);
            }

            .article-confirm-dialog__body {
                padding: 1.5rem 1.5rem 1.25rem;
            }

            .article-confirm-dialog__icon {
                width: 3.5rem;
                height: 3.5rem;
                border-radius: 1.15rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.3rem;
                margin-bottom: 1rem;
            }

            .article-confirm-dialog__icon[data-variant="primary"] {
                color: #1d4ed8;
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.16), rgba(96, 165, 250, 0.28));
            }

            .article-confirm-dialog__icon[data-variant="warning"] {
                color: #b45309;
                background: linear-gradient(135deg, rgba(251, 191, 36, 0.18), rgba(253, 230, 138, 0.3));
            }

            .article-confirm-dialog__icon[data-variant="danger"] {
                color: #b91c1c;
                background: linear-gradient(135deg, rgba(248, 113, 113, 0.16), rgba(254, 202, 202, 0.3));
            }

            .article-confirm-dialog__title {
                margin: 0 0 0.5rem;
                font-size: 1.35rem;
                font-weight: 800;
                color: #0f274f;
                letter-spacing: -0.02em;
            }

            .article-confirm-dialog__text {
                margin: 0;
                color: #546987;
                line-height: 1.7;
            }

            .article-confirm-dialog__actions {
                display: flex;
                justify-content: flex-end;
                gap: 0.75rem;
                padding: 0 1.5rem 1.5rem;
                flex-wrap: wrap;
            }

            .article-confirm-dialog__actions .btn {
                min-width: 9.5rem;
                justify-content: center;
            }

            body.article-confirm-open {
                overflow: hidden;
            }

            @media (max-width: 575.98px) {
                .article-confirm-overlay {
                    padding: 1rem;
                    align-items: flex-end;
                }

                .article-confirm-dialog {
                    width: 100%;
                    border-radius: 1.5rem 1.5rem 1rem 1rem;
                }

                .article-confirm-dialog__body {
                    padding: 1.25rem 1.25rem 1rem;
                }

                .article-confirm-dialog__actions {
                    padding: 0 1.25rem 1.25rem;
                    display: grid;
                    grid-template-columns: 1fr;
                }

                .article-confirm-dialog__actions .btn {
                    width: 100%;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
                const confirmOverlay = document.createElement('div');
                confirmOverlay.className = 'article-confirm-overlay';
                confirmOverlay.innerHTML = `
                    <div class="article-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="article-confirm-title" aria-describedby="article-confirm-text">
                        <div class="article-confirm-dialog__body">
                            <div class="article-confirm-dialog__icon" data-article-confirm-icon data-variant="primary">
                                <i class="bi bi-send-check"></i>
                            </div>
                            <h3 class="article-confirm-dialog__title" id="article-confirm-title">Konfirmasi aksi</h3>
                            <p class="article-confirm-dialog__text" id="article-confirm-text">Lanjutkan aksi ini?</p>
                        </div>
                        <div class="article-confirm-dialog__actions">
                            <button type="button" class="btn btn-outline-secondary" data-article-confirm-cancel>Batal</button>
                            <button type="button" class="btn btn-primary" data-article-confirm-submit>Lanjutkan</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(confirmOverlay);

                const confirmTitle = confirmOverlay.querySelector('#article-confirm-title');
                const confirmText = confirmOverlay.querySelector('#article-confirm-text');
                const confirmIcon = confirmOverlay.querySelector('[data-article-confirm-icon]');
                const confirmCancel = confirmOverlay.querySelector('[data-article-confirm-cancel]');
                const confirmSubmit = confirmOverlay.querySelector('[data-article-confirm-submit]');
                const confirmDialog = confirmOverlay.querySelector('.article-confirm-dialog');
                let pendingAction = null;
                let pendingTrigger = null;

                const confirmIcons = {
                    primary: 'bi bi-send-check',
                    warning: 'bi bi-exclamation-diamond',
                    danger: 'bi bi-trash3',
                };

                const restoreFormSubmitButtons = (form) => {
                    if (!(form instanceof HTMLFormElement)) {
                        return;
                    }

                    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                        button.disabled = false;
                        delete button.dataset.submitting;
                        if (button.dataset.originalHtml) {
                            button.innerHTML = button.dataset.originalHtml;
                            delete button.dataset.originalHtml;
                        }
                        button.style.removeProperty('width');
                        button.style.removeProperty('max-width');
                        button.classList.remove('is-loading');
                    });
                };

                const clearTemporarySubmitter = (form) => {
                    if (!(form instanceof HTMLFormElement)) {
                        return;
                    }

                    form.querySelectorAll('[data-confirm-temp-submitter="true"]').forEach((input) => input.remove());
                };

                const appendTemporarySubmitter = (form, submitter) => {
                    if (
                        !(form instanceof HTMLFormElement)
                        || !(submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement)
                        || !submitter.name
                    ) {
                        return;
                    }

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = submitter.name;
                    input.value = submitter.value;
                    input.setAttribute('data-confirm-temp-submitter', 'true');
                    form.appendChild(input);
                };

                const submitFormWithBypass = (form, submitter = null) => {
                    if (!(form instanceof HTMLFormElement)) {
                        return;
                    }

                    restoreFormSubmitButtons(form);
                    clearTemporarySubmitter(form);

                    if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                        form.dataset.confirmed = 'false';
                        return;
                    }

                    appendTemporarySubmitter(form, submitter);

                    if (submitter instanceof HTMLButtonElement) {
                        submitter.dataset.confirmBypass = 'true';
                    }

                    form.dataset.confirmed = 'true';
                    form.submit();
                };

                const closeConfirmDialog = () => {
                    confirmOverlay.classList.remove('is-open');
                    document.body.classList.remove('article-confirm-open');
                    pendingAction = null;
                    if (pendingTrigger instanceof HTMLElement) {
                        pendingTrigger.focus({ preventScroll: true });
                    }
                    pendingTrigger = null;
                };

                const openConfirmDialog = ({ title, message, variant = 'primary', submitLabel = 'Lanjutkan', onConfirm, trigger = null }) => {
                    pendingAction = onConfirm;
                    pendingTrigger = trigger;
                    confirmTitle.textContent = title || 'Konfirmasi aksi';
                    confirmText.textContent = message || 'Lanjutkan aksi ini?';
                    confirmSubmit.textContent = submitLabel;
                    confirmSubmit.className = `btn ${variant === 'danger' ? 'btn-danger' : variant === 'warning' ? 'btn-warning' : 'btn-primary'}`;
                    confirmIcon.dataset.variant = variant;
                    confirmIcon.innerHTML = `<i class="${confirmIcons[variant] || confirmIcons.primary}"></i>`;
                    confirmOverlay.classList.add('is-open');
                    document.body.classList.add('article-confirm-open');
                    window.setTimeout(() => confirmSubmit.focus({ preventScroll: true }), 10);
                };

                const toSlug = (value) => String(value || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');

                document.querySelectorAll('[data-editor-toolbar]').forEach((toolbar) => {
                    const textarea = document.getElementById(toolbar.dataset.editorToolbar);
                    if (!textarea) {
                        return;
                    }

                    const wrapSelection = (openTag, closeTag) => {
                        const start = textarea.selectionStart ?? 0;
                        const end = textarea.selectionEnd ?? 0;
                        const selected = textarea.value.slice(start, end);
                        const replacement = openTag + selected + closeTag;
                        textarea.setRangeText(replacement, start, end, 'end');
                        textarea.focus();
                    };

                    const insertList = (type) => {
                        const start = textarea.selectionStart ?? 0;
                        const end = textarea.selectionEnd ?? 0;
                        const selected = textarea.value.slice(start, end).trim() || 'Item 1\nItem 2';
                        const lines = selected.split(/\r?\n/).filter(Boolean);
                        const items = lines.map((line) => `<li>${line}</li>`).join('');
                        const replacement = `<${type}>${items}</${type}>`;
                        textarea.setRangeText(replacement, start, end, 'end');
                        textarea.focus();
                    };

                    toolbar.querySelectorAll('button').forEach((button) => {
                        button.addEventListener('click', () => {
                            const action = button.dataset.editorAction;
                            if (action === 'wrap') {
                                wrapSelection(button.dataset.editorOpen, button.dataset.editorClose);
                                return;
                            }

                            if (action === 'heading') {
                                wrapSelection(`<${button.dataset.editorValue}>`, `</${button.dataset.editorValue}>`);
                                return;
                            }

                            if (action === 'list') {
                                insertList(button.dataset.editorList);
                                return;
                            }

                            if (action === 'link') {
                                const url = window.prompt('Masukkan URL tautan:', 'https://');
                                if (!url) {
                                    return;
                                }

                                const start = textarea.selectionStart ?? 0;
                                const end = textarea.selectionEnd ?? 0;
                                const selected = textarea.value.slice(start, end) || 'Teks tautan';
                                const replacement = `<a href="${url}" target="_blank" rel="noopener noreferrer">${selected}</a>`;
                                textarea.setRangeText(replacement, start, end, 'end');
                                textarea.focus();
                            }
                        });
                    });
                });

                document.querySelectorAll('[data-article-form]').forEach((form) => {
                    const status = form.querySelector('[data-article-status]');
                    const publishDate = form.querySelector('[data-article-publish-date]');
                    const publishTime = form.querySelector('[data-article-publish-time]');
                    const scheduleArea = form.querySelector('[data-article-schedule-area]');
                    const scheduleFields = form.querySelectorAll('[data-article-schedule-group]');
                    const statusDescription = form.querySelector('[data-article-status-description]');
                    const titleInput = form.querySelector('[data-article-title]');
                    const slugInput = form.querySelector('[data-article-slug]');
                    const slugPreview = form.querySelector('[data-article-slug-preview]');
                    const regenerateSlugButton = form.querySelector('[data-article-slug-regenerate]');
                    const seoAccordionButton = form.querySelector('[data-article-seo-toggle]');
                    const seoAccordionPanel = form.querySelector('[data-article-seo-panel]');
                    const imageInput = form.querySelector('[data-article-image-input]');
                    const imageShell = form.querySelector('[data-article-upload-shell]');
                    const imageName = form.querySelector('[data-article-image-name]');
                    const imagePreviewWrap = form.querySelector('[data-article-image-preview-wrap]');
                    const imagePreview = form.querySelector('[data-article-image-preview]');
                    const imagePreviewFallback = form.querySelector('[data-article-image-preview-fallback]');
                    const imageClear = form.querySelector('[data-article-image-clear]');
                    const currentImage = form.querySelector('[data-article-current-image]')?.getAttribute('data-article-current-image') || '';
                    const currentImageAlt = form.querySelector('[data-article-current-image-alt]')?.getAttribute('data-article-current-image-alt') || 'Preview gambar artikel';

                    const statusNotes = {
                        draft: 'Draft: hanya tersimpan dan belum terlihat publik.',
                        scheduled: 'Dijadwalkan: artikel akan tampil otomatis pada waktu yang ditentukan.',
                        published: 'Dipublikasikan: artikel akan langsung tampil pada halaman publik.',
                        archived: 'Diarsipkan: artikel disimpan sebagai arsip dan tidak tampil publik.',
                        inactive: 'Dinonaktifkan: artikel tidak aktif dan tidak tampil publik.',
                    };

                    const syncCounter = (input) => {
                        const target = input?.closest('[data-article-counter-wrap]')?.querySelector('[data-article-counter]');
                        if (!input || !target) {
                            return;
                        }

                        const max = Number(input.getAttribute('maxlength') || 0);
                        target.textContent = max ? `${input.value.length}/${max}` : `${input.value.length}`;
                    };

                    const setPreview = (src, alt, fileLabel = '') => {
                        if (imageName) {
                            imageName.textContent = fileLabel || 'Belum ada file dipilih';
                        }

                        if (imagePreview && imagePreviewWrap) {
                            if (src) {
                                imagePreview.src = src;
                                imagePreview.alt = alt || currentImageAlt;
                                imagePreviewWrap.hidden = false;
                                imagePreviewFallback?.setAttribute('hidden', 'hidden');
                            } else {
                                imagePreview.removeAttribute('src');
                                imagePreviewWrap.hidden = true;
                                imagePreviewFallback?.removeAttribute('hidden');
                            }
                        }

                        imageShell?.classList.toggle('is-active', Boolean(src));
                    };

                    const resetPreviewToCurrent = () => {
                        if (currentImage) {
                            setPreview(currentImage, currentImageAlt, 'Menggunakan gambar saat ini');
                        } else {
                            setPreview('', '', 'Belum ada file dipilih');
                        }
                    };

                    const syncScheduleState = () => {
                        const current = status?.value || 'draft';
                        const showSchedule = current === 'scheduled' || current === 'published';
                        const scheduleRequired = current === 'scheduled';

                        scheduleFields.forEach((group) => {
                            group.classList.toggle('d-none', !showSchedule);
                        });

                        if (scheduleArea) {
                            scheduleArea.hidden = !showSchedule;
                        }

                        if (publishDate) {
                            publishDate.disabled = !showSchedule;
                            publishDate.required = scheduleRequired;
                            if (!showSchedule) {
                                publishDate.value = '';
                            }
                        }

                        if (publishTime) {
                            publishTime.disabled = !showSchedule;
                            publishTime.required = scheduleRequired;
                            if (!showSchedule) {
                                publishTime.value = '';
                            }
                        }

                        if (statusDescription) {
                            statusDescription.textContent = statusNotes[current] || '';
                        }
                    };

                    const syncSlugPreview = () => {
                        if (!slugPreview || !slugInput) {
                            return;
                        }

                        slugPreview.textContent = toSlug(slugInput.value) || 'slug-artikel';
                    };

                    const openSeoIfNeeded = () => {
                        if (!seoAccordionButton || !seoAccordionPanel) {
                            return;
                        }

                        const hasSeoError = form.querySelector('[data-article-seo-error="true"]');
                        if (hasSeoError) {
                            seoAccordionButton.classList.remove('collapsed');
                            seoAccordionButton.setAttribute('aria-expanded', 'true');
                            seoAccordionPanel.classList.add('show');
                        }
                    };

                    form.querySelectorAll('[data-article-counter-input]').forEach((input) => {
                        input.addEventListener('input', () => syncCounter(input));
                        syncCounter(input);
                    });

                    titleInput?.addEventListener('input', () => {
                        const manualSlug = slugInput?.dataset.slugEdited === 'true';
                        if (!manualSlug && slugInput) {
                            slugInput.value = toSlug(titleInput.value);
                            syncSlugPreview();
                        }
                    });

                    slugInput?.addEventListener('input', () => {
                        slugInput.dataset.slugEdited = 'true';
                        slugInput.value = toSlug(slugInput.value);
                        syncSlugPreview();
                    });

                    regenerateSlugButton?.addEventListener('click', () => {
                        if (!slugInput || !titleInput) {
                            return;
                        }

                        slugInput.dataset.slugEdited = 'false';
                        slugInput.value = toSlug(titleInput.value);
                        syncSlugPreview();
                    });

                    status?.addEventListener('change', syncScheduleState);
                    syncScheduleState();
                    syncSlugPreview();
                    openSeoIfNeeded();

                    if (imageInput) {
                        imageInput.addEventListener('change', () => {
                            const file = imageInput.files?.[0];
                            if (!file) {
                                resetPreviewToCurrent();
                                return;
                            }

                            imageName.textContent = file.name;
                            if (!file.type.startsWith('image/')) {
                                resetPreviewToCurrent();
                                return;
                            }

                            const reader = new FileReader();
                            reader.onload = () => {
                                setPreview(String(reader.result || ''), file.name, file.name);
                            };
                            reader.readAsDataURL(file);
                        });

                        imageClear?.addEventListener('click', () => {
                            imageInput.value = '';
                            resetPreviewToCurrent();
                        });

                        resetPreviewToCurrent();
                    }

                    form.addEventListener('submit', (event) => {
                        const submitter = event.submitter;
                        if (!(submitter instanceof HTMLButtonElement)) {
                            return;
                        }

                        if (submitter.dataset.submitting === 'true') {
                            event.preventDefault();
                            return;
                        }

                        submitter.dataset.submitting = 'true';
                        submitter.disabled = true;

                        const label = submitter.dataset.loadingText;
                        if (label) {
                            submitter.dataset.originalHtml = submitter.innerHTML;
                            submitter.innerHTML = '<i class="bi bi-arrow-repeat"></i>' + label;
                        }
                    });

                    const firstInvalid = form.querySelector('.is-invalid, [aria-invalid="true"]');
                    if (firstInvalid) {
                        window.requestAnimationFrame(() => {
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            if (typeof firstInvalid.focus === 'function') {
                                firstInvalid.focus({ preventScroll: true });
                            }
                        });
                    }
                });

                document.querySelectorAll('[data-article-filter-toggle]').forEach((button) => {
                    const target = document.querySelector(button.getAttribute('data-article-filter-toggle'));
                    if (!target) {
                        return;
                    }

                    button.addEventListener('click', () => {
                        const visible = target.classList.contains('show');
                        target.classList.toggle('show', !visible);
                        button.setAttribute('aria-expanded', visible ? 'false' : 'true');
                    });
                });

                document.querySelectorAll('.article-confirmable-form').forEach((form) => {
                    form.addEventListener('submit', (event) => {
                        if (form.dataset.confirmed === 'true') {
                            form.dataset.confirmed = 'false';
                            return;
                        }

                        event.preventDefault();
                        restoreFormSubmitButtons(form);
                        const submitButton = event.submitter instanceof HTMLElement
                            ? event.submitter
                            : form.querySelector('button[type="submit"], .dropdown-item');

                        openConfirmDialog({
                            title: form.dataset.confirmTitle || 'Konfirmasi aksi',
                            message: form.dataset.confirmMessage || 'Lanjutkan aksi ini?',
                            variant: form.dataset.confirmVariant || 'primary',
                            submitLabel: form.dataset.confirmSubmitLabel || 'Lanjutkan',
                            onConfirm: () => submitFormWithBypass(
                                form,
                                submitButton instanceof HTMLButtonElement ? submitButton : null,
                            ),
                            trigger: submitButton instanceof HTMLElement ? submitButton : null,
                        });
                    });
                });

                confirmOverlay.addEventListener('click', (event) => {
                    if (event.target === confirmOverlay) {
                        closeConfirmDialog();
                    }
                });

                confirmCancel?.addEventListener('click', closeConfirmDialog);
                confirmSubmit?.addEventListener('click', () => {
                    const action = pendingAction;
                    closeConfirmDialog();
                    action?.();
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && confirmOverlay.classList.contains('is-open')) {
                        event.preventDefault();
                        closeConfirmDialog();
                    }
                });
            });
        </script>
    @endpush
@endonce
