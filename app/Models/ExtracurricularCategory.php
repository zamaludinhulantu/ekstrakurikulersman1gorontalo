<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ExtracurricularCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'slug',
        'label',
        'description',
        'catalog_title',
        'catalog_subtitle',
        'icon',
        'tone',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    public static function catalogDefinitions(): Collection
    {
        return Cache::rememberForever('extracurricular.category-definitions.records.v1', function (): Collection {
            $defaults = collect(Extracurricular::defaultCategoryDefinitions());

            if (! Schema::hasTable('extracurricular_categories')) {
                return $defaults->values();
            }

            $records = self::query()->ordered()->get()->keyBy('key');

            return $defaults->map(function (array $definition, string $key) use ($records): array {
                $record = $records->get($key);
                if (! $record) {
                    return $definition;
                }

                return [
                    'key' => $definition['key'],
                    'slug' => $record->slug ?: $definition['slug'],
                    'label' => $record->label ?: $definition['label'],
                    'icon' => $record->icon ?: $definition['icon'],
                    'tone' => $record->tone ?: $definition['tone'],
                    'description' => $record->description ?: $definition['description'],
                    'catalog_title' => $record->catalog_title ?: $definition['catalog_title'],
                    'catalog_subtitle' => $record->catalog_subtitle ?: $definition['catalog_subtitle'],
                    'image' => $record->image_path
                        ? Extracurricular::assetUrl($record->image_path, $record->updated_at?->timestamp)
                        : $definition['image'],
                    'image_path' => $record->image_path,
                    'sort_order' => $record->sort_order,
                    'is_active' => $record->is_active,
                ];
            })->filter(fn (array $definition) => $definition['is_active'] ?? true)->values();
        });
    }
}
