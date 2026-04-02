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
