<?php

namespace App\Support;

use App\Models\Registration;
use Illuminate\Database\Eloquent\Builder;

final class RegistrationStatusPresenter
{
    public static function labels(): array
    {
        return [
            Registration::STATUS_PENDING => 'Menunggu',
            Registration::DISPLAY_STATUS_WAITING_TEST => 'Menunggu Tes',
            Registration::DISPLAY_STATUS_SCHEDULED_TEST => 'Tes Dijadwalkan',
            Registration::DISPLAY_STATUS_CANCELLATION_REQUESTED => 'Menunggu Konfirmasi Batal',
            Registration::STATUS_APPROVED => 'Diterima',
            Registration::STATUS_REJECTED => 'Ditolak',
            Registration::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public static function managementLabels(): array
    {
        return array_filter(
            static::labels(),
            fn (string $status): bool => $status !== Registration::STATUS_CANCELLED,
            ARRAY_FILTER_USE_KEY
        );
    }

    public static function label(string $status): string
    {
        return static::labels()[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function tone(string $status): string
    {
        return match ($status) {
            Registration::STATUS_APPROVED => 'success',
            Registration::STATUS_REJECTED => 'danger',
            Registration::DISPLAY_STATUS_WAITING_TEST,
            Registration::DISPLAY_STATUS_SCHEDULED_TEST => 'warning',
            Registration::DISPLAY_STATUS_CANCELLATION_REQUESTED => 'danger',
            Registration::STATUS_CANCELLED => 'cancelled',
            default => 'info',
        };
    }

    public static function statistics(Builder $scope): array
    {
        $summary = (clone $scope)
            ->selectRaw(
                'COUNT(*) as total_count,
                SUM(CASE WHEN status = ? AND (willing_to_take_test = 0 OR willing_to_take_test IS NULL) THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = ? AND willing_to_take_test = 1 THEN 1 ELSE 0 END) as test_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_count',
                [
                    Registration::STATUS_PENDING,
                    Registration::STATUS_PENDING,
                    Registration::STATUS_APPROVED,
                    Registration::STATUS_REJECTED,
                    Registration::STATUS_CANCELLED,
                ]
            )
            ->first();

        return [
            'total' => (int) ($summary->total_count ?? 0),
            'pending' => (int) ($summary->pending_count ?? 0),
            'test' => (int) ($summary->test_count ?? 0),
            'approved' => (int) ($summary->approved_count ?? 0),
            'closed' => (int) (($summary->rejected_count ?? 0) + ($summary->cancelled_count ?? 0)),
        ];
    }
}
