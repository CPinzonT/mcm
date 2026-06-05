<style>
    /* MCM — fondo azul corporativo #2852a0 (referencia imagen) + tarjetas #D1DCE5 */
    :root {
        --mcm-brand: #2852a0;
        --mcm-brand-hover: #1f4285;
        --mcm-brand-light: #3d66b3;
        --mcm-brand-soft: rgba(40, 82, 160, 0.14);
        --mcm-app-bg: #2852a0;
        --mcm-bar-dark: #1f4285;
        --mcm-bar-mid: #2852a0;
        --mcm-surface: #d1dce5;
        --mcm-surface-soft: #e4ebf2;
        --mcm-surface-strong: #c5d4e3;
        --mcm-border: #b8c8d6;
        --mcm-text: #1a202c;
        --mcm-text-strong: #0f172a;
        --mcm-muted: #475569;
        --mcm-on-brand: #ffffff;
        --mcm-input-surface: #ffffff;
        --mcm-input-text: #1a202c;
        --mcm-nav-bg: #1f4285;
        --mcm-nav-bg-deep: #2852a0;
        --mcm-nav-accent: #5b9bd5;
    }

    .mcm-brand-logo,
    .fi-logo img,
    .fi-sidebar .fi-logo img {
        display: block;
        height: 2.35rem;
        width: auto;
        max-width: 9rem;
        object-fit: contain;
    }

    /* Fondo global azul marca */
    html,
    body.fi-body,
    .fi-body,
    .fi-layout,
    .fi-main-ctn,
    .fi-main,
    .fi-page,
    .fi-page-content,
    .fi-simple-layout,
    .fi-simple-main,
    .fi-simple-page {
        background-color: var(--mcm-app-bg) !important;
        background-image: linear-gradient(165deg, #2852a0 0%, #3268b5 48%, #2852a0 100%) !important;
    }

    /* Anula fondos grises de Tailwind/Filament en el área principal */
    .fi-main-ctn.bg-gray-50,
    .fi-main.bg-gray-50,
    .fi-page.bg-gray-50,
    .fi-page-content.bg-gray-50,
    [class*="bg-gray-50"].fi-main,
    [class*="bg-gray-50"].fi-page {
        background-color: var(--mcm-app-bg) !important;
        background-image: linear-gradient(165deg, #2852a0 0%, #3268b5 48%, #2852a0 100%) !important;
    }

    /* Topbar oscura — contraste con menú y contenido */
    .fi-topbar {
        background: linear-gradient(90deg, var(--mcm-bar-dark) 0%, var(--mcm-bar-mid) 100%) !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.35) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
    }

    .fi-topbar nav,
    .fi-topbar .fi-topbar-item-button,
    .fi-topbar .fi-icon-btn,
    .fi-topbar-open-sidebar-btn,
    .fi-topbar .fi-user-menu-trigger {
        color: rgba(255, 255, 255, 0.92) !important;
    }

    .fi-topbar .fi-input-wrp,
    .fi-topbar input[type="search"] {
        background: rgba(255, 255, 255, 0.12) !important;
        border-color: rgba(255, 255, 255, 0.28) !important;
        color: #fff !important;
    }

    .fi-topbar .fi-input-wrp input::placeholder {
        color: rgba(255, 255, 255, 0.55) !important;
    }

    /* Sidebar oscuro */
    .fi-sidebar,
    .fi-sidebar-nav,
    .fi-main-sidebar {
        background: linear-gradient(180deg, var(--mcm-nav-bg) 0%, var(--mcm-nav-bg-deep) 100%) !important;
        border-inline-end-color: rgba(0, 0, 0, 0.35) !important;
    }

    .fi-sidebar-header {
        background: transparent !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .fi-sidebar .fi-logo,
    .fi-sidebar .fi-logo span,
    .fi-sidebar-header-heading,
    .fi-sidebar-item-label,
    .fi-sidebar-item-button {
        color: rgba(255, 255, 255, 0.94) !important;
    }

    .fi-sidebar-group-label {
        color: rgba(255, 255, 255, 0.55) !important;
    }

    .fi-sidebar-item-button:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
    }

    /* Pestaña activa: pastilla blanca + texto azul (legible) */
    .fi-sidebar-item.fi-active > .fi-sidebar-item-button,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button {
        background: #ffffff !important;
        background-color: #ffffff !important;
        color: #2852a0 !important;
        font-weight: 700 !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.18) !important;
        border-inline-start: 4px solid #7eb8ff !important;
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button span {
        color: #2852a0 !important;
    }

    .fi-sidebar-item-icon,
    .fi-sidebar .fi-icon {
        color: var(--mcm-nav-accent) !important;
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button .fi-icon {
        color: #2852a0 !important;
    }

    /* Tarjetas / tablas / formularios sobre fondo navy */
    .fi-section,
    .fi-section-content-ctn,
    .fi-ta-ctn,
    .fi-wi-stats-overview-stat,
    .fi-fo-component-ctn,
    .ops-shell,
    .mcm-modern-page .filter-bar,
    .mcm-modern-page .chart-card,
    .mcm-modern-page .kpi-card,
    .mcm-modern-page .sd-kpi,
    .mcm-modern-page .sd-filter-bar,
    .mcm-modern-page .sd-filter-card,
    .mcm-modern-page .sd-chart-card,
    .mcm-modern-page .sd-compare-panel,
    .mcm-modern-page .bucket-section,
    .client-view .cv-card,
    .client-view .cv-kpi-item {
        color: var(--mcm-text);
    }

    .fi-section,
    .fi-ta-ctn,
    .fi-wi,
    .fi-wi-stats-overview-stat,
    .mcm-modern-page .filter-bar,
    .mcm-modern-page .chart-card,
    .mcm-modern-page .kpi-card,
    .mcm-modern-page .sd-kpi,
    .mcm-modern-page .sd-filter-bar,
    .mcm-modern-page .sd-filter-card,
    .mcm-modern-page .sd-chart-card,
    .mcm-modern-page .sd-compare-panel,
    .mcm-modern-page .sd-score-kpi,
    .mcm-modern-page .sd-compare-kpis,
    .client-view .cv-card,
    .client-view .cv-kpi-item {
        background: var(--mcm-surface) !important;
        border-color: var(--mcm-border) !important;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        color: var(--mcm-text) !important;
    }

    .client-view .cv-card-head {
        background: var(--mcm-surface-strong) !important;
        border-bottom-color: var(--mcm-border) !important;
    }

    .fi-section-header-heading,
    .fi-ta-header-heading,
    label.fi-fo-field-wrp-label span,
    .fi-ta-text-item-label,
    .mcm-modern-page .kpi-label,
    .mcm-modern-page .chart-title,
    .mcm-modern-page .sd-kpi-label,
    .mcm-modern-page .sd-checklist-title,
    .mcm-modern-page .sd-section-title,
    .mcm-modern-page .sd-chart-title,
    .mcm-modern-page .sd-compare-panel-title,
    .mcm-modern-page .sd-compare-side-title,
    .mcm-modern-page .sd-compare-row-label,
    .client-view .cv-kpi-label,
    .client-view .cv-card-title,
    .client-view .cv-datum-label {
        color: var(--mcm-muted) !important;
    }

    .mcm-modern-page .kpi-value:not(.c-green):not(.c-amber):not(.c-red):not(.c-blue),
    .mcm-modern-page .sd-kpi-value:not(.c-green):not(.c-amber):not(.c-red):not(.c-blue),
    .mcm-modern-page .sd-compare-row-val:not(:has(.delta-up)):not(:has(.delta-down)),
    .client-view .cv-kpi-val:not(.danger):not(.warn):not(.ok),
    .client-view .cv-datum-value,
    .client-view .cv-identity-name {
        color: var(--mcm-text-strong) !important;
    }

    .mcm-modern-page .kpi-sub,
    .mcm-modern-page .sd-kpi-sub,
    .client-view .cv-kpi-sub {
        color: var(--mcm-muted) !important;
    }

    .mcm-modern-page .sd-check-item {
        color: var(--mcm-text) !important;
    }

    .mcm-modern-page .sd-checklist-title {
        border-bottom-color: var(--mcm-border) !important;
    }

    .mcm-modern-page .kpi-value.c-green,
    .mcm-modern-page .sd-kpi-value.c-green {
        color: var(--mcm-green, #22c55e) !important;
    }

    .mcm-modern-page .kpi-value.c-amber,
    .mcm-modern-page .sd-kpi-value.c-amber {
        color: var(--mcm-amber, #f59e0b) !important;
    }

    .mcm-modern-page .kpi-value.c-red,
    .mcm-modern-page .sd-kpi-value.c-red {
        color: var(--mcm-red, #ef4444) !important;
    }

    .mcm-modern-page .kpi-value.c-blue,
    .mcm-modern-page .sd-kpi-value.c-blue {
        color: var(--mcm-brand, #2852a0) !important;
    }

    .fi-input-wrp,
    .fi-select-input,
    .fi-fo-text-input,
    input[type="text"],
    input[type="search"],
    input[type="date"],
    input[type="email"],
    input[type="password"],
    select,
    textarea,
    .mcm-modern-page .filter-input,
    .mcm-modern-page .sd-filter-input,
    .mcm-modern-page .sd-filter-search,
    .mcm-modern-page .sd-period-select,
    .client-view .cv-form-input,
    .client-view .sd-filter-search {
        background: var(--mcm-input-surface) !important;
        color: var(--mcm-input-text) !important;
        border-color: #b8c8d6 !important;
    }

    .fi-ta-header-cell,
    .fi-ta-cell {
        color: var(--mcm-text) !important;
    }

    .fi-ta-header-cell {
        background: var(--mcm-surface-strong) !important;
        color: var(--mcm-muted) !important;
    }

    .client-view .data-table th {
        color: var(--mcm-muted) !important;
        background: var(--mcm-surface-strong) !important;
    }

    .client-view .data-table td {
        color: var(--mcm-text) !important;
        border-color: var(--mcm-border) !important;
    }

    .client-view .cv-doc-link {
        color: var(--mcm-brand) !important;
    }

    .fi-btn-color-primary {
        background: var(--mcm-brand) !important;
        border-color: var(--mcm-brand) !important;
        color: #fff !important;
    }

    .fi-btn-color-primary:hover {
        background: var(--mcm-brand-hover) !important;
        border-color: var(--mcm-brand-hover) !important;
    }

    /* Modales / dropdowns legibles */
    .fi-modal-window,
    .fi-dropdown-panel {
        background: var(--mcm-surface) !important;
        color: var(--mcm-text) !important;
    }
</style>
