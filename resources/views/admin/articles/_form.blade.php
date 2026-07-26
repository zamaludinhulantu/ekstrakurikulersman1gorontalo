@include('articles._form-fields', [
    'article' => $article,
    'fieldPrefix' => 'article',
    'backRoute' => route('admin.articles.index'),
    'showGeneralOption' => true,
    'extracurricularRequired' => false,
    'extracurricularPlaceholder' => 'Umum / semua kegiatan',
    'contentLabel' => 'Isi informasi dasar artikel agar mudah ditemukan, dipahami, dan ditampilkan rapi pada halaman publik.',
    'extracurriculars' => $extracurriculars,
    'contentCategories' => $contentCategories,
    'publicationStatuses' => $publicationStatuses,
])
