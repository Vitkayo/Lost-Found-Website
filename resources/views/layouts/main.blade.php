<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Campus Found') - Campus Found</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/campus-found-logo-nav.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/campus-found-logo-nav.png') }}">
    <link href="/assets/bootstrap-5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/lostfound.css?v=20260612-2" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-white">
    @unless (request()->routeIs('admin.*'))
        @include('partials.navbar')
    @endunless

    @if (session('success'))
        <div class="container mt-3">
            <div class="alert alert-success border-2 border-dark fw-bold mb-0" data-auto-dismiss>{{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger border-2 border-dark fw-bold mb-0" data-auto-dismiss>{{ session('error') }}</div>
        </div>
    @endif

    @yield('content')

    <script src="/assets/bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-auto-dismiss]').forEach(function (message) {
            window.setTimeout(function () {
                message.style.transition = 'opacity 0.3s ease';
                message.style.opacity = '0';
                window.setTimeout(function () {
                    message.parentElement?.remove();
                }, 300);
            }, 5000);
        });

        document.addEventListener('submit', async function (event) {
            const form = event.target.closest('[data-cf-claim-form]');
            if (!form) {
                return;
            }

            event.preventDefault();

            const success = form.parentElement.querySelector('.cf-inline-success');
            const submit = form.querySelector('[type="submit"]');
            const contact = form.querySelector('[name="contact_info"]');
            const messageField = form.querySelector('[name="message"]');

            for (const field of [contact, messageField]) {
                field.classList.remove('is-invalid');
            }

            if (!contact.value.trim()) {
                contact.focus();
                contact.classList.add('is-invalid');
                return;
            }

            if (!messageField.value.trim()) {
                messageField.focus();
                messageField.classList.add('is-invalid');
                return;
            }

            submit.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                if (!response.ok) {
                    throw new Error('Submit failed');
                }

                const data = await response.json();
                const itemId = form.querySelector('[name="item_id"]')?.value;
                form.reset();
                form.classList.add('d-none');

                if (success) {
                    success.textContent = data.message || form.dataset.successMessage || 'Submitted successfully.';
                    success.classList.remove('d-none');
                }

                if (itemId) {
                    document.querySelector(`[data-cf-item-id="${itemId}"]`)?.remove();
                }
            } catch (error) {
                if (success) {
                    success.textContent = 'Something went wrong. Please check your details and try again.';
                    success.classList.remove('d-none');
                }
            } finally {
                submit.disabled = false;
            }
        });

        document.addEventListener('shown.bs.collapse', function (event) {
            if (!event.target.id || !event.target.id.startsWith('claim-form-')) {
                return;
            }

            event.target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            event.target.querySelector('input[name="claimant_name"]')?.focus();
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-cf-card-button], a, button, input, textarea, select')) {
                return;
            }

            const card = event.target.closest('[data-cf-card-open]');
            if (!card) {
                return;
            }

            const modal = document.querySelector(card.dataset.cfCardOpen);
            if (modal) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (!['Enter', ' '].includes(event.key)) {
                return;
            }

            const card = event.target.closest('[data-cf-card-open]');
            if (!card || event.target !== card) {
                return;
            }

            event.preventDefault();
            const modal = document.querySelector(card.dataset.cfCardOpen);
            if (modal) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });

        (function () {
            const toggle = document.querySelector('[data-menu-toggle]');
            const icon = document.querySelector('[data-menu-icon]');
            const nav = document.querySelector('.cf-nav');

            if (!toggle || !icon || !nav) {
                return;
            }

            const closeMenu = function () {
                nav.classList.remove('is-menu-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Open navigation menu');
                icon.className = 'bi bi-list';
            };

            toggle.addEventListener('click', function () {
                const isOpen = nav.classList.toggle('is-menu-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                toggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
                icon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
            });

            document.querySelectorAll('.cf-nav-links a').forEach(function (link) {
                link.addEventListener('click', closeMenu);
            });

            document.addEventListener('click', function (event) {
                if (!nav.contains(event.target)) {
                    closeMenu();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMenu();
                    toggle.focus();
                }
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 767) {
                    closeMenu();
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
