const urlBase64ToUint8Array = (base64String) => {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const normalized = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(normalized);

    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
};

const bindPwaShell = () => {
    const root = document.body;
    if (!root || root.dataset.pwaEnabled !== 'true') {
        return;
    }

    const serviceWorkerUrl = root.dataset.serviceWorkerUrl;
    if (!serviceWorkerUrl || !('serviceWorker' in navigator)) {
        return;
    }

    const installPromptCard = document.getElementById('installPromptCard');
    const iosInstallCard = document.getElementById('iosInstallCard');
    const appUpdateCard = document.getElementById('appUpdateCard');
    const installAppButton = document.getElementById('installAppButton');
    const dismissInstallButton = document.getElementById('dismissInstallButton');
    const dismissIosInstallButton = document.getElementById('dismissIosInstallButton');
    const refreshAppButton = document.getElementById('refreshAppButton');
    const dismissAppUpdateButton = document.getElementById('dismissAppUpdateButton');
    const networkStatusBanner = document.getElementById('networkStatusBanner');
    const networkStatusText = document.getElementById('networkStatusText');
    const networkRetryButton = document.getElementById('networkRetryButton');
    const installTriggers = document.querySelectorAll('.install-app-trigger');
    const isStandaloneMode = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    const installDismissed = window.localStorage.getItem('pwa-install-dismissed') === '1';
    let lastKnownOnlineState = navigator.onLine;
    let deferredPrompt = null;
    let registrationRef = null;

    const hideInstallPrompts = () => {
        installPromptCard?.setAttribute('hidden', 'hidden');
        iosInstallCard?.setAttribute('hidden', 'hidden');
    };

    const syncInstallTriggerVisibility = () => {
        installTriggers.forEach((trigger) => {
            trigger.toggleAttribute('hidden', isStandaloneMode());
        });
    };

    const showInstallPrompt = () => {
        if (isStandaloneMode() || installDismissed) {
            return;
        }

        if (deferredPrompt && installPromptCard) {
            installPromptCard.removeAttribute('hidden');
            return;
        }

        if (isIos && iosInstallCard) {
            iosInstallCard.removeAttribute('hidden');
        }
    };

    const syncConnectionState = () => {
        if (!networkStatusBanner || !networkStatusText) {
            return;
        }

        if (!navigator.onLine) {
            networkStatusText.textContent = 'Anda sedang offline.';
            networkStatusBanner.removeAttribute('hidden');
            networkStatusBanner.classList.add('is-offline');
            lastKnownOnlineState = false;
            return;
        }

        if (lastKnownOnlineState) {
            networkStatusBanner.setAttribute('hidden', 'hidden');
            return;
        }

        networkStatusText.textContent = 'Koneksi kembali tersedia.';
        networkStatusBanner.removeAttribute('hidden');
        networkStatusBanner.classList.remove('is-offline');
        lastKnownOnlineState = true;
        window.setTimeout(() => networkStatusBanner.setAttribute('hidden', 'hidden'), 2400);
    };

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        showInstallPrompt();
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        hideInstallPrompts();
        window.localStorage.removeItem('pwa-install-dismissed');
        syncInstallTriggerVisibility();
    });

    installTriggers.forEach((trigger) => {
        trigger.addEventListener('click', async () => {
            if (isStandaloneMode()) {
                return;
            }

            if (deferredPrompt) {
                deferredPrompt.prompt();
                const choice = await deferredPrompt.userChoice;
                if (choice.outcome === 'accepted') {
                    hideInstallPrompts();
                }
                deferredPrompt = null;
                return;
            }

            showInstallPrompt();
        });
    });

    installAppButton?.addEventListener('click', async () => {
        if (!deferredPrompt) {
            showInstallPrompt();
            return;
        }

        deferredPrompt.prompt();
        const choice = await deferredPrompt.userChoice;
        if (choice.outcome !== 'accepted') {
            return;
        }

        deferredPrompt = null;
        hideInstallPrompts();
    });

    dismissInstallButton?.addEventListener('click', () => {
        window.localStorage.setItem('pwa-install-dismissed', '1');
        installPromptCard?.setAttribute('hidden', 'hidden');
    });

    dismissIosInstallButton?.addEventListener('click', () => {
        iosInstallCard?.setAttribute('hidden', 'hidden');
    });

    refreshAppButton?.addEventListener('click', () => {
        if (registrationRef?.waiting) {
            registrationRef.waiting.postMessage({ type: 'SKIP_WAITING' });
        }
    });

    dismissAppUpdateButton?.addEventListener('click', () => {
        appUpdateCard?.setAttribute('hidden', 'hidden');
    });

    networkRetryButton?.addEventListener('click', () => {
        window.location.reload();
    });

    window.addEventListener('online', syncConnectionState);
    window.addEventListener('offline', syncConnectionState);
    syncConnectionState();

    navigator.serviceWorker.register(serviceWorkerUrl).then((registration) => {
        registrationRef = registration;
        if (registration.waiting && appUpdateCard) {
            appUpdateCard.removeAttribute('hidden');
        }

        registration.addEventListener('updatefound', () => {
            const worker = registration.installing;
            if (!worker) {
                return;
            }

            worker.addEventListener('statechange', () => {
                if (worker.state === 'installed' && navigator.serviceWorker.controller && appUpdateCard) {
                    appUpdateCard.removeAttribute('hidden');
                }
            });
        });
    }).catch(() => {
        // Ignore registration failures and keep the app usable.
    });

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        window.location.reload();
    });

    syncInstallTriggerVisibility();

    if (!isStandaloneMode()) {
        window.setTimeout(showInstallPrompt, 1200);
    }
};

