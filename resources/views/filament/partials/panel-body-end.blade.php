@if (($showLoginFooter ?? false) === true)
    @include('filament.partials.login-footer')
@endif

<script>
    if ('serviceWorker' in navigator) {
        let pwaRefreshing = false;

        navigator.serviceWorker.addEventListener('controllerchange', function () {
            if (pwaRefreshing) {
                return;
            }

            pwaRefreshing = true;
            window.location.reload();
        });

        function showServiceWorkerUpdatePrompt(waitingWorker) {
            if (!waitingWorker || document.getElementById('pwa-update-banner')) {
                return;
            }

            const banner = document.createElement('div');
            banner.id = 'pwa-update-banner';
            banner.style.position = 'fixed';
            banner.style.right = '16px';
            banner.style.bottom = '16px';
            banner.style.zIndex = '9999';
            banner.style.maxWidth = '360px';
            banner.style.padding = '12px 14px';
            banner.style.borderRadius = '12px';
            banner.style.background = '#14532d';
            banner.style.color = '#ffffff';
            banner.style.boxShadow = '0 12px 30px rgba(0, 0, 0, 0.2)';
            banner.style.fontSize = '14px';
            banner.style.lineHeight = '1.45';

            const message = document.createElement('div');
            message.textContent = 'Versi baru aplikasi tersedia.';

            const actions = document.createElement('div');
            actions.style.display = 'flex';
            actions.style.gap = '8px';
            actions.style.marginTop = '10px';

            const refreshButton = document.createElement('button');
            refreshButton.type = 'button';
            refreshButton.textContent = 'Perbarui';
            refreshButton.style.border = '0';
            refreshButton.style.borderRadius = '8px';
            refreshButton.style.padding = '7px 10px';
            refreshButton.style.background = '#ffffff';
            refreshButton.style.color = '#14532d';
            refreshButton.style.cursor = 'pointer';
            refreshButton.style.fontWeight = '600';

            const dismissButton = document.createElement('button');
            dismissButton.type = 'button';
            dismissButton.textContent = 'Nanti';
            dismissButton.style.border = '1px solid rgba(255, 255, 255, 0.4)';
            dismissButton.style.borderRadius = '8px';
            dismissButton.style.padding = '7px 10px';
            dismissButton.style.background = 'transparent';
            dismissButton.style.color = '#ffffff';
            dismissButton.style.cursor = 'pointer';

            refreshButton.addEventListener('click', function () {
                waitingWorker.postMessage({ type: 'SKIP_WAITING' });
            });

            dismissButton.addEventListener('click', function () {
                banner.remove();
            });

            actions.appendChild(refreshButton);
            actions.appendChild(dismissButton);
            banner.appendChild(message);
            banner.appendChild(actions);
            document.body.appendChild(banner);
        }

        window.addEventListener('load', function () {
            navigator.serviceWorker.register('{{ asset('sw.js') }}').then(function (registration) {
                if (registration.waiting && navigator.serviceWorker.controller) {
                    showServiceWorkerUpdatePrompt(registration.waiting);
                }

                registration.addEventListener('updatefound', function () {
                    const installingWorker = registration.installing;

                    if (!installingWorker) {
                        return;
                    }

                    installingWorker.addEventListener('statechange', function () {
                        if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            showServiceWorkerUpdatePrompt(installingWorker);
                        }
                    });
                });
            }).catch(function (error) {
                console.warn('Service worker registration failed:', error);
            });
        });
    }
</script>

