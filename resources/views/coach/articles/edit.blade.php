@extends('layouts.app')

@section('page_title', 'Edit Artikel Pembina')
@section('page_subtitle', 'Perbarui publikasi kegiatan binaan sebelum atau sesudah ditayangkan ke halaman publik dengan form yang lebih rapi.')

@include('articles._assets')

@section('content')
    <div class="article-workspace">
        <section class="article-surface article-surface--padded">
            <div class="article-editor-shell__head">
                <div>
                    <h2>Detail dan Edit Artikel</h2>
                    <p>Perbarui artikel binaan dengan susunan data, media, status publikasi, dan SEO sederhana yang lebih konsisten.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('coach.articles.preview', $article) }}" class="btn btn-outline-primary" target="_blank"><i class="bi bi-display"></i>Pratinjau</a>
                    <a href="{{ route('coach.articles.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </div>

            <div class="card-body pt-3">
                <form method="post" action="{{ route('coach.articles.update', $article) }}" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    @include('coach.articles._form', [
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
