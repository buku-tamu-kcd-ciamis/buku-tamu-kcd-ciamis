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
