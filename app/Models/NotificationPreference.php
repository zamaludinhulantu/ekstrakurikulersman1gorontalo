<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    public const CATEGORY_REGISTRATION_STATUS = 'registration_status';
    public const CATEGORY_SCHEDULE_ACTIVITY = 'schedule_activity';
    public const CATEGORY_SCHEDULE_CHANGES = 'schedule_changes';
    public const CATEGORY_ANNOUNCEMENTS = 'announcements';
    public const CATEGORY_TALENT_TEST = 'talent_test';
    public const CATEGORY_ATTENDANCE = 'attendance';
    public const CATEGORY_ASSESSMENT = 'assessment';
    public const CATEGORY_SCHOOL_NEWS = 'school_news';
    public const CATEGORY_ADMIN_ALERT = 'admin_alert';

    public const CATEGORIES = [
        self::CATEGORY_REGISTRATION_STATUS,
        self::CATEGORY_SCHEDULE_ACTIVITY,
        self::CATEGORY_SCHEDULE_CHANGES,
        self::CATEGORY_ANNOUNCEMENTS,
        self::CATEGORY_TALENT_TEST,
        self::CATEGORY_ATTENDANCE,
        self::CATEGORY_ASSESSMENT,
        self::CATEGORY_SCHOOL_NEWS,
        self::CATEGORY_ADMIN_ALERT,
    ];

    protected $fillable = [
        'user_id',
        'in_app_preferences',
        'push_preferences',
        'email_preferences',
    ];

    protected function casts(): array
    {
        return [
            'in_app_preferences' => 'array',
            'push_preferences' => 'array',
            'email_preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function defaultInAppPreferences(): array
    {
        return array_fill_keys(self::CATEGORIES, true);
    }

    public static function defaultPushPreferences(): array
    {
        return [
            self::CATEGORY_REGISTRATION_STATUS => true,
            self::CATEGORY_SCHEDULE_ACTIVITY => true,
            self::CATEGORY_SCHEDULE_CHANGES => true,
            self::CATEGORY_ANNOUNCEMENTS => true,
            self::CATEGORY_TALENT_TEST => true,
            self::CATEGORY_ATTENDANCE => false,
            self::CATEGORY_ASSESSMENT => true,
            self::CATEGORY_SCHOOL_NEWS => false,
            self::CATEGORY_ADMIN_ALERT => true,
        ];
    }

    public static function defaultEmailPreferences(): array
    {
        return array_fill_keys(self::CATEGORIES, false);
    }

    public function mergedInAppPreferences(): array
    {
        return array_replace(self::defaultInAppPreferences(), $this->in_app_preferences ?? []);
    }

    public function mergedPushPreferences(): array
    {
        return array_replace(self::defaultPushPreferences(), $this->push_preferences ?? []);
    }
}
