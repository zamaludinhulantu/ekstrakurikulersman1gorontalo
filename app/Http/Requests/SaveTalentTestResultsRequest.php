<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTalentTestResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(\App\Models\User::ROLE_COACH) ?? false;
    }

    public function rules(): array
    {
        return [
            'publish' => ['nullable', 'boolean'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*.participant_id' => ['required', 'integer', 'exists:talent_test_participants,id'],
            'participants.*.attendance_status' => ['required', Rule::in(['pending', 'present', 'absent', 'sick', 'permission'])],
            'participants.*.attendance_notes' => ['nullable', 'string'],
            'participants.*.ability_category' => ['nullable', 'string', 'max:120'],
            'participants.*.training_group' => ['nullable', 'string', 'max:120'],
            'participants.*.recommended_role' => ['nullable', 'string', 'max:120'],
            'participants.*.recommendation' => ['nullable', 'string'],
            'participants.*.coach_notes' => ['nullable', 'string'],
            'participants.*.internal_notes' => ['nullable', 'string'],
            'participants.*.needs_retest' => ['nullable', 'boolean'],
            'participants.*.retest_schedule_id' => ['nullable', 'integer', 'exists:schedules,id'],
            'participants.*.scores' => ['nullable', 'array'],
            'participants.*.scores.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'participants.*.score_notes' => ['nullable', 'array'],
            'participants.*.score_notes.*' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $participants = collect($this->input('participants', []))
            ->map(function (array $participant): array {
                $participant['needs_retest'] = filter_var($participant['needs_retest'] ?? false, FILTER_VALIDATE_BOOLEAN);

                return $participant;
            })
            ->all();

        $this->merge([
            'publish' => $this->boolean('publish'),
            'participants' => $participants,
        ]);
    }
}
