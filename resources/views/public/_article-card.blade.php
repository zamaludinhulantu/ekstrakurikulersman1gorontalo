@php
    $revealDelay = $revealDelay ?? null;
@endphp

<article class="public-article-card h-100" data-reveal @if($revealDelay !== null) style="--reveal-delay: {{ (int) $revealDelay }}ms;" @endif>
    <a href="{{ route('public.articles.show', $article->slug) }}" class="public-article-card__media">
        @if($article->cover_image_url)
            <img
                src="{{ $article->cover_image_url }}"
                alt="{{ $article->image_alt_text_label }}"
                class="public-article-card__image"
                loading="lazy"
                decoding="async"
                width="640"
                height="360"
            >
            <span class="public-article-card__media-overlay"></span>
        @else
            <span class="public-article-card__placeholder">
                <span class="public-article-card__placeholder-icon"><i class="bi bi-newspaper"></i></span>
                <span class="public-article-card__placeholder-text">{{ $article->content_category_label }}</span>
            </span>
        @endif
    </a>

    <div class="public-article-card__body">
        <div class="public-article-card__meta">
            <span class="public-article-card__chip">
                <i class="bi bi-calendar-event"></i>{{ optional($article->publish_at ?? $article->created_at)->translatedFormat('d M Y') ?? '-' }}
            </span>
            <span class="public-article-card__chip public-article-card__chip--category">
                <i class="bi bi-bookmark-star"></i>{{ $article->content_category_label }}
            </span>
        </div>

        <h3 class="public-article-card__title">{{ $article->title }}</h3>
        <p class="public-article-card__excerpt">{{ \Illuminate\Support\Str::limit($article->excerpt ?: strip_tags($article->content), 150) }}</p>

        <div class="public-article-card__footer">
            <span class="public-article-card__author">Ditulis oleh {{ $article->publisher?->name ?? 'Admin' }}</span>
            <a href="{{ route('public.articles.show', $article->slug) }}" class="btn btn-outline-primary btn-sm public-article-card__button">
                <span>Baca Artikel</span>
                <i class="bi bi-arrow-right-circle"></i>
            </a>
        </div>
    </div>
</article>
