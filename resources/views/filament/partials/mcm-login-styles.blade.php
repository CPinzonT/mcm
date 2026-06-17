<style>
    /* Login — tarjeta centrada, sin barra lateral */
    body.fi-body:has(.mcm-auth-root),
    body.fi-body:has(.mcm-auth-root) .fi-simple-layout,
    body.fi-body:has(.mcm-auth-root) .fi-simple-main-ctn,
    body.fi-body:has(.mcm-auth-root) .fi-simple-main,
    body.fi-body:has(.mcm-auth-root) .fi-simple-page {
        background: #eef2f8 !important;
        background-image: none !important;
    }

    body.fi-body:has(.mcm-auth-root) .fi-simple-main-ctn,
    body.fi-body:has(.mcm-auth-root) .fi-simple-main {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .mcm-auth-root {
        width: 100%;
        min-height: calc(100vh - 2rem);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem 1rem;
    }

    .mcm-auth {
        width: min(100%, 26rem);
        margin: 0 auto;
    }

    .mcm-auth-panel {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.85rem;
    }

    .mcm-auth-card-top {
        display: flex;
        justify-content: center;
        margin-bottom: 0.25rem;
    }

    .mcm-auth-card-top .mcm-brand-logo {
        height: 2.75rem;
        max-width: 11rem;
    }

    .mcm-auth-card {
        background: #ffffff !important;
        border: 1px solid #d5dde9 !important;
        border-radius: 14px !important;
        box-shadow: 0 10px 32px rgba(15, 23, 42, 0.08) !important;
        padding: 1.5rem 1.35rem 1.25rem !important;
        max-width: 100%;
    }

    .mcm-auth-card .fi-simple-header-heading {
        color: #0f1117 !important;
        font-size: 1.35rem !important;
        font-weight: 700 !important;
        text-align: center;
    }

    .mcm-auth-card .fi-simple-header-subheading {
        color: #5c6478 !important;
        font-size: 0.85rem !important;
        line-height: 1.45 !important;
        text-align: center;
    }

    .mcm-auth-card label,
    .mcm-auth-card .fi-fo-field-wrp-label,
    .mcm-auth-card .fi-fo-field-wrp-label span,
    .mcm-auth-card .fi-checkbox-label {
        color: #1a1d26 !important;
        font-weight: 500 !important;
    }

    .mcm-auth-card .fi-input-wrp,
    .mcm-auth-card input {
        background: #f8fafc !important;
        border-color: #c5d0e4 !important;
        color: #1a1d26 !important;
    }

    .mcm-auth-card .fi-input-wrp:focus-within {
        border-color: #2852a0 !important;
        box-shadow: 0 0 0 2px rgba(40, 82, 160, 0.15) !important;
    }

    .mcm-auth-card .fi-btn.fi-btn-color-primary {
        width: 100%;
        background: #2852a0 !important;
        border-color: #2852a0 !important;
        font-weight: 600;
        padding-block: 0.65rem;
    }

    .mcm-auth-card .fi-btn.fi-btn-color-primary:hover {
        background: #1f4285 !important;
        border-color: #1f4285 !important;
    }

    .mcm-auth-footer {
        margin: 0;
        text-align: center;
        font-size: 0.72rem;
        color: #7b8499;
    }
</style>
