@extends('layouts.app')

@section('page_title', 'Edit Berita / Artikel')
@section('page_subtitle', 'Perbarui konten, jadwal tayang, dan pengaturan publikasi artikel dengan tampilan form yang lebih rapi.')

@include('articles._assets')

@section('content')
    <div class="article-workspace">
        <section class="article-surface article-surface--padded">
            <div class="article-editor-shell__head">
                <div>
                    <h2>Detail dan Edit Artikel</h2>
                    <p>Perbarui informasi utama, media, pengaturan publikasi, dan SEO sederhana tanpa mengubah alur backend yang sudah berjalan.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.articles.preview', $article) }}" class="btn btn-outline-primary" target="_blank"><i class="bi bi-display"></i>Pratinjau</a>
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </div>

            <div class="card-body pt-3">
                <form method="post" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    @include('admin.articles._form', [
                        'article' => $article,
                        'extracurriculars' => $extracurriculars,
                        'contentCategories' => $contentCategories,
                        'publicationStatuses' => $publicationStatuses,
                    ])
                </form>
            </div>
        </section>
    </div>
@endsection