const bindLogoutCacheCleanup = () => {
    document.querySelectorAll(`form[action$="/logout"]`).forEach((form) => {
        form.addEventListener('submit', () => {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.ready
                    .then((registration) => {
                        registration.active?.postMessage({ type: 'CLEAR_PRIVATE_CACHE' });
                    })
                    .catch(() => {
                        // Ignore cache cleanup failures during logout.
                    });
            }
        });
    });
};

const bindPushSettings = () => {
    const root = document.querySelector('[data-pwa-settings-root]');
    if (!root) {
        return;
    }

    const vapidPublicKey = root.dataset.vapidPublicKey;
    const statusUrl = document.body.dataset.pushStatusUrl;
    const subscribeUrl = document.body.dataset.pushSubscribeUrl;
    const unsubscribeUrl = document.body.dataset.pushUnsubscribeUrl;
    const unsubscribeAllUrl = document.body.dataset.pushUnsubscribeAllUrl;
    const installState = document.getElementById('pwaInstallState');
    const connectionState = document.getElementById('pwaConnectionState');
    const pushState = document.getElementById('pwaPushState');
    const pushHelpText = document.getElementById('pwaPushHelpText');
    const toggleButton = document.getElementById('settingsTogglePushButton');
    const toggleStatus = document.getElementById('settingsTogglePushStatus');
    const toggleText = document.getElementById('settingsTogglePushText');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    if (installState) {
        installState.textContent = isStandalone ? 'Aplikasi terpasang' : 'Belum dipasang';
    }

    if (connectionState) {
        connectionState.textContent = navigator.onLine ? 'Online' : 'Offline';
    }

    const setPushState = (value, helpText = '') => {
        if (pushState) {
            pushState.textContent = value;
        }
        if (pushHelpText && helpText) {
            pushHelpText.textContent = helpText;
        }
    };

    const setToggleButtonState = (mode, disabled = false) => {
        if (!toggleButton) {
            return;
        }

        toggleButton.dataset.state = mode;
        toggleButton.disabled = disabled;
        toggleButton.classList.remove('is-enabled');
        toggleButton.setAttribute('aria-pressed', 'false');

        if (mode === 'enabled') {
            toggleButton.classList.add('is-enabled');
            toggleButton.setAttribute('aria-pressed', 'true');
            toggleButton.setAttribute('aria-label', 'Nonaktifkan notifikasi');
            toggleButton.setAttribute('title', 'Nonaktifkan notifikasi');
            if (toggleText) {
                toggleText.textContent = 'Nonaktifkan notifikasi';
            }
            if (toggleStatus) {
                toggleStatus.textContent = 'Notifikasi aktif di perangkat ini.';
                toggleStatus.classList.add('is-active');
            }
            return;
        }

        if (mode === 'unsupported') {
            toggleButton.setAttribute('aria-label', 'Notifikasi tidak tersedia');
            toggleButton.setAttribute('title', 'Notifikasi tidak tersedia');
            toggleButton.disabled = true;
            if (toggleText) {
                toggleText.textContent = 'Notifikasi tidak tersedia';
            }
            if (toggleStatus) {
                toggleStatus.textContent = 'Notifikasi tidak tersedia di browser ini.';
                toggleStatus.classList.remove('is-active');
            }
            return;
        }

        toggleButton.setAttribute('aria-label', 'Aktifkan notifikasi');
        toggleButton.setAttribute('title', 'Aktifkan notifikasi');
        if (toggleText) {
            toggleText.textContent = 'Aktifkan notifikasi';
        }
        if (toggleStatus) {
            toggleStatus.textContent = 'Notifikasi belum aktif.';
            toggleStatus.classList.remove('is-active');
        }
    };

    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        setPushState('Browser tidak mendukung notifikasi.', 'Gunakan browser yang mendukung Push API dan Service Worker.');
        setToggleButtonState('unsupported');
        return;
    }

    if (!vapidPublicKey || !statusUrl || !subscribeUrl || !unsubscribeUrl || !unsubscribeAllUrl || !csrfToken) {
        setPushState('Konfigurasi push belum lengkap.', 'Isi VAPID key di server sebelum mengaktifkan notifikasi.');
        setToggleButtonState('unsupported');
        return;
    }

    const readServerSubscriptionState = async (subscription) => {
        const response = await fetch(statusUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ endpoint: subscription.endpoint }),
        });

        if (!response.ok) {
            throw new Error('server-status-check-failed');
        }

        return response.json();
    };

    const submitSubscription = async (subscription, { takeover = false } = {}) => {
        const response = await fetch(subscribeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                keys: {
                    p256dh: window.btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('p256dh')))),
                    auth: window.btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('auth')))),
                },
                contentEncoding: subscription.options?.supportedContentEncodings?.[0] || 'aes128gcm',
                deviceName: navigator.platform || 'Perangkat browser',
                takeover,
            }),
        });

        if (response.status === 409) {
            return {
                ok: false,
                status: 409,
                data: await response.json(),
            };
        }

        if (!response.ok) {
            throw new Error('subscription-save-failed');
        }

        return {
            ok: true,
            status: response.status,
            data: await response.json(),
        };
    };

    const syncSubscriptionState = async () => {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (Notification.permission === 'denied') {
            if (subscription) {
                await fetch(unsubscribeUrl, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ endpoint: subscription.endpoint }),
                }).catch(() => {
                    // Ignore cleanup failures and keep reflecting the permission state.
                });

                await subscription.unsubscribe().catch(() => {
                    // Ignore browser cleanup failures when permission is already denied.
                });
            }

            setPushState('Izin notifikasi ditolak.', 'Aktifkan kembali izin notifikasi dari pengaturan browser untuk perangkat ini.');
            setToggleButtonState('disabled');
            return null;
        }

        if (!subscription) {
            setPushState('Notifikasi belum diaktifkan.', isStandalone || !/iphone|ipad/i.test(navigator.userAgent)
                ? 'Tekan tombol aktivasi untuk menghubungkan perangkat ini ke push notification.'
                : 'Di iPhone, dukungan paling baik tersedia setelah aplikasi dipasang ke layar utama.');
            setToggleButtonState('disabled');
            return null;
        }

        const serverState = await readServerSubscriptionState(subscription);
        if (serverState.status === 'linked') {
            setPushState('Notifikasi aktif pada perangkat ini.', 'Perangkat ini sudah menerima push notification untuk akun yang sedang login.');
            setToggleButtonState('enabled');

            return subscription;
        }

        if (serverState.status === 'linked_to_other_account') {
            setPushState('Perangkat ini terhubung ke akun lain.', 'Tekan tombol aktivasi jika Anda ingin memindahkan notifikasi browser ini ke akun yang sedang login.');
            setToggleButtonState('disabled');

            return subscription;
        }

        setPushState('Notifikasi browser belum terhubung ke akun ini.', 'Aktifkan notifikasi untuk menghubungkan perangkat ini ke akun yang sedang login.');
        setToggleButtonState('disabled');

        return subscription;
    };

    toggleButton?.addEventListener('click', async () => {
        const currentState = toggleButton.dataset.state || 'disabled';
        setToggleButtonState(currentState, true);

        try {
            if (currentState === 'enabled') {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();

                if (subscription) {
                    await fetch(unsubscribeUrl, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ endpoint: subscription.endpoint }),
                    });

                    await subscription.unsubscribe();
                }

                await syncSubscriptionState();
                return;
            }

            if (Notification.permission === 'default') {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    setPushState('Izin notifikasi ditolak.', 'Browser tidak memberi izin notifikasi untuk perangkat ini.');
                    setToggleButtonState('disabled');
                    return;
                }
            }

            const registration = await navigator.serviceWorker.ready;
            let subscription = await registration.pushManager.getSubscription();

            if (!subscription) {
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
                });
            }

            let saveResult = await submitSubscription(subscription);
            if (saveResult.status === 409 && saveResult.data?.requires_confirmation) {
                const approved = window.confirm('Perangkat ini masih terhubung ke akun lain. Pindahkan notifikasi di perangkat ini ke akun yang sedang login?');
                if (!approved) {
                    setPushState('Perangkat ini masih terhubung ke akun lain.', 'Aktivasi dibatalkan. Notifikasi akun sebelumnya tidak akan dipindahkan tanpa persetujuan Anda.');
                    return;
                }

                saveResult = await submitSubscription(subscription, { takeover: true });
            }

            if (!saveResult.ok) {
                throw new Error('subscription-save-rejected');
            }

            await syncSubscriptionState();
        } catch (error) {
            setPushState('Perubahan notifikasi gagal.', 'Periksa dukungan browser, koneksi internet, dan konfigurasi VAPID.');
            await syncSubscriptionState().catch(() => {
                setToggleButtonState('disabled');
            });
        } finally {
            if (toggleButton.dataset.state === 'unknown') {
                setToggleButtonState('disabled');
            } else {
                toggleButton.disabled = false;
            }
        }
    });

    window.addEventListener('online', () => {
        if (connectionState) {
            connectionState.textContent = 'Online';
        }
    });
    window.addEventListener('offline', () => {
        if (connectionState) {
            connectionState.textContent = 'Offline';
        }
    });

    syncSubscriptionState().catch(() => {
        setPushState('Status push belum dapat dibaca.', 'Refresh halaman lalu coba lagi.');
    });
};

export const initPwaFeatures = () => {
    bindPwaShell();
    bindLogoutCacheCleanup();
    bindPushSettings();
};
