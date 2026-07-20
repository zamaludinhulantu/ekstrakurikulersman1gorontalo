@extends('layouts.app')

@section('page_title', 'Ubah Pendaftaran')
@section('page_subtitle', 'Perbarui pendaftaran secara ringkas sebelum diverifikasi pembina.')

@push('styles')
    <style>
        .registration-edit-card {
            border-radius: 28px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(248, 251, 255, 0.95));
            box-shadow: 0 18px 32px rgba(16, 35, 63, 0.07);
        }

        .registration-edit-card .card-body {
            padding: 1.2rem;
        }

        .registration-textarea {
            min-height: 110px;
            resize: vertical;
        }

        .registration-accordion summary {
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            cursor: pointer;
        }

        .registration-accordion summary::-webkit-details-marker {
            display: none;
        }

        .registration-accordion summary strong {
            display: block;
            color: #18334f;
        }

        .registration-accordion summary small {
            display: block;
            color: #6a7f98;
            margin-top: 0.15rem;
        }

        .registration-accordion summary i {
            transition: transform 0.18s ease;
        }

        .registration-accordion[open] summary i {
            transform: rotate(180deg);
        }

        .registration-accordion-body {
            padding-top: 1rem;
        }

        @media (max-width: 767.98px) {
            .registration-edit-card .card-body {
                padding: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="card registration-edit-card">
        <div class="card-header">Pendaftaran {{ $registration->extracurricular->name }}</div>
        <div class="card-body">
            <form method="post" action="{{ route('student.registrations.update', $registration) }}" enctype="multipart/form-data">
                @csrf
                @method('put')
                @include('partials.registration-talent-fields', ['registration' => $registration, 'extracurricular' => $registration->extracurricular])
                <div class="form-actions mt-3">
                    <a href="{{ route('student.registrations.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                    <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan..."><i class="bi bi-save"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.querySelector('.registration-edit-card form');
            const motivation = document.getElementById('motivation_reason');
            const count = document.getElementById('motivationCount');
            const goalStatement = document.querySelector('input[name="goal_statement"]');
            const currentSkills = document.getElementById('current_skills');
            const priorExperience = document.querySelector('input[name="prior_experience"]');

            const syncTextFields = function () {
                if (goalStatement && motivation) {
                    goalStatement.value = motivation.value;
                }

                if (priorExperience && currentSkills) {
                    priorExperience.value = currentSkills.value;
                }
            };

            const updateCount = function () {
                if (count && motivation) {
                    count.textContent = motivation.value.length;
                }
            };

            motivation?.addEventListener('input', function () {
                updateCount();
                syncTextFields();
            });

            currentSkills?.addEventListener('input', syncTextFields);
            updateCount();
            syncTextFields();

            form?.addEventListener('submit', function (event) {
                syncTextFields();
                const submitButton = form.querySelector('button[type="submit"]');
                if (!submitButton || submitButton.disabled) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.dataset.originalHtml = submitButton.innerHTML;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>' + (submitButton.dataset.loadingText || 'Memproses...');

                if (!form.checkValidity()) {
                    event.preventDefault();
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton.dataset.originalHtml;
                }
            });
        })();
    </script>
@endpush
