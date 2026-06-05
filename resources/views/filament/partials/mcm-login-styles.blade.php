<style>
    /* Login — diseño sobrio, sin saturación de azul */
    .fi-simple-layout,
    .fi-simple-layout .fi-simple-main-ctn,
    .fi-simple-layout .fi-simple-main,
    body.fi-body:has(.mcm-auth) {
        background: #eef2f8 !important;
        background-image: none !important;
    }

    .mcm-auth-root {
        width: 100%;
    }

    .mcm-auth {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(22rem, 0.95fr);
        gap: 2rem;
        align-items: center;
        width: min(100%, 56rem);
        margin: 0 auto;
        padding: 1.5rem 1rem 2rem;
        min-height: calc(100vh - 3rem);
    }

    .mcm-auth-brand {
        padding: 2rem 2.25rem;
        border-radius: 16px;
        background: linear-gradient(145deg, #1f4285 0%, #2852a0 62%, #3268b5 100%);
        color: #fff;
        box-shadow: 0 18px 48px rgba(15, 17, 23, 0.22);
    }

    .mcm-auth-brand-inner {
        max-width: 22rem;
    }

    .mcm-auth-mark {
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        margin-bottom: 1.25rem;
    }

    .mcm-auth-mark .mcm-brand-logo {
        height: 2.75rem;
        max-width: 10rem;
    }

    .mcm-auth-title {
        font-size: 1.65rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.15;
        margin: 0 0 0.65rem;
    }

    .mcm-auth-lead {
        margin: 0 0 1.25rem;
        font-size: 0.9rem;
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.78);
    }

    .mcm-auth-features {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 0.55rem;
        font-size: 0.82rem;
        color: rgba(255, 255, 255, 0.88);
    }

    .mcm-auth-features li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .mcm-auth-features li::before {
        content: "";
        width: 0.4rem;
        height: 0.4rem;
        border-radius: 50%;
        background: #5b9bd5;
        flex-shrink: 0;
    }

    .mcm-auth-panel {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
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
    }

    .mcm-auth-card .fi-simple-header-subheading {
        color: #5c6478 !important;
        font-size: 0.85rem !important;
        line-height: 1.45 !important;
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

    @media (max-width: 900px) {
        .mcm-auth {
            grid-template-columns: 1fr;
            max-width: 26rem;
        }

        .mcm-auth-brand {
            padding: 1.35rem 1.25rem;
        }

        .mcm-auth-title {
            font-size: 1.25rem;
        }

        .mcm-auth-features {
            display: none;
        }
    }
</style>
