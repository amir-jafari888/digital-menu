/* Admin shell: off-canvas navigation + safe confirmations. */
(function () {
    'use strict';

    var body = document.body;
    var toggle = document.getElementById('admin-menu-toggle');
    var scrim = document.getElementById('admin-scrim');
    var sidebar = document.getElementById('admin-sidebar');

    function setNav(open) {
        body.classList.toggle('is-nav-open', open);
        if (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (scrim) {
            scrim.hidden = !open;
        }
        if (open && sidebar) {
            var first = sidebar.querySelector('a, button');
            if (first) {
                first.focus({ preventScroll: true });
            }
        }
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            setNav(!body.classList.contains('is-nav-open'));
        });
    }

    if (scrim) {
        scrim.addEventListener('click', function () {
            setNav(false);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && body.classList.contains('is-nav-open')) {
            setNav(false);
            if (toggle) {
                toggle.focus();
            }
        }
    });

    /* Destructive forms ask for confirmation. */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || 'مطمئنی؟';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    /* Show the selected file name next to the upload input. */
    document.querySelectorAll('input[type="file"][data-file-label]').forEach(function (input) {
        input.addEventListener('change', function () {
            var target = document.getElementById(input.getAttribute('data-file-label'));
            if (target) {
                target.textContent = input.files && input.files.length ? input.files[0].name : '';
            }
        });
    });

    /* Close the off-canvas menu after following a link (mobile). */
    if (sidebar) {
        sidebar.addEventListener('click', function (event) {
            if (event.target.closest('a') && body.classList.contains('is-nav-open')) {
                setNav(false);
            }
        });
    }
}());
