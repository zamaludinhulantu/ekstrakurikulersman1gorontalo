import './bootstrap';
import 'bootstrap';

const bindParticipantProfilePreview = () => {
    const spotlight = document.getElementById('participantSpotlight');
    if (!spotlight) {
        return;
    }

    const fields = {
        avatar: document.getElementById('participantSpotlightAvatar'),
        title: document.getElementById('participantSpotlightTitle'),
        className: document.getElementById('participantSpotlightClass'),
        extracurricular: document.getElementById('participantSpotlightExtracurricular'),
        talent: document.getElementById('participantSpotlightTalent'),
        level: document.getElementById('participantSpotlightLevel'),
        role: document.getElementById('participantSpotlightRole'),
        group: document.getElementById('participantSpotlightGroup'),
        recommendedRole: document.getElementById('participantSpotlightRecommendedRole'),
        experience: document.getElementById('participantSpotlightExperience'),
        achievements: document.getElementById('participantSpotlightAchievements'),
        recommendation: document.getElementById('participantSpotlightRecommendation'),
    };

    const close = () => {
        spotlight.hidden = true;
        document.body.classList.remove('spotlight-open');
    };

    const open = (payload) => {
        fields.avatar.textContent = payload.initial || 'S';
        fields.title.textContent = payload.name || '-';
        fields.className.textContent = payload.class_name || '-';
        fields.extracurricular.textContent = payload.extracurricular || '-';
        fields.talent.textContent = payload.primary_talent || '-';
        fields.level.textContent = payload.skill_level || '-';
        fields.role.textContent = payload.preferred_position || '-';
        fields.group.textContent = payload.training_group || '-';
        fields.recommendedRole.textContent = payload.recommended_role || '-';
        fields.experience.textContent = payload.experience || '-';
        fields.achievements.textContent = payload.achievements || '-';
        fields.recommendation.textContent = payload.recommendation || '-';
        spotlight.hidden = false;
        document.body.classList.add('spotlight-open');
    };

    spotlight.querySelectorAll('[data-profile-close]').forEach((button) => {
        button.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !spotlight.hidden) {
            close();
        }
    });

    document.querySelectorAll('.profile-preview-trigger').forEach((trigger) => {
        trigger.addEventListener('click', async () => {
            const url = trigger.getAttribute('data-profile-url');
            if (!url) {
                return;
            }

            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            open(await response.json());
        });
    });
};

const bindTalentTestParticipantSelector = () => {
    const select = document.querySelector('[data-talent-test-selector]');
    const list = document.querySelector('[data-talent-test-participant-list]');
    const actions = document.querySelector('[data-talent-test-participant-actions]');
    const selectAllButton = document.querySelector('[data-talent-test-select-all]');
    const clearAllButton = document.querySelector('[data-talent-test-clear-all]');
    if (!select || !list) {
        return;
    }

    const selectedIds = new Set(JSON.parse(select.dataset.selected || '[]').map((value) => String(value)));

    const syncCheckboxState = () => {
        list.querySelectorAll('input[type="checkbox"][name="participant_registration_ids[]"]').forEach((checkbox) => {
            const id = String(checkbox.value);
            checkbox.checked = selectedIds.has(id);
        });
    };

    const refreshActionVisibility = (hasRegistrations) => {
        if (!actions) {
            return;
        }

        actions.hidden = !hasRegistrations;
    };

    const render = () => {
        const option = select.options[select.selectedIndex];
        const registrations = option?.dataset?.registrations ? JSON.parse(option.dataset.registrations) : [];

        list.innerHTML = '';
        refreshActionVisibility(registrations.length > 0);
        registrations.forEach((registration) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'col-md-6';
            wrapper.innerHTML = `
                <label class="participant-picker__option">
                    <input type="checkbox" name="participant_registration_ids[]" value="${registration.id}" ${selectedIds.has(String(registration.id)) ? 'checked' : ''}>
                    <span>
                        <strong>${registration.name}</strong>
                        <small>${registration.class_name} | ${registration.status}</small>
                    </span>
                </label>
            `;
            list.appendChild(wrapper);
        });

        syncCheckboxState();

        list.querySelectorAll('input[type="checkbox"][name="participant_registration_ids[]"]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const id = String(checkbox.value);
                if (checkbox.checked) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
            });
        });
    };

    selectAllButton?.addEventListener('click', () => {
        list.querySelectorAll('input[type="checkbox"][name="participant_registration_ids[]"]').forEach((checkbox) => {
            checkbox.checked = true;
            selectedIds.add(String(checkbox.value));
        });
    });

    clearAllButton?.addEventListener('click', () => {
        list.querySelectorAll('input[type="checkbox"][name="participant_registration_ids[]"]').forEach((checkbox) => {
            checkbox.checked = false;
            selectedIds.delete(String(checkbox.value));
        });
    });

    select.addEventListener('change', render);
    render();
};

