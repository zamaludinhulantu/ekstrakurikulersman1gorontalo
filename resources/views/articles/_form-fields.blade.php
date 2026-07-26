@php
    $article = $article ?? null;
    $isEdit = $article !== null;
    $fieldPrefix = $fieldPrefix ?? 'article';
    $backRoute = $backRoute ?? route('admin.articles.index');
    $selectedStatus = old('publication_status', $article?->publication_status ?? \App\Models\Article::STATUS_DRAFT);
    $selectedCategory = old('content_category', $article?->content_category ?? \App\Models\Article::CATEGORY_ACTIVITY_NEWS);
    $selectedExtracurricular = (string) old('extracurricular_id', $article?->extracurricular_id);
    $showGeneralOption = $showGeneralOption ?? true;
    $extracurricularRequired = $extracurricularRequired ?? false;
    $extracurricularPlaceholder = $extracurricularPlaceholder ?? 'Umum / semua kegiatan';
    $contentLabel = $contentLabel ?? 'Isi informasi dasar artikel agar mudah ditemukan dan dipahami.';
    $currentImageUrl = $article?->cover_image_url;
    $currentImageAlt = $article?->image_alt_text_label ?? $article?->title ?? 'Preview gambar artikel';
    $seoHasError = $errors->hasAny(['slug', 'image_alt_text', 'meta_description']);
    $errorMap = [
        'excerpt' => [
            'The excerpt field is required.' => 'Ringkasan artikel wajib diisi sebelum artikel dipublikasikan.',
            'The excerpt field must be at least 24 characters.' => 'Ringkasan artikel minimal harus berisi 24 karakter.',
        ],
        'content' => [
            'The content field is required.' => 'Isi artikel wajib diisi sebelum artikel dipublikasikan.',
        ],
        'title' => [
            'The title field is required.' => 'Judul artikel wajib diisi.',
            'The title field must be at least 8 characters.' => 'Judul artikel minimal harus berisi 8 karakter.',
        ],
        'slug' => [
            'The slug field format is invalid.' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
        ],
    ];

    $fieldError = function (string $field) use ($errors, $errorMap): ?string {
        $message = $errors->first($field);
        if ($message === '') {
            return null;
        }

        return $errorMap[$field][$message] ?? $message;
    };
@endphp

<input type="hidden" name="_active_tab" value="write">

