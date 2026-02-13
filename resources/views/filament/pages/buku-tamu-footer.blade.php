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
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('open-url-in-new-tab', (event) => {
            window.open(event.url, '_blank');
        });
    });
</script>