const bindTalentReviewPanels = () => {
    const root = document.querySelector('[data-talent-review]');
    if (!root) {
        return;
    }

    const triggers = Array.from(root.querySelectorAll('[data-talent-review-trigger]'));
    const panels = Array.from(root.querySelectorAll('[data-talent-review-panel]'));
    if (!triggers.length || !panels.length) {
        return;
    }

    const showPanel = (targetId) => {
        triggers.forEach((trigger) => {
            const isActive = trigger.dataset.target === targetId;
            trigger.classList.toggle('is-active', isActive);
            trigger.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach((panel) => {
            panel.classList.toggle('d-none', panel.id !== targetId);
        });
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            showPanel(trigger.dataset.target);
        });
    });

    const firstActive = triggers.find((trigger) => trigger.classList.contains('is-active')) || triggers[0];
    if (firstActive?.dataset.target) {
        showPanel(firstActive.dataset.target);
    }
};

const bindRegistrationVerificationModal = () => {
    const modal = document.getElementById('registrationVerificationModal');
    const form = document.getElementById('registrationVerificationForm');
    if (!modal || !form) {
        return;
    }

    const fields = {
        title: document.getElementById('registrationVerificationModalLabel'),
        student: document.getElementById('registrationVerificationStudent'),
        meta: document.getElementById('registrationVerificationMeta'),
        extracurricular: document.getElementById('registrationVerificationExtracurricular'),
        skillLevel: document.getElementById('registrationVerificationSkillLevel'),
        primaryTalent: document.getElementById('registrationVerificationPrimaryTalent'),
        currentSkills: document.getElementById('registrationVerificationCurrentSkills'),
        experience: document.getElementById('registrationVerificationExperience'),
        achievements: document.getElementById('registrationVerificationAchievements'),
        notes: document.getElementById('registrationVerificationNotes'),
        status: document.getElementById('registrationVerificationStatus'),
    };

    const radios = Array.from(form.querySelectorAll('input[name="decision"]'));

    const syncDecision = (value) => {
        fields.status.value = value === 'reject' ? 'rejected' : 'approved';
    };

    radios.forEach((radio) => {
        radio.addEventListener('change', () => {
            if (radio.checked) {
                syncDecision(radio.value);
            }
        });
    });

    modal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!(trigger instanceof HTMLElement)) {
            return;
        }

        form.action = trigger.dataset.action || '#';
        fields.title.textContent = trigger.dataset.modalTitle || 'Verifikasi Pendaftar';
        fields.student.textContent = trigger.dataset.student || '-';
        fields.meta.textContent = [trigger.dataset.nis ? `NIS: ${trigger.dataset.nis}` : null, trigger.dataset.className || null]
            .filter(Boolean)
            .join(' | ') || '-';
        fields.extracurricular.textContent = trigger.dataset.extracurricular || '-';
        fields.skillLevel.textContent = trigger.dataset.skillLevel || '-';
        fields.primaryTalent.textContent = trigger.dataset.primaryTalent || '-';
        fields.currentSkills.textContent = trigger.dataset.currentSkills || '-';
        fields.experience.textContent = trigger.dataset.priorExperience || '-';
        fields.achievements.textContent = trigger.dataset.achievementHistory || '-';
        fields.notes.value = trigger.dataset.notes || '';

        const defaultDecision = trigger.dataset.defaultDecision || 'approve';
        radios.forEach((radio) => {
            radio.checked = radio.value === defaultDecision;
        });
        syncDecision(defaultDecision);
    });
};

const bindRegistrationNoteModal = () => {
    const modal = document.getElementById('registrationNoteModal');
    if (!modal) {
        return;
    }

    const meta = document.getElementById('registrationNoteModalMeta');
    const body = document.getElementById('registrationNoteModalBody');

    modal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!(trigger instanceof HTMLElement)) {
            return;
        }

        const student = trigger.dataset.student || '-';
        const extracurricular = trigger.dataset.extracurricular || '-';

        meta.textContent = `${student} | ${extracurricular}`;
        body.textContent = trigger.dataset.note || 'Belum ada catatan verifikasi.';
    });
};

