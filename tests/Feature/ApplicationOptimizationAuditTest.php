<?php

namespace Tests\Feature;

use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use App\Support\UploadedImageOptimizer;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApplicationOptimizationAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_activity_with_operational_history_cannot_be_deleted(): void
    {
        $activity = Extracurricular::query()->create([
            'name' => 'Kegiatan Audit Dengan Riwayat',
            'description' => 'Kegiatan untuk menguji perlindungan riwayat.',
            'is_active' => true,
        ]);
        $student = Student::query()->firstOrFail();
        Registration::query()->create([
            'student_id' => $student->id,
            'extracurricular_id' => $activity->id,
            'registration_date' => now()->toDateString(),
            'status' => Registration::STATUS_APPROVED,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.extracurriculars.destroy', $activity))
            ->assertRedirect(route('admin.extracurriculars.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('extracurriculars', ['id' => $activity->id]);
        $this->assertDatabaseHas('registrations', ['extracurricular_id' => $activity->id]);
    }

    public function test_unused_activity_can_still_be_deleted(): void
    {
        $activity = Extracurricular::query()->create([
            'name' => 'Kegiatan Audit Tanpa Riwayat',
            'description' => 'Kegiatan kosong yang aman dihapus.',
            'is_active' => false,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.extracurriculars.destroy', $activity))
            ->assertRedirect(route('admin.extracurriculars.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('extracurriculars', ['id' => $activity->id]);
    }

    public function test_activity_detail_uses_bounded_paginators(): void
    {
        $activity = Extracurricular::query()->firstOrFail();

        $this->actingAs($this->admin())
            ->get(route('admin.extracurriculars.show', $activity))
            ->assertOk()
            ->assertViewHas('schedules', fn ($items): bool => $items->perPage() === 10)
            ->assertViewHas('registrations', fn ($items): bool => $items->perPage() === 20)
            ->assertViewHas('achievements', fn ($items): bool => $items->perPage() === 10);
    }

    public function test_uploaded_images_are_resized_without_changing_their_format(): void
    {
        $directory = storage_path('framework/testing/optimized-images');
        File::deleteDirectory($directory);

        try {
            $path = app(UploadedImageOptimizer::class)->store(
                UploadedFile::fake()->image('large.png', 2400, 1800),
                $directory,
                'optimized-images',
                maxWidth: 1200,
                maxHeight: 900,
            );
            $absolutePath = $directory.DIRECTORY_SEPARATOR.basename($path);
            $dimensions = getimagesize($absolutePath);

            $this->assertFileExists($absolutePath);
            $this->assertSame('image/png', $dimensions['mime']);
            $this->assertLessThanOrEqual(1200, $dimensions[0]);
            $this->assertLessThanOrEqual(900, $dimensions[1]);
        } finally {
            File::deleteDirectory($directory);
        }
    }

    private function admin(): User
    {
        return User::query()->where('email', 'admin@gmail.com')->firstOrFail();
    }
}
