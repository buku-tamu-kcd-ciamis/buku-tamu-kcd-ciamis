<style>
    /* Fix: vertically center all table cells including action column */
    .fi-ta-row td.fi-ta-cell,
    .fi-ta-row td.fi-ta-actions-cell {
        vertical-align: middle !important;
    }

    .fi-ta-actions-cell > div {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .fi-ta-actions-header-cell {
        text-align: center !important;
    }
</style>

<script>
    function getPreferredOpenTarget(url) {
        const ua = (navigator.userAgent || '').toLowerCase();
        const isSafari = ua.includes('safari')
            && !ua.includes('chrome')
            && !ua.includes('crios')
            && !ua.includes('android')
            && !ua.includes('edg')
            && !ua.includes('opr');
        const isIos = /iphone|ipad|ipod/.test(ua);

        return (isSafari || isIos) ? '_self' : '_blank';
    }

    function openUrlCrossBrowser(url) {
        const target = getPreferredOpenTarget(url);

        if (target === '_self') {
            window.location.assign(url);

            return;
        }

        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.target = '_blank';
        anchor.rel = 'noopener noreferrer';
        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();
    }

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('open-url-in-new-tab', (event) => {
            if (!event || !event.url) {
                return;
            }

            openUrlCrossBrowser(event.url);
        });
    });
</script>
