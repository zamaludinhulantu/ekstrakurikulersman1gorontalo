<?php

namespace App\Support;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationCancellationManager
{
    public function request(Registration $registration): Registration
    {
        return DB::transaction(function () use ($registration): Registration {
            $locked = Registration::query()->lockForUpdate()->findOrFail($registration->id);

            if (! $locked->isCancellationRequested()) {
                $locked->update(['cancellation_requested_at' => now()]);
            }

            return $locked->refresh();
        });
    }

    public function approve(
        Registration $registration,
        ?User $reviewer,
        string $note,
        bool $requireRequest = true
    ): Registration
    {
        return DB::transaction(function () use ($registration, $reviewer, $note, $requireRequest): Registration {
            $locked = Registration::query()->lockForUpdate()->findOrFail($registration->id);

            if ($requireRequest && ! $locked->isCancellationRequested()) {
                throw ValidationException::withMessages([
                    'cancellation' => 'Permintaan pembatalan sudah tidak tersedia.',
                ]);
            }

            $locked->scheduleParticipants()->delete();
            $locked->talentTestParticipants()->delete();
            $locked->update([
                'status' => Registration::STATUS_CANCELLED,
                'notes' => $this->appendNote($locked->notes, $note),
                'willing_to_take_test' => false,
                'verified_by' => $reviewer?->id,
                'verified_at' => $reviewer ? now() : null,
                'cancellation_requested_at' => null,
            ]);

            return $locked->refresh();
        });
    }

    public function reject(Registration $registration, User $reviewer, string $note): Registration
    {
        return DB::transaction(function () use ($registration, $reviewer, $note): Registration {
            $locked = Registration::query()->lockForUpdate()->findOrFail($registration->id);

            if (! $locked->isCancellationRequested()) {
                throw ValidationException::withMessages([
                    'cancellation' => 'Permintaan pembatalan sudah tidak tersedia.',
                ]);
            }

            $locked->update([
                'notes' => $this->appendNote($locked->notes, $note),
                'verified_by' => $reviewer->id,
                'verified_at' => now(),
                'cancellation_requested_at' => null,
            ]);

            return $locked->refresh();
        });
    }

    private function appendNote(?string $existingNotes, string $newNote): string
    {
        $existing = trim((string) $existingNotes);

        return $existing !== '' ? $existing."\n\n".$newNote : $newNote;
    }
}
