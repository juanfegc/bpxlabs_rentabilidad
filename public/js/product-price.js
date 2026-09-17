(() => {
    const initialize = () => {
        document.querySelectorAll('[data-retail-price]').forEach((retail) => {
            if (retail.dataset.priceInitialized) return;
            const net = retail.closest('form')?.querySelector('[data-net-price]');
            if (!net) return;
            retail.dataset.priceInitialized = 'true';
            retail.addEventListener('input', () => {
                const value = retail.value.trim();
                net.value = /^\d{1,9}(\.\d{1,4})?$/.test(value)
                    ? (Number(value) / (1 + Number(retail.dataset.vatPercent) / 100)).toFixed(4)
                    : '';
            });
        });
    };
    document.addEventListener('DOMContentLoaded', initialize);
    document.addEventListener('turbo:load', initialize);
    initialize();
})();
