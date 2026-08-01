import { Collapse, Dropdown, Offcanvas } from 'bootstrap';

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
    if (!menu) {
        return;
    }

    const trigger = document.querySelector('.public-menu-button');
    const offcanvas = Offcanvas.getOrCreateInstance(menu);
    const firstFocusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    menu.querySelectorAll('[data-bs-toggle="collapse"]').forEach((toggle) => {
        Collapse.getOrCreateInstance(toggle);
    });

    menu.addEventListener('shown.bs.offcanvas', () => {
        trigger?.setAttribute('aria-expanded', 'true');
        const firstFocusable = menu.querySelector('.offcanvas-body ' + firstFocusableSelector)
            || menu.querySelector('.offcanvas-header ' + firstFocusableSelector);
        firstFocusable?.focus();
    });

    menu.addEventListener('hidden.bs.offcanvas', () => {
        trigger?.setAttribute('aria-expanded', 'false');
        trigger?.focus();
    });

    menu.querySelectorAll('[data-nav-close]').forEach((link) => {
        link.addEventListener('click', () => {
            offcanvas.hide();
        });
    });
};

const bindImageFallbacks = () => {
    document.querySelectorAll('[data-card-media]').forEach((wrapper) => {
        const image = wrapper.querySelector('[data-fallback-image]');
        if (!(image instanceof HTMLImageElement)) {
            wrapper.classList.add('has-image-fallback');
            return;
        }

        const markAsFallback = () => {
            wrapper.classList.add('has-image-fallback');
        };

        image.addEventListener('error', markAsFallback, { once: true });

        if (image.complete && image.naturalWidth === 0) {
            markAsFallback();
        }
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

const bootPublicPwa = () => {
    if (document.body?.dataset.pwaEnabled !== 'true') {
        return;
    }

    import('./pwa')
        .then(({ initPwaFeatures }) => {
            initPwaFeatures();
        })
        .catch(() => {
            // Ignore deferred PWA bootstrap failures and keep the page usable.
        });
};

document.addEventListener('DOMContentLoaded', () => {
    bindPublicNavbar();
    bindPublicMobileMenu();
    bindImageFallbacks();
    bindRevealAnimations();
    bindCounters();
    bootPublicPwa();
});