<div class="article-form-shell" data-article-form>
    @if($errors->any())
        <div class="article-form-error-summary" role="alert" aria-live="assertive">
            <h3>Periksa kembali data berikut</h3>
            <ul>
                @foreach($errors->all() as $message)
                    <li>
                        {{
                            match ($message) {
                                'The excerpt field must be at least 24 characters.' => 'Ringkasan artikel minimal harus berisi 24 karakter.',
                                'The title field must be at least 8 characters.' => 'Judul artikel minimal harus berisi 8 karakter.',
                                'The title field is required.' => 'Judul artikel wajib diisi.',
                                'The excerpt field is required.' => 'Ringkasan artikel wajib diisi sebelum artikel dipublikasikan.',
                                'The content field is required.' => 'Isi artikel wajib diisi sebelum artikel dipublikasikan.',
                                default => $message,
                            }
                        }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="article-form-section">
        <h2 class="article-form-section__title">Informasi Utama</h2>
        <p class="article-form-section__caption">{{ $contentLabel }}</p>

        <div class="article-form-grid">
            <div class="article-col-6 article-form-field">
                <label for="{{ $fieldPrefix }}_title">Judul</label>
                <input
                    id="{{ $fieldPrefix }}_title"
                    type="text"
                    name="title"
                    value="{{ old('title', $article?->title) }}"
                    class="form-control @error('title') is-invalid @enderror"
                    placeholder="Contoh: Tim Pramuka raih juara kota"
                    required
                    aria-invalid="@error('title') true @else false @enderror"
                    aria-describedby="@error('title') {{ $fieldPrefix }}_title_error @else {{ $fieldPrefix }}_title_hint @enderror"
                    data-article-title
                >
                <div id="{{ $fieldPrefix }}_title_hint" class="article-field-hint">Gunakan judul yang jelas, ringkas, dan mudah dipahami siswa.</div>
                @if($fieldError('title'))
                    <div id="{{ $fieldPrefix }}_title_error" class="article-form-error">{{ $fieldError('title') }}</div>
                @endif
            </div>

            <div class="article-col-3 article-form-field">
                <label for="{{ $fieldPrefix }}_category">Kategori Konten</label>
                <select
                    id="{{ $fieldPrefix }}_category"
                    name="content_category"
                    class="form-select @error('content_category') is-invalid @enderror"
                    required
                    aria-invalid="@error('content_category') true @else false @enderror"
                    aria-describedby="@error('content_category') {{ $fieldPrefix }}_category_error @enderror"
                >
                    @foreach($contentCategories as $key => $label)
                        <option value="{{ $key }}" @selected($selectedCategory === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('content_category')
                    <div id="{{ $fieldPrefix }}_category_error" class="article-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="article-col-3 article-form-field">
                <label for="{{ $fieldPrefix }}_extracurricular_id">Kegiatan terkait</label>
                <select
                    id="{{ $fieldPrefix }}_extracurricular_id"
                    name="extracurricular_id"
                    class="form-select @error('extracurricular_id') is-invalid @enderror"
                    @required($extracurricularRequired)
                    aria-invalid="@error('extracurricular_id') true @else false @enderror"
                    aria-describedby="@error('extracurricular_id') {{ $fieldPrefix }}_extracurricular_error @else {{ $fieldPrefix }}_extracurricular_hint @enderror"
                >
                    @if($showGeneralOption)
                        <option value="">{{ $extracurricularPlaceholder }}</option>
                    @endif
                    @foreach($extracurriculars as $item)
                        <option value="{{ $item->id }}" @selected($selectedExtracurricular === (string) $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
                <div id="{{ $fieldPrefix }}_extracurricular_hint" class="article-field-hint">
                    {{ $extracurricularRequired ? 'Pilih kegiatan binaan yang akan ditautkan ke artikel ini.' : 'Biarkan umum jika artikel tidak terkait satu kegiatan khusus.' }}
                </div>
                @error('extracurricular_id')
                    <div id="{{ $fieldPrefix }}_extracurricular_error" class="article-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="article-col-12 article-form-field" data-article-counter-wrap>
                <label for="{{ $fieldPrefix }}_excerpt">Ringkasan artikel</label>
                <textarea
                    id="{{ $fieldPrefix }}_excerpt"
                    name="excerpt"
                    class="form-control @error('excerpt') is-invalid @enderror"
                    rows="3"
                    maxlength="320"
                    placeholder="Ringkasan untuk kartu berita dan hasil pencarian publik"
                    aria-invalid="@error('excerpt') true @else false @enderror"
                    aria-describedby="@error('excerpt') {{ $fieldPrefix }}_excerpt_error @else {{ $fieldPrefix }}_excerpt_hint @enderror"
                    data-article-counter-input
                >{{ old('excerpt', $article?->excerpt) }}</textarea>
                <div class="article-seo-toolbar">
                    <div id="{{ $fieldPrefix }}_excerpt_hint" class="article-field-hint">Wajib saat artikel akan dijadwalkan atau dipublikasikan.</div>
                    <div class="article-char-counter">Karakter: <strong data-article-counter>0/320</strong></div>
                </div>
                @if($fieldError('excerpt'))
                    <div id="{{ $fieldPrefix }}_excerpt_error" class="article-form-error">{{ $fieldError('excerpt') }}</div>
                @endif
            </div>
        </div>
    </section>

    <section class="article-form-section">
        <h2 class="article-form-section__title">Konten</h2>
        <p class="article-form-section__caption">Gunakan editor ringan untuk heading, daftar, tautan, dan kutipan tanpa menambah editor berat.</p>

        <div class="article-form-field">
            <div class="article-editor-toolbar" data-editor-toolbar="{{ $fieldPrefix }}_content">
                <button type="button" aria-label="Tambah heading" data-editor-action="heading" data-editor-value="h2">Heading</button>
                <button type="button" aria-label="Tebalkan teks" data-editor-action="wrap" data-editor-open="<strong>" data-editor-close="</strong>">Bold</button>
                <button type="button" aria-label="Miringkan teks" data-editor-action="wrap" data-editor-open="<em>" data-editor-close="</em>">Italic</button>
                <button type="button" aria-label="Daftar bullet" data-editor-action="list" data-editor-list="ul">Bullet</button>
                <button type="button" aria-label="Daftar bernomor" data-editor-action="list" data-editor-list="ol">Numbered</button>
                <button type="button" aria-label="Tambah tautan" data-editor-action="link">Link</button>
                <button type="button" aria-label="Tambah kutipan" data-editor-action="wrap" data-editor-open="<blockquote>" data-editor-close="</blockquote>">Kutipan</button>
            </div>
            <textarea
                id="{{ $fieldPrefix }}_content"
                name="content"
                class="form-control article-editor-textarea @error('content') is-invalid @enderror"
                rows="12"
                placeholder="Tulis isi berita atau artikel lengkap. HTML aman sederhana seperti <h2>, <strong>, <em>, <ul>, <ol>, <li>, <blockquote>, dan <a> akan dipertahankan."
                aria-invalid="@error('content') true @else false @enderror"
                aria-describedby="@error('content') {{ $fieldPrefix }}_content_error @else {{ $fieldPrefix }}_content_hint @enderror"
            >{{ old('content', $article?->content) }}</textarea>
            <div id="{{ $fieldPrefix }}_content_hint" class="article-field-hint">Konten akan disanitasi oleh server. Gunakan struktur yang rapi agar nyaman dibaca di halaman publik.</div>
            @if($fieldError('content'))
                <div id="{{ $fieldPrefix }}_content_error" class="article-form-error">{{ $fieldError('content') }}</div>
            @endif
        </div>
    </section>

    <section class="article-form-section">
        <h2 class="article-form-section__title">Media dan Publikasi</h2>
        <p class="article-form-section__caption">Atur gambar utama, status tayang, masa berlaku, dan artikel unggulan.</p>

        <div class="article-form-grid">
            <div class="article-col-6 article-upload-field">
                <span class="article-upload-field__label">Gambar utama</span>
                <div
                    class="article-upload-shell @error('image') is-invalid @enderror"
                    data-article-upload-shell
                >
                    <div class="article-upload-topline">
                        <div class="article-upload-filename" data-article-image-name>Belum ada file dipilih</div>
                        <div class="article-upload-actions">
                            <label for="{{ $fieldPrefix }}_image" class="btn btn-outline-primary mb-0">
                                <i class="bi bi-upload"></i>Pilih Gambar
                            </label>
                            <button type="button" class="btn btn-outline-secondary" data-article-image-clear aria-label="Hapus pilihan gambar">
                                <i class="bi bi-x-circle"></i>Hapus Pilihan
                            </button>
                        </div>
                    </div>

                    <input
                        id="{{ $fieldPrefix }}_image"
                        type="file"
                        name="image"
                        class="visually-hidden"
                        accept=".jpg,.jpeg,.png,.webp"
                        data-article-image-input
                        aria-describedby="@error('image') {{ $fieldPrefix }}_image_error @else {{ $fieldPrefix }}_image_hint @enderror"
                    >

                    <div class="article-upload-preview" data-article-image-preview-wrap hidden>
                        <img src="" alt="Preview gambar artikel" class="article-thumbnail-preview" data-article-image-preview>
                    </div>
                    <div class="article-upload-note" data-article-image-preview-fallback>
                        JPG, JPEG, PNG, atau WebP. Ukuran maksimal 2 MB.
                    </div>
                    <span class="d-none" data-article-current-image="{{ $currentImageUrl }}"></span>
                    <span class="d-none" data-article-current-image-alt="{{ $currentImageAlt }}"></span>
                </div>
                <div id="{{ $fieldPrefix }}_image_hint" class="article-field-hint">Jika Anda memilih file baru, preview akan berubah sebelum form dikirim.</div>
                @error('image')
                    <div id="{{ $fieldPrefix }}_image_error" class="article-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="article-col-6">
                <div class="article-publish-grid">
                    <div class="article-publish-top">
                        <div class="article-status-panel article-form-field">
                            <label for="{{ $fieldPrefix }}_status">Status publikasi</label>
                            <select
                                id="{{ $fieldPrefix }}_status"
                                name="publication_status"
                                class="form-select @error('publication_status') is-invalid @enderror"
                                required
                                aria-invalid="@error('publication_status') true @else false @enderror"
                                aria-describedby="@error('publication_status') {{ $fieldPrefix }}_status_error @else {{ $fieldPrefix }}_status_hint @enderror"
                                data-article-status
                            >
                                @foreach($publicationStatuses as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="article-status-badges">
                                <span>Draft</span>
                                <span>Dijadwalkan</span>
                                <span>Dipublikasikan</span>
                            </div>
                            <div id="{{ $fieldPrefix }}_status_hint" class="article-status-note" data-article-status-description></div>
                            @error('publication_status')
                                <div id="{{ $fieldPrefix }}_status_error" class="article-form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="article-switch-card">
                            <div class="article-switch-card__copy">
                                <strong>Artikel unggulan</strong>
                                <span>Tampilkan artikel ini sebagai konten sorotan bila relevan.</span>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="{{ $fieldPrefix }}_featured" name="is_featured" value="1" @checked(old('is_featured', $article?->is_featured))>
                                <label class="visually-hidden" for="{{ $fieldPrefix }}_featured">Artikel unggulan</label>
                            </div>
                        </div>
                    </div>

                    <div class="article-schedule-grid" data-article-schedule-area>
                        <div class="article-schedule-card">
                            <h3 class="article-schedule-card__title">Jadwal tayang</h3>
                            <p class="article-schedule-card__subtitle">Isi jika artikel dijadwalkan atau ingin mengatur waktu publikasi.</p>
                            <div class="article-schedule-card__grid">
                                <div class="article-form-field article-schedule-card__field d-none" data-article-schedule-group>
                                    <label for="{{ $fieldPrefix }}_publish_date">Tanggal tayang</label>
                                    <input
                                        id="{{ $fieldPrefix }}_publish_date"
                                        type="date"
                                        name="publish_date"
                                        value="{{ old('publish_date', optional($article?->publish_at)->format('Y-m-d')) }}"
                                        class="form-control @error('publish_date') is-invalid @enderror"
                                        aria-invalid="@error('publish_date') true @else false @enderror"
                                        aria-describedby="@error('publish_date') {{ $fieldPrefix }}_publish_date_error @else {{ $fieldPrefix }}_publish_date_hint @enderror"
                                        data-article-publish-date
                                    >
                                    <div id="{{ $fieldPrefix }}_publish_date_hint" class="article-schedule-note">Tampilan mengikuti browser, data tetap dikirim aman ke backend.</div>
                                    @error('publish_date')
                                        <div id="{{ $fieldPrefix }}_publish_date_error" class="article-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="article-form-field article-schedule-card__field article-schedule-card__field--time d-none" data-article-schedule-group>
                                    <label for="{{ $fieldPrefix }}_publish_time">Jam tayang</label>
                                    <input
                                        id="{{ $fieldPrefix }}_publish_time"
                                        type="time"
                                        name="publish_time"
                                        value="{{ old('publish_time', optional($article?->publish_at)->format('H:i')) }}"
                                        class="form-control @error('publish_time') is-invalid @enderror"
                                        aria-invalid="@error('publish_time') true @else false @enderror"
                                        aria-describedby="@error('publish_time') {{ $fieldPrefix }}_publish_time_error @enderror"
                                        data-article-publish-time
                                    >
                                    @error('publish_time')
                                        <div id="{{ $fieldPrefix }}_publish_time_error" class="article-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="article-schedule-card">
                            <h3 class="article-schedule-card__title">Masa berlaku</h3>
                            <p class="article-schedule-card__subtitle">Tanggal berakhir bersifat opsional jika artikel hanya tampil sementara.</p>
                            <div class="article-schedule-card__grid">
                                <div class="article-form-field article-schedule-card__field">
                                    <label for="{{ $fieldPrefix }}_expires_date">Tanggal berakhir</label>
                                    <input
                                        id="{{ $fieldPrefix }}_expires_date"
                                        type="date"
                                        name="expires_date"
                                        value="{{ old('expires_date', optional($article?->expires_at)->format('Y-m-d')) }}"
                                        class="form-control @error('expires_date') is-invalid @enderror"
                                        aria-invalid="@error('expires_date') true @else false @enderror"
                                        aria-describedby="@error('expires_date') {{ $fieldPrefix }}_expires_date_error @enderror"
                                    >
                                    @error('expires_date')
                                        <div id="{{ $fieldPrefix }}_expires_date_error" class="article-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="article-form-field article-schedule-card__field article-schedule-card__field--time">
                                    <label for="{{ $fieldPrefix }}_expires_time">Jam berakhir</label>
                                    <input
                                        id="{{ $fieldPrefix }}_expires_time"
                                        type="time"
                                        name="expires_time"
                                        value="{{ old('expires_time', optional($article?->expires_at)->format('H:i')) }}"
                                        class="form-control @error('expires_time') is-invalid @enderror"
                                        aria-invalid="@error('expires_time') true @else false @enderror"
                                        aria-describedby="@error('expires_time') {{ $fieldPrefix }}_expires_time_error @enderror"
                                    >
                                    @error('expires_time')
                                        <div id="{{ $fieldPrefix }}_expires_time_error" class="article-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section
        class="article-seo-card @if($seoHasError) is-invalid @endif"
        @if($seoHasError) data-article-seo-error="true" @endif
    >
        <button
            type="button"
            class="article-seo-card__toggle collapsed"
            data-bs-toggle="collapse"
            data-bs-target="#{{ $fieldPrefix }}_seo_panel"
            data-article-seo-toggle
            aria-expanded="{{ $seoHasError ? 'true' : 'false' }}"
            aria-controls="{{ $fieldPrefix }}_seo_panel"
        >
            <span class="article-seo-card__header">
                <strong>SEO Sederhana</strong>
                <span>Atur slug, alt text, dan meta description agar artikel lebih mudah dibaca dan dibagikan.</span>
            </span>
            <i class="bi bi-chevron-down article-seo-card__icon" aria-hidden="true"></i>
        </button>

        <div
            id="{{ $fieldPrefix }}_seo_panel"
            class="collapse @if($seoHasError) show @endif"
            data-article-seo-panel
        >
            <div class="article-seo-card__body">
                <div class="article-seo-grid">
                    <div class="article-form-field" data-article-counter-wrap>
                        <label for="{{ $fieldPrefix }}_slug">Slug</label>
                        <input
                            id="{{ $fieldPrefix }}_slug"
                            type="text"
                            name="slug"
                            value="{{ old('slug', $article?->slug) }}"
                            class="form-control @error('slug') is-invalid @enderror"
                            placeholder="slug-artikel-anda"
                            aria-invalid="@error('slug') true @else false @enderror"
                            aria-describedby="@error('slug') {{ $fieldPrefix }}_slug_error @else {{ $fieldPrefix }}_slug_hint @enderror"
                            data-article-slug
                            data-article-counter-input
                        >
                        <div class="article-seo-toolbar">
                            <div id="{{ $fieldPrefix }}_slug_hint" class="article-field-hint">Slug hasil bersih: <strong data-article-slug-preview>slug-artikel</strong></div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-article-slug-regenerate>
                                <i class="bi bi-arrow-repeat"></i>Buat ulang dari judul
                            </button>
                        </div>
                        @if($fieldError('slug'))
                            <div id="{{ $fieldPrefix }}_slug_error" class="article-form-error">{{ $fieldError('slug') }}</div>
                        @endif
                    </div>

                    <div class="article-form-field" data-article-counter-wrap>
                        <label for="{{ $fieldPrefix }}_image_alt_text">Alt text gambar</label>
                        <input
                            id="{{ $fieldPrefix }}_image_alt_text"
                            type="text"
                            name="image_alt_text"
                            value="{{ old('image_alt_text', $article?->image_alt_text) }}"
                            class="form-control @error('image_alt_text') is-invalid @enderror"
                            placeholder="Deskripsi singkat gambar utama"
                            maxlength="125"
                            aria-invalid="@error('image_alt_text') true @else false @enderror"
                            aria-describedby="@error('image_alt_text') {{ $fieldPrefix }}_image_alt_text_error @else {{ $fieldPrefix }}_image_alt_text_hint @enderror"
                            data-article-counter-input
                        >
                        <div class="article-seo-toolbar">
                            <div id="{{ $fieldPrefix }}_image_alt_text_hint" class="article-field-hint">Disarankan singkat dan jelas, maksimal sekitar 125 karakter.</div>
                            <div class="article-char-counter">Karakter: <strong data-article-counter>0/125</strong></div>
                        </div>
                        @error('image_alt_text')
                            <div id="{{ $fieldPrefix }}_image_alt_text_error" class="article-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="article-col-full article-form-field" data-article-counter-wrap>
                        <label for="{{ $fieldPrefix }}_meta_description">Meta description</label>
                        <textarea
                            id="{{ $fieldPrefix }}_meta_description"
                            name="meta_description"
                            class="form-control @error('meta_description') is-invalid @enderror"
                            rows="4"
                            maxlength="180"
                            placeholder="Ringkasan pendek untuk mesin pencari"
                            aria-invalid="@error('meta_description') true @else false @enderror"
                            aria-describedby="@error('meta_description') {{ $fieldPrefix }}_meta_description_error @else {{ $fieldPrefix }}_meta_description_hint @enderror"
                            data-article-counter-input
                        >{{ old('meta_description', $article?->meta_description) }}</textarea>
                        <div class="article-seo-toolbar">
                            <div id="{{ $fieldPrefix }}_meta_description_hint" class="article-field-hint">Disarankan sekitar 150 sampai 160 karakter untuk ringkasan hasil pencarian.</div>
                            <div class="article-char-counter">Karakter: <strong data-article-counter>0/180</strong></div>
                        </div>
                        @error('meta_description')
                            <div id="{{ $fieldPrefix }}_meta_description_error" class="article-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="article-action-bar">
        <div class="article-action-bar__back">
            <a href="{{ $backRoute }}" class="btn btn-link text-decoration-none px-0">
                <i class="bi bi-arrow-left"></i>Kembali
            </a>
        </div>
        <div class="article-action-bar__group">
            <button class="btn btn-outline-secondary" type="submit" name="submit_action" value="draft" data-loading-text="Menyimpan draft..." data-article-submit-action="draft">
                <i class="bi bi-save2"></i>Simpan Draft
            </button>
            <button class="btn btn-outline-primary" type="submit" name="submit_action" value="preview" data-loading-text="Membuka pratinjau..." data-article-submit-action="preview">
                <i class="bi bi-display"></i>Pratinjau
            </button>
            <button class="btn btn-primary" type="submit" name="submit_action" value="publish" data-loading-text="{{ $isEdit ? 'Memperbarui artikel...' : 'Mempublikasikan artikel...' }}" data-article-submit-action="publish">
                <i class="bi bi-send-check"></i>{{ $isEdit ? 'Perbarui / Publikasikan' : 'Publikasikan' }}
            </button>
        </div>
    </div>
</div>