const bindTooltips = () => {
    if (!window.bootstrap?.Tooltip) {
        return;
    }

    document.querySelectorAll('[data-bs-toggle="tooltip"], [data-ui-tooltip]').forEach((element) => {
        if (element.hasAttribute('data-ui-tooltip')) {
            element.setAttribute('title', element.getAttribute('data-ui-tooltip') || '');
        }
        window.bootstrap.Tooltip.getOrCreateInstance(element);
    });
};

const bindAssessmentFormFilters = () => {
    document.querySelectorAll('[data-assessment-extracurricular-select]').forEach((extracurricularSelect) => {
        const scope = extracurricularSelect.closest('form');
        if (!scope) {
            return;
        }

        const studentSelect = scope.querySelector('[data-assessment-student-select]');
        const coachSelect = scope.querySelector('[data-assessment-coach-select]');

        const filterStudentOptions = () => {
            if (!studentSelect) {
                return;
            }

            const extracurricularId = extracurricularSelect.value;
            Array.from(studentSelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const matches = !extracurricularId || option.dataset.extracurricularId === extracurricularId;
                option.hidden = !matches;
            });

            const selected = studentSelect.options[studentSelect.selectedIndex];
            if (selected && selected.hidden) {
                studentSelect.value = '';
            }
        };

        const filterCoachOptions = () => {
            if (!coachSelect) {
                return;
            }

            const extracurricularId = extracurricularSelect.value;
            Array.from(coachSelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const ids = String(option.dataset.extracurricularIds || '')
                    .split(',')
                    .map((value) => value.trim())
                    .filter(Boolean);

                const matches = !extracurricularId || ids.includes(extracurricularId);
                option.hidden = !matches;
            });

            const selected = coachSelect.options[coachSelect.selectedIndex];
            if (selected && selected.hidden) {
                coachSelect.value = '';
            }
        };

        extracurricularSelect.addEventListener('change', () => {
            filterStudentOptions();
            filterCoachOptions();
        });

        filterStudentOptions();
        filterCoachOptions();
    });
};

const bindAssessmentDetailModal = () => {
    const modal = document.getElementById('assessmentDetailModal');
    if (!modal) {
        return;
    }

    const fields = {
        meta: document.getElementById('assessmentDetailMeta'),
        type: document.getElementById('assessmentDetailType'),
        student: document.getElementById('assessmentDetailStudent'),
        score: document.getElementById('assessmentDetailScore'),
        date: document.getElementById('assessmentDetailDate'),
        coach: document.getElementById('assessmentDetailCoach'),
        description: document.getElementById('assessmentDetailDescription'),
    };

    modal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!(trigger instanceof HTMLElement)) {
            return;
        }

        fields.meta.textContent = `${trigger.dataset.title || '-'} | ${trigger.dataset.extracurricular || '-'}`;
        fields.type.textContent = trigger.dataset.type || '-';
        fields.student.textContent = trigger.dataset.student || '-';
        fields.score.textContent = trigger.dataset.score || '-';
        fields.date.textContent = trigger.dataset.date || '-';
        fields.coach.textContent = trigger.dataset.coach || '-';
        fields.description.textContent = trigger.dataset.description || 'Belum ada catatan tambahan.';
    });
};

