@include('articles._form-fields', [
    'article' => $article,
    'fieldPrefix' => 'coach_article',
    'backRoute' => route('coach.articles.index'),
    'showGeneralOption' => false,
    'extracurricularRequired' => true,
    'extracurricularPlaceholder' => 'Pilih kegiatan binaan',
    'contentLabel' => 'Isi informasi dasar artikel pembinaan agar publikasinya jelas, konsisten, dan mudah dipantau siswa.',
    'extracurriculars' => $extracurriculars,
    'contentCategories' => $contentCategories,
    'publicationStatuses' => $publicationStatuses,
])
