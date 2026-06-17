(function () {
    function getErrorStatusCode() {
        var badgeNode = document.querySelector('.error-badge');

        if (!badgeNode) {
            return '';
        }

        var text = String(badgeNode.textContent || '');
        var match = text.match(/status\s*([45]\d{2}|[45]xx)/i);

        return match ? match[1].toLowerCase() : '';
    }

    function isServerErrorStatus(statusCode) {
        return statusCode.indexOf('5') === 0;
    }

    function setText(id, value) {
        var node = document.getElementById(id);
        if (node) {
            node.textContent = value;
        }
    }

    function setChipState(id, text, stateClass) {
        var node = document.getElementById(id);
        if (!node) {
            return;
        }

        node.textContent = text;
        node.classList.remove('is-ok', 'is-warning', 'is-danger');

        if (stateClass) {
            node.classList.add(stateClass);
        }
    }

    function updateClock() {
        var now = new Date();
        setText('timeBadge', now.toLocaleString('id-ID', { hour12: false }) + ' WIB');
        setText('yearValue', String(now.getFullYear()));
    }

    function checkConnection() {
        var retryButton = document.getElementById('connectionRetry');
        var currentStatus = getErrorStatusCode();
        var inServerErrorPage = isServerErrorStatus(currentStatus);

        if (!navigator.onLine) {
            setChipState('networkState', 'Perangkat: Offline', 'is-danger');
            setChipState('serverState', 'Server: Tidak dapat dicek', 'is-warning');
            setText('connectionHint', 'Koneksi internet di perangkat ini terputus. Periksa Wi-Fi/data seluler lalu coba lagi.');

            if (retryButton) {
                retryButton.disabled = false;
            }

            return;
        }

        setChipState('networkState', 'Perangkat: Online', 'is-ok');
        setChipState('serverState', 'Server: Memeriksa...', 'is-warning');
        setText('connectionHint', 'Perangkat terhubung. Sedang mengecek apakah server aplikasi sudah berjalan.');

        var healthUrl = '/up?ts=' + Date.now();

        fetch(healthUrl, {
            method: 'GET',
            cache: 'no-store',
            headers: {
                Accept: 'text/plain, text/html;q=0.9,*/*;q=0.8'
            }
        })
            .then(function (response) {
                return response.text().then(function (bodyText) {
                    return {
                        response: response,
                        bodyText: bodyText
                    };
                });
            })
            .then(function (result) {
                var response = result.response;
                var bodyText = String(result.bodyText || '').trim();
                var bodyTextLower = bodyText.toLowerCase();
                var contentType = String(response.headers.get('content-type') || '').toLowerCase();
                var looksHtml = contentType.indexOf('text/html') >= 0
                    || bodyTextLower.indexOf('<!doctype') >= 0
                    || bodyTextLower.indexOf('<html') >= 0;
                var looksHealthyToken = bodyTextLower === 'ok'
                    || bodyTextLower === 'up'
                    || bodyTextLower === 'healthy'
                    || bodyTextLower.indexOf('application up') >= 0;
                var validHealthPayload = !looksHtml && bodyText.length > 0 && bodyText.length <= 120 && looksHealthyToken;

                if (response.ok && validHealthPayload) {
                    if (inServerErrorPage) {
                        setChipState('serverState', 'Server: Merespons (sedang gangguan)', 'is-warning');
                        setText('connectionHint', 'Server bisa dijangkau, tetapi sedang mengembalikan error ' + (currentStatus || '5xx') + '. Coba lagi setelah layanan stabil.');
                    } else {
                        setChipState('serverState', 'Server: Berjalan', 'is-ok');
                        setText('connectionHint', 'Server merespons normal. Kamu bisa lanjut kembali ke beranda.');
                    }

                    return;
                }

                if (response.ok && !validHealthPayload) {
                    setChipState('serverState', 'Server: Respon tidak valid', 'is-warning');
                    setText('connectionHint', 'Terhubung ke host, tetapi endpoint kesehatan tidak valid. Jika lokal, server aplikasi mungkin belum berjalan penuh.');
                    return;
                }

                setChipState('serverState', 'Server: Belum siap (' + response.status + ')', 'is-warning');
                setText('connectionHint', 'Server terjangkau tetapi belum siap. Silakan tunggu beberapa saat lalu refresh.');
            })
            .catch(function () {
                setChipState('serverState', 'Server: Tidak merespons', 'is-danger');
                setText('connectionHint', 'Server belum berjalan atau tidak dapat dijangkau dari jaringan ini. Jika lokal, pastikan server PHP/Laragon sudah aktif.');
            })
            .finally(function () {
                if (retryButton) {
                    retryButton.disabled = false;
                }
            });
    }

    function initAutoBack() {
        var labelNode = document.getElementById('autoBackLabel');

        if (!labelNode) {
            return;
        }

        var secondsAttr = Number(document.body.getAttribute('data-auto-back-seconds'));
        var countdownSeconds = Number.isFinite(secondsAttr) && secondsAttr > 0 ? secondsAttr : 30;

        var countdownStorageKey = 'error_autoback_deadline_' + window.location.pathname;
        var nowMs = Date.now();
        var defaultDeadline = nowMs + (countdownSeconds * 1000);
        var deadlineMs = defaultDeadline;

        try {
            var savedDeadline = Number(window.sessionStorage.getItem(countdownStorageKey));
            if (Number.isFinite(savedDeadline) && savedDeadline > nowMs) {
                deadlineMs = savedDeadline;
            }
            window.sessionStorage.setItem(countdownStorageKey, String(deadlineMs));
        } catch (error) {
            deadlineMs = defaultDeadline;
        }

        function navigateHome() {
            try {
                window.sessionStorage.removeItem(countdownStorageKey);
            } catch (error) {
                // Ignore sessionStorage failure.
            }
            window.location.href = '/';
        }

        function updateLabel() {
            var remainingSeconds = Math.max(0, Math.ceil((deadlineMs - Date.now()) / 1000));
            labelNode.textContent = 'Otomatis kembali ke beranda dalam ' + remainingSeconds + ' detik.';

            if (remainingSeconds <= 0) {
                window.clearInterval(intervalId);
                navigateHome();
            }
        }

        updateLabel();
        var intervalId = window.setInterval(updateLabel, 500);
    }

    function initRetryButton() {
        var retryButton = document.getElementById('connectionRetry');

        if (!retryButton) {
            return;
        }

        retryButton.addEventListener('click', function () {
            retryButton.disabled = true;
            setChipState('serverState', 'Server: Memeriksa ulang...', 'is-warning');
            checkConnection();
        });
    }

    updateClock();
    initAutoBack();
    initRetryButton();
    checkConnection();

    window.addEventListener('online', checkConnection);
    window.addEventListener('offline', checkConnection);

    window.setInterval(updateClock, 30000);
})();