const bindTabScrollNav = () => {
    document.querySelectorAll('.tab-scroll-nav').forEach((nav) => {
        const items = Array.from(nav.querySelectorAll('[data-bs-toggle="tab"]'));
        if (!items.length) {
            return;
        }

        const sync = (activeItem) => {
            items.forEach((item) => {
                const isActive = item === activeItem;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        };

        items.forEach((item) => {
            item.addEventListener('shown.bs.tab', () => {
                sync(item);
            });
        });

        const initialActive = items.find((item) => item.classList.contains('active') || item.classList.contains('is-active')) || items[0];
        sync(initialActive);
    });
};

const bindAssessmentTitleOptions = () => {
    document.querySelectorAll('[data-assessment-title-option]').forEach((select) => {
        const scope = select.closest('form, .tab-pane, .form-section-card, .card-body') || document;
        const wrapper = scope.querySelector('[data-custom-title-wrapper]');
        const input = wrapper?.querySelector('input[name="custom_title"]');
        if (!wrapper || !input) {
            return;
        }

        const sync = () => {
            const isCustom = select.value === 'Penilaian lain';
            wrapper.classList.toggle('d-none', !isCustom);
            input.required = isCustom;
            if (!isCustom) {
                input.value = '';
            }
        };

        select.addEventListener('change', sync);
        sync();
    });
};

const bindIndividualAssessmentSummary = () => {
    const select = document.getElementById('individual_student_id');
    const summary = document.querySelector('[data-individual-student-summary]');
    if (!select || !summary) {
        return;
    }

    const nameField = summary.querySelector('[data-individual-student-name]');
    const metaField = summary.querySelector('[data-individual-student-meta]');

    const sync = () => {
        const option = select.options[select.selectedIndex];
        if (!option || !option.value) {
            nameField.textContent = 'Pilih siswa untuk melihat ringkasan singkat.';
            metaField.textContent = 'NIS dan kelas akan muncul di sini.';
            return;
        }

        const text = option.textContent || '';
        const match = text.match(/^(.*?)\s+\((.*?)\)$/);
        nameField.textContent = match?.[1]?.trim() || text.trim();
        metaField.textContent = `NIS: ${match?.[2]?.trim() || '-'} | Cek daftar anggota pada ekstrakurikuler terpilih.`;
    };

    select.addEventListener('change', sync);
    sync();
};

const bindMassAssessmentForm = () => {
    const root = document.querySelector('[data-mass-assessment-root]');
    if (!root) {
        return;
    }

    const payload = JSON.parse(root.getAttribute('data-mass-assessment') || '{}');
    const students = Array.isArray(payload.students) ? payload.students : [];
    const lookup = payload.lookup || {};
    const oldRows = payload.oldRows || {};

    const extracurricularSelect = root.querySelector('[data-mass-extracurricular-select]');
    const titleSelect = root.querySelector('[data-assessment-title-option]');
    const customTitleInput = root.querySelector('input[name="custom_title"]');
    const dateInput = root.querySelector('[data-mass-assessment-date]');
    const searchInput = root.querySelector('[data-mass-student-search]');
    const classFilter = root.querySelector('[data-mass-class-filter]');
    const tableBody = root.querySelector('[data-mass-table-body]');
    const cardBody = root.querySelector('[data-mass-card-body]');
    const bulkScoreButton = root.querySelector('[data-bulk-score]');
    const bulkNoteButton = root.querySelector('[data-bulk-note]');
    const bulkClearButton = root.querySelector('[data-bulk-clear]');

    if (!extracurricularSelect || !titleSelect || !dateInput || !searchInput || !classFilter || !tableBody || !cardBody) {
        return;
    }

    const resolveTitle = () => {
        if (titleSelect.value === 'Penilaian lain') {
            return customTitleInput?.value?.trim() || '';
        }

        return titleSelect.value || '';
    };

    const lookupStatus = (studentId) => {
        const title = resolveTitle();
        const date = dateInput.value;
        const extracurricularId = extracurricularSelect.value;
        if (!title || !date || !extracurricularId) {
            return null;
        }

        const key = `${extracurricularId}|${studentId}|${title}|${date}`;
        return lookup[key] || null;
    };

    const resolveState = (studentId) => {
        const row = oldRows[String(studentId)] || {};
        return {
            score: row.score ?? '',
            description: row.description ?? '',
        };
    };

    const createStatusBadge = (studentId, rowState) => {
        const existingStatus = lookupStatus(studentId);
        if (existingStatus === 'published') {
            return '<span class="badge badge-status-success">Sudah dinilai</span>';
        }
        if (existingStatus === 'draft') {
            return '<span class="badge badge-status-warning">Draft</span>';
        }
        if ((rowState.score ?? '') !== '' || (rowState.description ?? '').trim() !== '') {
            return '<span class="badge badge-status-secondary">Belum dinilai</span>';
        }

        return '<span class="badge badge-status-secondary">Belum dinilai</span>';
    };

    const getVisibleStudents = () => {
        const extracurricularId = extracurricularSelect.value;
        const search = searchInput.value.trim().toLowerCase();
        const className = classFilter.value;

        return students.filter((student) => {
            if (extracurricularId && String(student.extracurricular_id) !== extracurricularId) {
                return false;
            }

            if (className && student.class_name !== className) {
                return false;
            }

            if (!search) {
                return true;
            }

            return [student.name, student.nis, student.class_name]
                .join(' ')
                .toLowerCase()
                .includes(search);
        });
    };

    const rebuildClassFilter = () => {
        const extracurricularId = extracurricularSelect.value;
        const availableClasses = Array.from(new Set(
            students
                .filter((student) => !extracurricularId || String(student.extracurricular_id) === extracurricularId)
                .map((student) => student.class_name || '-')
        )).sort();

        const current = classFilter.value;
        classFilter.innerHTML = '<option value="">Semua kelas</option>';
        availableClasses.forEach((className) => {
            const option = document.createElement('option');
            option.value = className;
            option.textContent = className;
            classFilter.appendChild(option);
        });
        classFilter.value = availableClasses.includes(current) ? current : '';
    };

    const renderEmpty = (message) => {
        const html = `
            <tr>
                <td colspan="4">
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-people"></i></div>
                        <p class="mb-0">${message}</p>
                    </div>
                </td>
            </tr>
        `;
        tableBody.innerHTML = html;
        cardBody.innerHTML = `
            <div class="empty-state">
                <div class="icon"><i class="bi bi-people"></i></div>
                <p class="mb-0">${message}</p>
            </div>
        `;
    };

    const render = () => {
        rebuildClassFilter();

        const extracurricularId = extracurricularSelect.value;
        if (!extracurricularId) {
            renderEmpty('Pilih ekstrakurikuler untuk menampilkan anggota aktif.');
            return;
        }

        const visibleStudents = getVisibleStudents();
        if (!visibleStudents.length) {
            renderEmpty('Tidak ada siswa yang sesuai dengan filter saat ini.');
            return;
        }

        tableBody.innerHTML = visibleStudents.map((student) => {
            const rowState = resolveState(student.student_id);
            return `
                <tr>
                    <td>
                        <div class="assessment-student-cell">
                            <span class="assessment-student-avatar">${student.initial}</span>
                            <div>
                                <strong>${student.name}</strong>
                                <small>${student.nis} | ${student.class_name}</small>
                            </div>
                        </div>
                        <input type="hidden" name="rows[${student.student_id}][student_id]" value="${student.student_id}">
                    </td>
                    <td class="assessment-score-cell">
                        <input type="number" step="0.01" min="0" max="100" name="rows[${student.student_id}][score]" value="${rowState.score ?? ''}" class="form-control form-control-sm assessment-score-input" placeholder="0 - 100">
                    </td>
                    <td>
                        <textarea name="rows[${student.student_id}][description]" class="form-control form-control-sm assessment-note-input" rows="2" placeholder="Catatan singkat">${rowState.description ?? ''}</textarea>
                    </td>
                    <td>${createStatusBadge(student.student_id, rowState)}</td>
                </tr>
            `;
        }).join('');

        cardBody.innerHTML = visibleStudents.map((student) => {
            const rowState = resolveState(student.student_id);
            return `
                <div class="mobile-data-card">
                    <input type="hidden" name="rows[${student.student_id}][student_id]" value="${student.student_id}">
                    <div class="mobile-data-card-header">
                        <div>
                            <h3 class="mobile-data-card-title">${student.name}</h3>
                            <div class="small text-muted">${student.nis} | ${student.class_name}</div>
                        </div>
                        ${createStatusBadge(student.student_id, rowState)}
                    </div>
                    <div class="mobile-data-list">
                        <div>
                            <span class="mobile-data-item-label">Nilai</span>
                            <input type="number" step="0.01" min="0" max="100" name="rows[${student.student_id}][score]" value="${rowState.score ?? ''}" class="form-control form-control-sm assessment-score-input" placeholder="0 - 100">
                        </div>
                        <div>
                            <span class="mobile-data-item-label">Catatan</span>
                            <textarea name="rows[${student.student_id}][description]" class="form-control form-control-sm assessment-note-input" rows="2" placeholder="Catatan singkat">${rowState.description ?? ''}</textarea>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        syncResponsiveInputs();
    };

    const syncResponsiveInputs = () => {
        const isMobile = window.matchMedia('(max-width: 767.98px)').matches;

        tableBody.querySelectorAll('input, textarea, select').forEach((field) => {
            field.disabled = isMobile;
        });

        cardBody.querySelectorAll('input, textarea, select').forEach((field) => {
            field.disabled = !isMobile;
        });
    };

    const withConfirmation = (message, callback) => {
        if (!window.confirm(message)) {
            return;
        }

        callback();
    };

    extracurricularSelect.addEventListener('change', render);
    titleSelect.addEventListener('change', render);
    customTitleInput?.addEventListener('input', render);
    dateInput.addEventListener('change', render);
    searchInput.addEventListener('input', render);
    classFilter.addEventListener('change', render);

    bulkScoreButton?.addEventListener('click', () => {
        const value = window.prompt('Masukkan nilai yang akan diterapkan ke semua siswa yang sedang tampil.');
        if (value === null || value.trim() === '') {
            return;
        }

        withConfirmation('Terapkan nilai yang sama ke semua siswa yang sedang tampil?', () => {
            root.querySelectorAll('.assessment-score-input').forEach((input) => {
                input.value = value;
            });
        });
    });

    bulkNoteButton?.addEventListener('click', () => {
        const value = window.prompt('Masukkan catatan yang akan diterapkan ke semua siswa yang sedang tampil.');
        if (value === null || value.trim() === '') {
            return;
        }

        withConfirmation('Terapkan catatan yang sama ke semua siswa yang sedang tampil?', () => {
            root.querySelectorAll('.assessment-note-input').forEach((input) => {
                input.value = value;
            });
        });
    });

    bulkClearButton?.addEventListener('click', () => {
        withConfirmation('Kosongkan semua nilai yang sedang tampil?', () => {
            root.querySelectorAll('.assessment-score-input').forEach((input) => {
                input.value = '';
            });
        });
    });

    window.addEventListener('resize', syncResponsiveInputs);
    render();
};

const bindPublicNavbar = () => {
    const navbar = document.querySelector('[data-public-navbar]');
    if (!navbar) {
        return;
    }

    const sync = () => {
        navbar.classList.toggle('is-scrolled', window.scrollY > 18);
    };

    sync();
    window.addEventListener('scroll', sync, { passive: true });
};

const bindPublicMobileMenu = () => {
    const menu = document.getElementById('publicMobileMenu');
    if (!menu || typeof window.bootstrap === 'undefined') {
        return;
    }

    const trigger = document.querySelector('.public-menu-button');
    const firstFocusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    menu.addEventListener('shown.bs.offcanvas', () => {
        const firstFocusable = menu.querySelector('.offcanvas-body ' + firstFocusableSelector)
            || menu.querySelector('.offcanvas-header ' + firstFocusableSelector);
        firstFocusable?.focus();
    });

    menu.addEventListener('hidden.bs.offcanvas', () => {
        trigger?.focus();
    });
};

const bindRevealAnimations = () => {
    const items = document.querySelectorAll('[data-reveal]');
    if (!items.length) {
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reducedMotion || !('IntersectionObserver' in window)) {
        items.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.01,
        rootMargin: '0px 0px 160px 0px',
    });

    items.forEach((item) => observer.observe(item));
};

const bindCounters = () => {
    const counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) {
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reducedMotion) {
        return;
    }

    const animate = (element) => {
        const target = Number(element.getAttribute('data-counter'));
        if (!Number.isFinite(target)) {
            return;
        }

        const duration = 800;
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const value = Math.round(target * progress);
            element.textContent = String(value);

            if (progress < 1) {
                window.requestAnimationFrame(tick);
            } else {
                element.textContent = String(target);
            }
        };

        window.requestAnimationFrame(tick);
    };

    if (!('IntersectionObserver' in window)) {
        counters.forEach(animate);
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            animate(entry.target);
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.5,
    });

    counters.forEach((counter) => observer.observe(counter));
};

document.addEventListener('DOMContentLoaded', () => {
    bindParticipantProfilePreview();
    bindTalentTestParticipantSelector();
    bindTalentReviewPanels();
    bindRegistrationVerificationModal();
    bindRegistrationNoteModal();
    bindAssessmentFormFilters();
    bindAssessmentDetailModal();
    bindAssessmentTitleOptions();
    bindIndividualAssessmentSummary();
    bindMassAssessmentForm();
    bindTabScrollNav();
    bindTooltips();
    bindPublicNavbar();
    bindPublicMobileMenu();
    bindRevealAnimations();
    bindCounters();

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const submitter = event.submitter;
            if (submitter?.name) {
                const existingMirror = form.querySelector(`input[type="hidden"][data-submit-mirror="${submitter.name}"]`);
                if (!existingMirror) {
                    const mirror = document.createElement('input');
                    mirror.type = 'hidden';
                    mirror.name = submitter.name;
                    mirror.value = submitter.value;
                    mirror.dataset.submitMirror = submitter.name;
                    form.appendChild(mirror);
                }
            }

            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach((button) => {
                if (button.dataset.submitting === '1') {
                    return;
                }

                button.dataset.submitting = '1';
                button.dataset.originalHtml = button.innerHTML;
                const loadingText = button.getAttribute('data-loading-text') || 'Memproses...';
                button.disabled = true;
                button.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>${loadingText}</span>`;
            });
        });
    });
});
