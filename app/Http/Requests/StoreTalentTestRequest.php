<?php

namespace App\Http\Requests;

use App\Models\Coach;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTalentTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_COACH) ?? false;
    }

    public function rules(): array
    {
        $coachId = $this->user()?->coach?->id;
        $allowedExtracurricularIds = $coachId
            ? Coach::findOrFail($coachId)->extracurriculars()->pluck('extracurriculars.id')->all()
            : [];

        return [
            'extracurricular_id' => ['required', Rule::in($allowedExtracurricularIds)],
            'title' => ['required', 'string', 'max:255'],
            'activity_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'equipment' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'participant_registration_ids' => ['required', 'array', 'min:1'],
            'participant_registration_ids.*' => ['integer', 'exists:registrations,id'],
        ];
    }
}
