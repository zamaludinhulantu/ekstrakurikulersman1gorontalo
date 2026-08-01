<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Extracurricular;
use App\Models\User;
use App\Support\ArticleManager;
use App\Support\ScheduledArticlePublisher;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_save_draft_and_later_publish_article(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $extracurricular = Extracurricular::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Berita Prestasi Ekskul',
                'content_category' => Article::CATEGORY_ACHIEVEMENT,
                'excerpt' => 'Ringkasan prestasi ekskul terbaru yang tampil di halaman publik.',
                'content' => '<p>Isi lengkap berita prestasi ekskul terbaru.</p>',
                'extracurricular_id' => $extracurricular->id,
                'publication_status' => Article::STATUS_DRAFT,
                'submit_action' => 'draft',
            ])
            ->assertRedirect(route('admin.articles.index', ['tab' => 'list']));

        $article = Article::query()->where('title', 'Berita Prestasi Ekskul')->firstOrFail();
        $this->assertSame(Article::STATUS_DRAFT, $article->publication_status);

        $this->actingAs($admin)
            ->patch(route('admin.articles.publish', $article))
            ->assertRedirect(route('admin.articles.index'));

        $article->refresh();
        $this->assertSame(Article::STATUS_PUBLISHED, $article->publication_status);

        $this->get(route('public.articles.index'))
            ->assertOk()
            ->assertSee('Berita Prestasi Ekskul');

        $this->get(route('public.articles.show', $article->slug))
            ->assertOk()
            ->assertSee('Isi lengkap berita prestasi ekskul terbaru.', false);
    }

    public function test_scheduled_article_does_not_appear_before_publish_time(): void
    {
        Carbon::setTestNow('2026-07-25 10:00:00');

        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Artikel Jadwal Besok',
                'content_category' => Article::CATEGORY_ACTIVITY_NEWS,
                'excerpt' => 'Ringkasan artikel jadwal besok untuk diuji.',
                'content' => '<p>Isi artikel terjadwal.</p>',
                'publication_status' => Article::STATUS_SCHEDULED,
                'publish_date' => '2026-07-26',
                'publish_time' => '08:00',
                'submit_action' => 'draft',
            ]);

        $article = Article::query()->where('title', 'Artikel Jadwal Besok')->firstOrFail();

        $this->get(route('public.articles.index'))
            ->assertOk()
            ->assertDontSee('Artikel Jadwal Besok');

        Carbon::setTestNow();
    }

    public function test_admin_can_preview_draft_article(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $article = Article::query()->create([
            'title' => 'Preview Draft Admin',
            'slug' => 'preview-draft-admin',
            'excerpt' => 'Ringkasan draft admin untuk preview.',
            'content' => '<p>Isi draft admin.</p>',
            'content_category' => Article::CATEGORY_INFORMATION,
            'published_by' => $admin->id,
            'publication_status' => Article::STATUS_DRAFT,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.articles.preview', $article))
            ->assertOk()
            ->assertSee('Mode pratinjau.')
            ->assertSee('Preview Draft Admin');
    }

    public function test_coach_can_manage_own_article_but_other_coach_cannot_edit_it(): void
    {
        $coachUser = User::query()->where('email', 'pembina1@gmail.com')->firstOrFail();
        $otherCoachUser = User::query()->where('email', 'pembina2@gmail.com')->firstOrFail();
        $extracurricular = $coachUser->coach->extracurriculars()->firstOrFail();

        $this->actingAs($coachUser)
            ->post(route('coach.articles.store'), [
                'title' => 'Artikel Kegiatan Pembina',
                'content_category' => Article::CATEGORY_ACTIVITY_NEWS,
                'excerpt' => 'Ringkasan artikel pembina untuk publikasi kegiatan siswa.',
                'content' => '<p>Isi artikel pembina untuk publikasi kegiatan.</p>',
                'extracurricular_id' => $extracurricular->id,
                'publication_status' => Article::STATUS_PUBLISHED,
                'submit_action' => 'publish',
            ])
            ->assertRedirect();

        $article = Article::query()->where('title', 'Artikel Kegiatan Pembina')->firstOrFail();

        $this->actingAs($coachUser)
            ->get(route('coach.articles.edit', $article))
            ->assertOk();

        $this->actingAs($otherCoachUser)
            ->get(route('coach.articles.edit', $article))
            ->assertForbidden();
    }

    public function test_article_image_must_not_exceed_two_megabytes(): void
    {
        Storage::fake('public');

        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $largeImage = UploadedFile::fake()->image('artikel-besar.jpg')->size(2500);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Artikel Dengan Gambar Besar',
                'content_category' => Article::CATEGORY_INFORMATION,
                'excerpt' => 'Ringkasan singkat artikel dengan gambar besar untuk validasi.',
                'content' => '<p>Isi artikel dengan file terlalu besar.</p>',
                'publication_status' => Article::STATUS_PUBLISHED,
                'submit_action' => 'publish',
                'image' => $largeImage,
            ])
            ->assertSessionHasErrors('image');
    }

    public function test_slug_is_uniqued_automatically_and_manual_duplicate_is_rejected(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        Article::query()->create([
            'title' => 'Artikel Awal',
            'slug' => 'artikel-awal',
            'excerpt' => 'Ringkasan artikel awal untuk pengujian slug duplikat.',
            'content' => '<p>Isi awal.</p>',
            'content_category' => Article::CATEGORY_INFORMATION,
            'published_by' => $admin->id,
            'publication_status' => Article::STATUS_DRAFT,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Artikel Awal',
                'slug' => 'artikel-awal',
                'content_category' => Article::CATEGORY_INFORMATION,
                'excerpt' => 'Ringkasan artikel kedua untuk pengujian slug duplikat.',
                'content' => '<p>Isi kedua.</p>',
                'publication_status' => Article::STATUS_DRAFT,
                'submit_action' => 'draft',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_archived_article_is_hidden_from_public_pages(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $article = Article::query()->create([
            'title' => 'Artikel Arsip',
            'slug' => 'artikel-arsip',
            'excerpt' => 'Ringkasan artikel arsip untuk pengujian halaman publik.',
            'content' => '<p>Isi arsip.</p>',
            'content_category' => Article::CATEGORY_INFORMATION,
            'published_by' => $admin->id,
            'publication_status' => Article::STATUS_ARCHIVED,
            'is_active' => true,
        ]);

        $this->get(route('public.articles.index'))
            ->assertOk()
            ->assertDontSee('Artikel Arsip');

        $this->get(route('public.articles.show', $article->slug))
            ->assertNotFound();
    }

    public function test_public_articles_page_can_filter_by_search_category_and_activity(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $extracurricular = Extracurricular::query()->firstOrFail();

        Article::query()->create([
            'title' => 'Prestasi Pramuka Nasional',
            'slug' => 'prestasi-pramuka-nasional',
            'excerpt' => 'Ringkasan prestasi pramuka tingkat nasional.',
            'content' => '<p>Isi artikel prestasi pramuka.</p>',
            'content_category' => Article::CATEGORY_ACHIEVEMENT,
            'extracurricular_id' => $extracurricular->id,
            'published_by' => $admin->id,
            'publication_status' => Article::STATUS_PUBLISHED,
            'publish_at' => now()->subDay(),
            'is_active' => true,
        ]);

        Article::query()->create([
            'title' => 'Pengumuman Kegiatan Umum',
            'slug' => 'pengumuman-kegiatan-umum',
            'excerpt' => 'Ringkasan pengumuman umum sekolah.',
            'content' => '<p>Isi artikel umum.</p>',
            'content_category' => Article::CATEGORY_PUBLIC_NOTICE,
            'published_by' => $admin->id,
            'publication_status' => Article::STATUS_PUBLISHED,
            'publish_at' => now()->subDays(2),
            'is_active' => true,
        ]);

        $this->get(route('public.articles.index', [
            'search' => 'Pramuka',
            'content_category' => Article::CATEGORY_ACHIEVEMENT,
            'extracurricular_id' => $extracurricular->id,
        ]))
            ->assertOk()
            ->assertSee('Prestasi Pramuka Nasional')
            ->assertDontSee('Pengumuman Kegiatan Umum');
    }

    public function test_public_article_detail_shows_related_activity_and_related_articles(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $extracurricular = Extracurricular::query()->firstOrFail();

        $article = Article::query()->create([
            'title' => 'Liputan Pramuka Sekolah',
            'slug' => 'liputan-pramuka-sekolah',
            'excerpt' => 'Ringkasan liputan pramuka sekolah.',
            'content' => '<p>Isi liputan pramuka sekolah.</p>',
            'content_category' => Article::CATEGORY_ACTIVITY_NEWS,
            'extracurricular_id' => $extracurricular->id,
            'published_by' => $admin->id,
            'publication_status' => Article::STATUS_PUBLISHED,
            'publish_at' => now()->subDay(),
            'is_active' => true,
        ]);

        Article::query()->create([
            'title' => 'Liputan Pramuka Lanjutan',
            'slug' => 'liputan-pramuka-lanjutan',
            'excerpt' => 'Ringkasan liputan lanjutan pramuka.',
            'content' => '<p>Isi liputan lanjutan.</p>',
            'content_category' => Article::CATEGORY_ACTIVITY_NEWS,
            'extracurricular_id' => $extracurricular->id,
            'published_by' => $admin->id,
            'publication_status' => Article::STATUS_PUBLISHED,
            'publish_at' => now()->subHours(10),
            'is_active' => true,
        ]);

        $this->get(route('public.articles.show', $article->slug))
            ->assertOk()
            ->assertSee('Kegiatan Terkait')
            ->assertSee($extracurricular->name)
            ->assertSee('Liputan Pramuka Lanjutan')
            ->assertSee('Kembali ke daftar berita');
    }

    public function test_management_statistics_reconcile_all_article_statuses(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        Article::query()->delete();

        foreach ([
            [Article::STATUS_DRAFT, null, true],
            [Article::STATUS_SCHEDULED, now()->addDay(), true],
            [Article::STATUS_PUBLISHED, now()->subDay(), true],
            [Article::STATUS_PUBLISHED, now()->subDays(2), true, now()->subHour()],
            [Article::STATUS_ARCHIVED, null, true],
            [Article::STATUS_INACTIVE, null, false],
        ] as $index => $state) {
            Article::query()->create([
                'title' => 'Konten Statistik Nomor '.($index + 1),
                'slug' => 'konten-statistik-'.($index + 1),
                'excerpt' => 'Ringkasan konten untuk memastikan statistik pengelolaan akurat.',
                'content' => '<p>Isi konten statistik.</p>',
                'content_category' => Article::CATEGORY_INFORMATION,
                'published_by' => $admin->id,
                'publication_status' => $state[0],
                'publish_at' => $state[1],
                'expires_at' => $state[3] ?? null,
                'is_active' => $state[2],
            ]);
        }

        $statistics = app(ArticleManager::class)->statistics(Article::query());

        $this->assertSame(6, $statistics['total']);
        $this->assertSame(1, $statistics['draft']);
        $this->assertSame(1, $statistics['scheduled']);
        $this->assertSame(1, $statistics['published']);
        $this->assertSame(1, $statistics['expired']);
        $this->assertSame(1, $statistics['archived']);
        $this->assertSame(1, $statistics['inactive']);
        $this->assertSame($statistics['total'], array_sum([
            $statistics['draft'],
            $statistics['scheduled'],
            $statistics['published'],
            $statistics['expired'],
            $statistics['archived'],
            $statistics['inactive'],
        ]));
    }

    public function test_coach_list_and_activity_filter_are_scoped_to_assignments(): void
    {
        $coach = User::query()->where('email', 'pembina1@gmail.com')->firstOrFail();
        $otherCoach = User::query()->where('email', 'pembina2@gmail.com')->firstOrFail();
        $ownActivity = $coach->coach->extracurriculars()->firstOrFail();
        $otherActivity = $otherCoach->coach->extracurriculars()->firstOrFail();

        Article::query()->create([
            'title' => 'Konten Milik Pembina Pertama',
            'slug' => 'konten-milik-pembina-pertama',
            'excerpt' => 'Ringkasan konten milik pembina pertama untuk pengujian cakupan.',
            'content' => '<p>Isi konten pembina pertama.</p>',
            'content_category' => Article::CATEGORY_ACTIVITY_NEWS,
            'extracurricular_id' => $ownActivity->id,
            'published_by' => $coach->id,
            'publication_status' => Article::STATUS_DRAFT,
            'is_active' => true,
        ]);
        Article::query()->create([
            'title' => 'Konten Rahasia Pembina Lain',
            'slug' => 'konten-rahasia-pembina-lain',
            'excerpt' => 'Ringkasan konten milik pembina lain yang tidak boleh terlihat.',
            'content' => '<p>Isi konten pembina lain.</p>',
            'content_category' => Article::CATEGORY_ACTIVITY_NEWS,
            'extracurricular_id' => $otherActivity->id,
            'published_by' => $otherCoach->id,
            'publication_status' => Article::STATUS_DRAFT,
            'is_active' => true,
        ]);

        $this->actingAs($coach)
            ->get(route('coach.articles.index'))
            ->assertOk()
            ->assertSee('Konten Milik Pembina Pertama')
            ->assertDontSee('Konten Rahasia Pembina Lain');

        $this->actingAs($coach)
            ->get(route('coach.articles.index', ['extracurricular_id' => $otherActivity->id]))
            ->assertForbidden();
    }

    public function test_duplicate_image_is_only_deleted_after_last_reference_is_removed(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('articles/shared.webp', 'image-content');
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $this->actingAs($admin);

        $article = Article::query()->create([
            'title' => 'Konten Dengan Media Bersama',
            'slug' => 'konten-dengan-media-bersama',
            'excerpt' => 'Ringkasan konten yang memiliki media bersama pada hasil duplikasi.',
            'content' => '<p>Isi konten dengan media.</p>',
            'content_category' => Article::CATEGORY_INFORMATION,
            'published_by' => $admin->id,
            'image_path' => 'articles/shared.webp',
            'publication_status' => Article::STATUS_DRAFT,
            'is_active' => true,
        ]);

        $duplicate = app(ArticleManager::class)->duplicate($article);
        app(ArticleManager::class)->delete($duplicate);
        Storage::disk('public')->assertExists('articles/shared.webp');

        app(ArticleManager::class)->delete($article);
        Storage::disk('public')->assertMissing('articles/shared.webp');
    }

    public function test_published_article_must_be_archived_before_permanent_deletion(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $article = Article::query()->create([
            'title' => 'Konten Aktif Tidak Boleh Dihapus',
            'slug' => 'konten-aktif-tidak-boleh-dihapus',
            'excerpt' => 'Ringkasan konten aktif yang harus diarsipkan sebelum dihapus.',
            'content' => '<p>Isi konten aktif.</p>',
            'content_category' => Article::CATEGORY_INFORMATION,
            'published_by' => $admin->id,
            'publication_status' => Article::STATUS_PUBLISHED,
            'publish_at' => now()->subHour(),
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.articles.destroy', $article))
            ->assertSessionHasErrors('article');
        $this->assertDatabaseHas('articles', ['id' => $article->id]);

        $this->actingAs($admin)->patch(route('admin.articles.archive', $article))->assertRedirect();
        $this->actingAs($admin)->delete(route('admin.articles.destroy', $article))->assertRedirect();
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_due_scheduled_article_is_published_and_notified_only_once(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $article = Article::query()->create([
            'title' => 'Konten Terjadwal Siap Tayang',
            'slug' => 'konten-terjadwal-siap-tayang',
            'excerpt' => 'Ringkasan konten terjadwal yang siap dipublikasikan otomatis.',
            'content' => '<p>Isi konten terjadwal.</p>',
            'content_category' => Article::CATEGORY_INFORMATION,
            'published_by' => $admin->id,
            'publication_status' => Article::STATUS_SCHEDULED,
            'publish_at' => now()->subMinute(),
            'is_active' => true,
        ]);
        $before = DB::table('notifications')->count();

        $this->assertSame(1, app(ScheduledArticlePublisher::class)->publishDue());
        $afterFirstRun = DB::table('notifications')->count();
        $this->assertGreaterThan($before, $afterFirstRun);
        $this->assertSame(0, app(ScheduledArticlePublisher::class)->publishDue());
        $this->assertSame($afterFirstRun, DB::table('notifications')->count());
        $this->assertSame(Article::STATUS_PUBLISHED, $article->refresh()->publication_status);
    }
}
