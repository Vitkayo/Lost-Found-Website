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
    <link href="/assets/lostfound.css?v=20260618-5" rel="stylesheet">
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
            const mobileAccount = document.querySelector('.cf-mobile-account-menu');

            if (!toggle || !icon || !nav) {
                return;
            }

            const closeMenu = function () {
                nav.classList.remove('is-menu-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Open navigation menu');
                icon.className = 'bi bi-list';
            };

            const closeMobileAccount = function () {
                if (mobileAccount) {
                    mobileAccount.removeAttribute('open');
                }
            };

            toggle.addEventListener('click', function () {
                closeMobileAccount();
                const isOpen = nav.classList.toggle('is-menu-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                toggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
                icon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
            });

            if (mobileAccount) {
                mobileAccount.addEventListener('toggle', function () {
                    if (mobileAccount.open) {
                        closeMenu();
                    }
                });
            }

            document.querySelectorAll('[data-menu-close]').forEach(function (link) {
                link.addEventListener('click', closeMenu);
            });

            document.addEventListener('click', function (event) {
                if (!nav.contains(event.target)) {
                    closeMenu();
                    closeMobileAccount();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMenu();
                    closeMobileAccount();
                    toggle.focus();
                }
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 767) {
                    closeMenu();
                    closeMobileAccount();
                }
            });
        })();

        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                const wrapper = button.closest('.cf-password-field');
                const input = wrapper?.querySelector('input');
                const icon = button.querySelector('i');
                if (!input || !icon) {
                    return;
                }

                const isVisible = input.type === 'text';
                input.type = isVisible ? 'password' : 'text';
                button.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
                icon.className = isVisible ? 'bi bi-eye' : 'bi bi-eye-slash';
            });
        });

        document.querySelectorAll('[data-resend-form]').forEach(function (form) {
            const button = form.querySelector('[data-resend-button]');
            const message = form.querySelector('[data-resend-message]');
            const storageKey = form.dataset.resendKey;
            const seconds = Number(form.dataset.resendSeconds || 60);

            if (!button || !message || !storageKey) {
                return;
            }

            let timerId = null;

            const render = function (remaining) {
                if (remaining > 0) {
                    button.disabled = true;
                    button.textContent = 'Send New Code (' + remaining + 's)';
                    message.textContent = 'Please wait before requesting another verification code.';
                    return;
                }

                button.disabled = false;
                button.textContent = 'Send New Code';
                message.textContent = 'You can request a new code if you did not receive the email.';
            };

            const startCountdown = function (expiresAt) {
                window.clearInterval(timerId);
                const tick = function () {
                    const remaining = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
                    render(remaining);

                    if (remaining <= 0) {
                        window.clearInterval(timerId);
                        localStorage.removeItem(storageKey);
                    }
                };

                tick();
                timerId = window.setInterval(tick, 1000);
            };

            const stored = Number(localStorage.getItem(storageKey));
            const serverUntil = form.dataset.resendUntil ? Date.parse(form.dataset.resendUntil) : 0;
            const initialUntil = Math.max(stored || 0, serverUntil || 0);

            if (initialUntil && initialUntil > Date.now()) {
                startCountdown(initialUntil);
            } else {
                render(0);
            }

            form.addEventListener('submit', function () {
                const expiresAt = Date.now() + (seconds * 1000);
                localStorage.setItem(storageKey, String(expiresAt));
                startCountdown(expiresAt);
            });
        });

        document.querySelectorAll('[data-live-countdown]').forEach(function (node) {
            const expiresAt = node.dataset.expiresAt ? Date.parse(node.dataset.expiresAt) : 0;
            const expiredText = node.dataset.expiredText || 'Expired';

            if (!expiresAt) {
                return;
            }

            const render = function () {
                const remainingMs = expiresAt - Date.now();

                if (remainingMs <= 0) {
                    node.textContent = expiredText;
                    return false;
                }

                const totalSeconds = Math.ceil(remainingMs / 1000);
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                node.textContent = 'Time remaining: ' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

                return true;
            };

            if (!render()) {
                return;
            }

            const timer = window.setInterval(function () {
                if (!render()) {
                    window.clearInterval(timer);
                }
            }, 1000);
        });

        document.querySelectorAll('[data-image-preview-input]').forEach(function (input) {
            input.addEventListener('change', function () {
                const wrapper = input.closest('label')?.querySelector('[data-image-preview]');
                const preview = wrapper?.querySelector('[data-image-preview-tag]');
                const file = input.files?.[0];

                if (!wrapper || !preview) {
                    return;
                }

                if (!file) {
                    wrapper.classList.add('d-none');
                    preview.removeAttribute('src');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.src = String(event.target?.result || '');
                    wrapper.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