<script>
    (function () {
        const FIRST_CONFIRM_MESSAGE = 'Yakin ingin logout dari akun ini?';
        const SECOND_CONFIRM_MESSAGE = 'Konfirmasi sekali lagi: logout sekarang?';
        const DIALOG_TITLE = 'Konfirmasi Logout';
        const DIALOG_PRIMARY_TEXT = 'Lanjut Logout';
        const DIALOG_SECONDARY_TEXT = 'Batal';
        const DIALOG_LAST_TEXT = 'Ya, Logout Sekarang';
        const DIALOG_ID = 'logout-confirm-dialog';

        function ensureDialogStyle() {
            if (document.getElementById('logout-confirm-dialog-style')) {
                return;
            }

            const style = document.createElement('style');
            style.id = 'logout-confirm-dialog-style';
            style.textContent = [
                '.logout-confirm-overlay {',
                '  position: fixed;',
                '  inset: 0;',
                '  display: grid;',
                '  place-items: center;',
                '  padding: 16px;',
                '  background: rgba(2, 6, 23, 0.58);',
                '  backdrop-filter: blur(3px);',
                '  z-index: 99999;',
                '}',
                '.logout-confirm-overlay.is-hidden {',
                '  display: none !important;',
                '}',
                '.logout-confirm-card {',
                '  width: min(440px, 100%);',
                '  border-radius: 16px;',
                '  border: 1px solid rgba(148, 163, 184, 0.34);',
                '  background: linear-gradient(180deg, #0f172a 0%, #0b1222 100%);',
                '  color: #e2e8f0;',
                '  box-shadow: 0 30px 70px -35px rgba(15, 23, 42, 0.95);',
                '}',
                '.logout-confirm-head {',
                '  display: flex;',
                '  align-items: center;',
                '  gap: 10px;',
                '  padding: 16px 18px 10px;',
                '}',
                '.logout-confirm-badge {',
                '  width: 30px;',
                '  height: 30px;',
                '  border-radius: 999px;',
                '  display: grid;',
                '  place-items: center;',
                '  font-size: 14px;',
                '  color: #f8fafc;',
                '  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);',
                '}',
                '.logout-confirm-title {',
                '  margin: 0;',
                '  font-weight: 700;',
                '  font-size: 1rem;',
                '  color: #f8fafc;',
                '}',
                '.logout-confirm-message {',
                '  margin: 0;',
                '  padding: 2px 18px 18px;',
                '  color: #cbd5e1;',
                '  line-height: 1.5;',
                '}',
                '.logout-confirm-actions {',
                '  display: flex;',
                '  justify-content: flex-end;',
                '  gap: 10px;',
                '  border-top: 1px solid rgba(148, 163, 184, 0.22);',
                '  padding: 14px 18px 16px;',
                '}',
                '.logout-confirm-btn {',
                '  border-radius: 12px;',
                '  padding: 8px 14px;',
                '  min-width: 112px;',
                '  font-weight: 600;',
                '  font-size: 0.9rem;',
                '  border: 1px solid transparent;',
                '  cursor: pointer;',
                '}',
                '.logout-confirm-btn-cancel {',
                '  background: transparent;',
                '  color: #cbd5e1;',
                '  border-color: rgba(148, 163, 184, 0.45);',
                '}',
                '.logout-confirm-btn-cancel:hover {',
                '  color: #f1f5f9;',
                '  border-color: rgba(148, 163, 184, 0.72);',
                '  background: rgba(148, 163, 184, 0.11);',
                '}',
                '.logout-confirm-btn-confirm {',
                '  color: #eff6ff;',
                '  border-color: rgba(37, 99, 235, 0.88);',
                '  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);',
                '  box-shadow: 0 14px 22px -18px rgba(37, 99, 235, 0.88);',
                '}',
                '.logout-confirm-btn-confirm:hover {',
                '  filter: brightness(1.08);',
                '}',
                '.logout-confirm-btn-confirm.is-danger {',
                '  border-color: rgba(239, 68, 68, 0.88);',
                '  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);',
                '  box-shadow: 0 14px 22px -18px rgba(239, 68, 68, 0.9);',
                '}',
                '@media (max-width: 540px) {',
                '  .logout-confirm-actions {',
                '    flex-direction: column-reverse;',
                '  }',
                '  .logout-confirm-btn {',
                '    width: 100%;',
                '  }',
                '}',
            ].join('\n');

            document.head.appendChild(style);
        }

        function createDialogElement() {
            const overlay = document.createElement('div');
            overlay.id = DIALOG_ID;
            overlay.className = 'logout-confirm-overlay is-hidden';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.setAttribute('aria-hidden', 'true');
            overlay.innerHTML = [
                '<div class="logout-confirm-card" role="document">',
                '  <div class="logout-confirm-head">',
                '    <span class="logout-confirm-badge">!</span>',
                '    <h3 class="logout-confirm-title"></h3>',
                '  </div>',
                '  <p class="logout-confirm-message"></p>',
                '  <div class="logout-confirm-actions">',
                '    <button type="button" class="logout-confirm-btn logout-confirm-btn-cancel"></button>',
                '    <button type="button" class="logout-confirm-btn logout-confirm-btn-confirm"></button>',
                '  </div>',
                '</div>',
            ].join('');

            document.body.appendChild(overlay);

            return overlay;
        }

        function getDialogElement() {
            ensureDialogStyle();

            return document.getElementById(DIALOG_ID) || createDialogElement();
        }

        function isLikelyLogoutAction(action) {
            if (!action) {
                return false;
            }

            const normalized = String(action).toLowerCase();

            return normalized.includes('/logout') || normalized.includes('.auth.logout');
        }

        function openConfirmationDialog(options) {
            const dialog = getDialogElement();
            const titleEl = dialog.querySelector('.logout-confirm-title');
            const messageEl = dialog.querySelector('.logout-confirm-message');
            const cancelBtn = dialog.querySelector('.logout-confirm-btn-cancel');
            const confirmBtn = dialog.querySelector('.logout-confirm-btn-confirm');
            const badge = dialog.querySelector('.logout-confirm-badge');

            titleEl.textContent = options.title || DIALOG_TITLE;
            messageEl.textContent = options.message || '';
            cancelBtn.textContent = options.cancelText || DIALOG_SECONDARY_TEXT;
            confirmBtn.textContent = options.confirmText || DIALOG_PRIMARY_TEXT;
            confirmBtn.classList.toggle('is-danger', Boolean(options.danger));

            if (options.danger) {
                badge.textContent = '!!';
                badge.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
            } else {
                badge.textContent = '!';
                badge.style.background = 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)';
            }

            dialog.classList.remove('is-hidden');
            dialog.setAttribute('aria-hidden', 'false');

            return new Promise(function (resolve) {
                let settled = false;

                function finish(result) {
                    if (settled) {
                        return;
                    }

                    settled = true;
                    dialog.classList.add('is-hidden');
                    dialog.setAttribute('aria-hidden', 'true');
                    dialog.removeEventListener('click', onOverlayClick);
                    dialog.removeEventListener('keydown', onKeyDown);
                    cancelBtn.removeEventListener('click', onCancel);
                    confirmBtn.removeEventListener('click', onConfirm);
                    resolve(result);
                }

                function onCancel() {
                    finish(false);
                }

                function onConfirm() {
                    finish(true);
                }

                function onOverlayClick(event) {
                    if (event.target === dialog) {
                        finish(false);
                    }
                }

                function onKeyDown(event) {
                    if (event.key === 'Escape') {
                        finish(false);
                    }
                }

                dialog.addEventListener('click', onOverlayClick);
                dialog.addEventListener('keydown', onKeyDown);
                cancelBtn.addEventListener('click', onCancel);
                confirmBtn.addEventListener('click', onConfirm);

                setTimeout(function () {
                    confirmBtn.focus();
                }, 0);
            });
        }

        async function requestDoubleLogoutConfirmation() {
            const firstConfirmed = await openConfirmationDialog({
                title: DIALOG_TITLE,
                message: FIRST_CONFIRM_MESSAGE,
                confirmText: DIALOG_PRIMARY_TEXT,
                cancelText: DIALOG_SECONDARY_TEXT,
                danger: false,
            });

            if (!firstConfirmed) {
                return false;
            }

            return openConfirmationDialog({
                title: DIALOG_TITLE,
                message: SECOND_CONFIRM_MESSAGE,
                confirmText: DIALOG_LAST_TEXT,
                cancelText: DIALOG_SECONDARY_TEXT,
                danger: true,
            });
        }

        document.addEventListener('submit', async function (event) {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (!isLikelyLogoutAction(form.getAttribute('action'))) {
                return;
            }

            if (form.dataset.logoutDoubleConfirmed === '1') {
                return;
            }

            event.preventDefault();

            const confirmed = await requestDoubleLogoutConfirmation();

            if (!confirmed) {
                return;
            }

            form.dataset.logoutDoubleConfirmed = '1';
            HTMLFormElement.prototype.submit.call(form);
        }, true);
    })();
</script>
