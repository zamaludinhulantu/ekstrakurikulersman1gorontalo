<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRegistrationRequest extends FormRequest
{
    private ?\App\Models\Extracurricular $resolvedExtracurricular = null;

    public function authorize(): bool
    {
        return $this->user()?->hasRole(\App\Models\User::ROLE_STUDENT) ?? false;
    }

    public function rules(): array
    {
        return [
            'selected_branch' => ['nullable', 'string', 'max:255'],
            'motivation_reason' => ['nullable', 'string'],
            'goal_statement' => ['nullable', 'string'],
            'prior_experience' => ['nullable', 'string'],
            'current_skills' => ['nullable', 'string'],
            'primary_talent' => ['nullable', 'string', 'max:255'],
            'preferred_position' => ['nullable', 'string', 'max:255'],
            'achievement_history' => ['nullable', 'string'],
            'achievement_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:3072'],
            'willing_to_take_test' => ['nullable', 'boolean'],
            'student_notes' => ['nullable', 'string'],
            'allow_public_profile' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $extracurricular = $this->resolveExtracurricular();

            if (! $extracurricular || ! $extracurricular->has_branches) {
                return;
            }

            $selectedBranch = trim((string) $this->input('selected_branch'));
            $allowedBranches = collect($extracurricular->branch_options)
                ->filter(fn ($value) => filled($value))
                ->values();

            if ($selectedBranch === '') {
                $validator->errors()->add('selected_branch', 'Pilih cabang kegiatan terlebih dahulu.');

                return;
            }

            if (! $allowedBranches->contains($selectedBranch)) {
                $validator->errors()->add('selected_branch', 'Cabang kegiatan yang dipilih tidak valid.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $motivationReason = trim((string) $this->input('motivation_reason'));
        $experienceSummary = trim((string) $this->input('current_skills'));
        $existingExperience = trim((string) $this->input('prior_experience'));

        $this->merge([
            'selected_branch' => trim((string) $this->input('selected_branch')) !== ''
                ? trim((string) $this->input('selected_branch'))
                : null,
            'goal_statement' => trim((string) $this->input('goal_statement')) !== ''
                ? $this->input('goal_statement')
                : $motivationReason,
            'prior_experience' => $existingExperience !== ''
                ? $this->input('prior_experience')
                : ($experienceSummary !== '' ? $experienceSummary : null),
            'willing_to_take_test' => $this->boolean('willing_to_take_test'),
            'allow_public_profile' => $this->boolean('allow_public_profile'),
        ]);
    }

    private function resolveExtracurricular(): ?\App\Models\Extracurricular
    {
        if ($this->resolvedExtracurricular !== null) {
            return $this->resolvedExtracurricular;
        }

        $routeValue = $this->route('extracurricular');
        if ($routeValue instanceof \App\Models\Extracurricular) {
            return $this->resolvedExtracurricular = $routeValue;
        }

        $registration = $this->route('registration');
        if ($registration instanceof \App\Models\Registration) {
            return $this->resolvedExtracurricular = $registration->extracurricular()->first();
        }

        return null;
    }
}
