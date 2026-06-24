<script>
window.mcmFmtChartMoney = function (value) {
    const n = Number(value);
    if (!isFinite(n)) {
        return '';
    }

    const abs = Math.abs(n);

    if (abs >= 1e6) {
        const millions = n / 1e6;
        const decimals = Math.abs(millions) >= 1000 ? 0 : 1;

        return '$' + millions.toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: decimals,
        }) + ' M';
    }

    if (abs >= 1e3) {
        return '$' + (n / 1e3).toLocaleString('es-CO', { maximumFractionDigits: 0 }) + ' K';
    }

    return '$' + Math.round(n).toLocaleString('es-CO');
};
</script>
