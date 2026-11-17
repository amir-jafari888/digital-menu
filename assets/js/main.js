/* Monochi — landing + shared page interactions.
   No backend requests are made from this file. */
(() => {
    'use strict';

    const root = document.documentElement;
    root.classList.add('js-ready');

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---- Top app bar elevation ------------------------------------------ */
    const header = document.querySelector('[data-header]');
    if (header) {
        const onScroll = () => {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ---- Mobile navigation sheet ---------------------------------------- */
    const navToggle = document.querySelector('[data-nav-toggle]');
    const navSheet = document.querySelector('[data-nav-sheet]');
    const navScrim = document.querySelector('.sheet-scrim');
    const navClosers = document.querySelectorAll('[data-nav-close]');
    let lastFocused = null;

    const focusableIn = (container) =>
        Array.from(
            container.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            )
        ).filter((el) => el.offsetWidth > 0 || el.offsetHeight > 0);

    const openNav = () => {
        if (!navSheet || !navToggle) return;
        const active = document.activeElement;
        lastFocused = active && active !== document.body ? active : navToggle;
        if (navScrim) navScrim.hidden = false;
        window.requestAnimationFrame(() => {
            if (navScrim) navScrim.classList.add('is-visible');
        });
        navSheet.classList.add('is-open');
        navSheet.setAttribute('aria-hidden', 'false');
        navToggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('nav-locked');
        const first = focusableIn(navSheet)[0];
        if (first) window.setTimeout(() => first.focus(), 60);
    };

    const closeNav = (restoreFocus = true) => {
        if (!navSheet || !navToggle) return;
        navSheet.classList.remove('is-open');
        navSheet.setAttribute('aria-hidden', 'true');
        navToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('nav-locked');
        if (navScrim) {
            navScrim.classList.remove('is-visible');
            window.setTimeout(() => {
                if (!navSheet.classList.contains('is-open')) navScrim.hidden = true;
            }, 320);
        }
        if (restoreFocus) {
            const target = lastFocused && document.contains(lastFocused) ? lastFocused : navToggle;
            target.focus();
        }
    };

    if (navToggle && navSheet) {
        navToggle.addEventListener('click', () => {
            if (navSheet.classList.contains('is-open')) closeNav();
            else openNav();
        });

        navClosers.forEach((el) => el.addEventListener('click', () => closeNav()));

        navSheet.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => closeNav(false));
        });

        document.addEventListener('keydown', (event) => {
            if (!navSheet.classList.contains('is-open')) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                closeNav();
                return;
            }
            if (event.key === 'Tab') {
                const items = focusableIn(navSheet);
                if (!items.length) return;
                const first = items[0];
                const last = items[items.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 980 && navSheet.classList.contains('is-open')) {
                closeNav(false);
            }
        });
    }

    /* ---- Pricing billing toggle ----------------------------------------- */
    const billingButtons = Array.from(document.querySelectorAll('[data-billing]'));
    const priceNodes = Array.from(document.querySelectorAll('[data-monthly]'));
    if (billingButtons.length) {
        const setBilling = (mode) => {
            billingButtons.forEach((btn) => {
                const active = btn.getAttribute('data-billing') === mode;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-pressed', String(active));
            });
            priceNodes.forEach((node) => {
                const value = node.getAttribute('data-' + mode);
                if (value) node.textContent = value;
            });
        };
        billingButtons.forEach((btn) => {
            btn.addEventListener('click', () => setBilling(btn.getAttribute('data-billing')));
        });
        setBilling('monthly');
    }

    /* ---- Cart demo (visual only) ---------------------------------------- */
    const addButtons = Array.from(document.querySelectorAll('.add-item'));
    const countNode = document.querySelector('.cart-count');
    const totalNode = document.querySelector('.cart-total');
    if (addButtons.length && countNode && totalNode) {
        let count = 0;
        let total = 0;
        const fa = new Intl.NumberFormat('fa-IR');
        addButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const price = Number(button.getAttribute('data-price') || 0);
                count += 1;
                total += price;
                countNode.textContent = fa.format(count);
                totalNode.textContent = fa.format(total) + ' تومان';
                button.textContent = '✓';
                window.setTimeout(() => {
                    button.textContent = '+';
                }, 700);
            });
        });
    }

    /* ---- Back to top ----------------------------------------------------- */
    const toTop = document.querySelector('[data-to-top]');
    if (toTop) {
        const footer = document.querySelector('.site-footer');
        const toggleToTop = () => {
            // Hide at the top of the page and once the footer is in view, so the
            // button never sits on top of footer text.
            const footerVisible = footer
                ? footer.getBoundingClientRect().top < window.innerHeight - 80
                : false;
            toTop.hidden = window.scrollY < 640 || footerVisible;
        };
        toggleToTop();
        window.addEventListener('scroll', toggleToTop, { passive: true });
        window.addEventListener('resize', toggleToTop, { passive: true });
        toTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
        });
    }
})();
