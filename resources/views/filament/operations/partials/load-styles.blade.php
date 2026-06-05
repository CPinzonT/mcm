@once
    @push('styles')
        <style>
            .ops-shell {
                --mcm-primary: #2852a0;
                --mcm-primary-hover: #1f4285;
                --mcm-primary-soft: rgba(40, 82, 160, 0.1);
                --mcm-bg: transparent;
                --mcm-surface: #FFFFFF;
                --mcm-surface-soft: #F8FAFC;
                --mcm-surface-strong: #EFF2F7;
                --mcm-border: #D8E0EC;
                --mcm-border-strong: #B0BEC5;
                --mcm-text: #2B2B2B;
                --mcm-text-strong: #111111;
                --mcm-muted: #6B7280;
                --mcm-soft: #94A3B8;
                --mcm-green: #22C55E;
                --mcm-green-bg: #DCFCE7;
                --mcm-amber: #F59E0B;
                --mcm-amber-bg: #FEF3C7;
                --mcm-red: #EF4444;
                --mcm-red-bg: #FEE2E2;
                --mcm-blue: #1E5AA8;
                --mcm-blue-bg: #DBEAFE;
                --mcm-shadow-hover: 0 4px 14px rgba(15, 23, 42, 0.08);
                width: min(100%, 86rem);
                margin-inline: auto;
                display: flex;
                flex-direction: column;
                gap: 1rem;
                color: var(--mcm-text);
            }

            .dark .ops-shell {
                --mcm-primary: #3B82F6;
                --mcm-primary-hover: #60A5FA;
                --mcm-primary-soft: rgba(59, 130, 246, 0.08);
                --mcm-bg: #0F172A;
                --mcm-surface: #1E293B;
                --mcm-surface-soft: #253347;
                --mcm-surface-strong: #334155;
                --mcm-border: #334155;
                --mcm-border-strong: #475569;
                --mcm-text: #E2E8F0;
                --mcm-text-strong: #F8FAFC;
                --mcm-muted: #94A3B8;
                --mcm-soft: #64748B;
                --mcm-green: #4ADE80;
                --mcm-green-bg: rgba(34, 197, 94, 0.15);
                --mcm-amber: #FBBF24;
                --mcm-amber-bg: rgba(245, 158, 11, 0.15);
                --mcm-red: #F87171;
                --mcm-red-bg: rgba(239, 68, 68, 0.15);
                --mcm-blue: #60A5FA;
                --mcm-blue-bg: rgba(59, 130, 246, 0.15);
                --mcm-shadow-hover: 0 4px 14px rgba(0, 0, 0, 0.25);
            }

            body:has(.ops-shell),
            body:has(.ops-shell) .fi-main,
            body:has(.ops-shell) .fi-page,
            body:has(.ops-shell) .fi-page-content {
                background: #2852a0 !important;
                background-image: linear-gradient(165deg, #2852a0 0%, #1f4285 50%, #2852a0 100%) !important;
            }

            .dark body:has(.ops-shell),
            .dark body:has(.ops-shell) .fi-main,
            .dark body:has(.ops-shell) .fi-page,
            .dark body:has(.ops-shell) .fi-page-content {
                background: #0F172A;
            }

            body:has(.ops-shell) .fi-main {
                padding-block: 1.25rem 2.25rem;
            }

            .ops-hero, .ops-card, .ops-result, .ops-table-shell,
            .ops-upload-card, .ops-panel, .ops-metric {
                background: var(--mcm-surface, #FFFFFF);
                border: 1px solid var(--mcm-border, #D8E0EC);
                border-radius: 12px;
            }

            .ops-hero {
                padding: 1.5rem 1.75rem;
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .ops-title {
                color: var(--mcm-text-strong, #111111);
                font-size: 1.75rem;
                font-weight: 700;
                letter-spacing: -0.02em;
                line-height: 1.15;
            }

            .ops-subtitle,
            .ops-section-copy,
            .ops-kpi-sub,
            .ops-helper-list,
            .ops-list,
            .ops-meta-label,
            .ops-file-meta,
            .ops-file-loading,
            .ops-period-hint,
            .ops-metric-sub,
            .ops-metric-label {
                color: var(--mcm-muted, #6B7280);
            }

            .ops-subtitle { font-size: 0.82rem; margin-top: 0.35rem; max-width: 46rem; line-height: 1.5; }
            .ops-kpi-sub { font-size: 0.78rem; margin-top: 0.3rem; }
            .ops-section-copy { font-size: 0.82rem; }

            .ops-kpi-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr)); }

            .ops-card, .ops-result { padding: 1.25rem; }

            .ops-kpi-label, .ops-meta-label {
                font-size: 0.72rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: var(--mcm-muted, #6B7280);
            }

            .ops-kpi-value {
                margin-top: 0.5rem;
                font-size: 1.55rem;
                font-weight: 700;
                letter-spacing: -0.02em;
                color: var(--mcm-text-strong, #111111);
            }

            .ops-meta-value {
                margin-top: 0.35rem;
                font-size: 0.92rem;
                font-weight: 600;
                color: var(--mcm-text-strong, #111111);
            }

            .ops-section-title {
                font-size: 0.95rem;
                font-weight: 600;
                color: var(--mcm-text-strong, #111111);
                margin-bottom: 0.3rem;
            }

            .ops-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-start; }

            .ops-btn-primary, .ops-btn-secondary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.45rem;
                padding: 0.6rem 1rem;
                min-height: 2.5rem;
                border-radius: 6px;
                font-size: 0.82rem;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
            }

            .ops-btn-primary {
                background: var(--mcm-primary, #1E5AA8);
                color: #fff;
                border: 1px solid var(--mcm-primary, #1E5AA8);
            }

            .ops-btn-primary:hover {
                background: var(--mcm-primary-hover, #15427A);
                border-color: var(--mcm-primary-hover, #15427A);
            }

            .ops-btn-secondary {
                background: var(--mcm-surface, #FFFFFF);
                color: var(--mcm-text, #2B2B2B);
                border: 1px solid var(--mcm-border, #D8E0EC);
            }

            .ops-btn-secondary:hover {
                background: var(--mcm-surface-soft, #F8FAFC);
                border-color: var(--mcm-border-strong, #B0BEC5);
            }

            .ops-input, .ops-textarea {
                width: 100%;
                border-radius: 8px;
                border: 1px solid var(--mcm-border, #D8E0EC);
                background: var(--mcm-surface, #FFFFFF);
                color: var(--mcm-text, #2B2B2B);
                padding: 0.5rem 0.75rem;
                font-size: 0.82rem;
                outline: none;
            }

            .ops-input:focus, .ops-textarea:focus {
                border-color: var(--mcm-primary, #1E5AA8);
                box-shadow: 0 0 0 2px var(--mcm-primary-soft, rgba(30,90,168,0.08));
            }

            .ops-textarea { min-height: 5rem; resize: vertical; }

            .ops-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.2rem;
                padding: 0.2rem 0.55rem;
                border-radius: 9999px;
                font-size: 0.72rem;
                font-weight: 600;
            }

            .ops-badge-success { background: var(--mcm-green-bg, #DCFCE7); color: var(--mcm-green, #22C55E); }
            .ops-badge-danger { background: var(--mcm-red-bg, #FEE2E2); color: var(--mcm-red, #EF4444); }
            .ops-badge-warning { background: var(--mcm-amber-bg, #FEF3C7); color: var(--mcm-amber, #F59E0B); }
            .ops-badge-info { background: var(--mcm-blue-bg, #DBEAFE); color: var(--mcm-blue, #1E5AA8); }

            .ops-form-grid { display: grid; gap: 1rem; grid-template-columns: 2fr 1fr; }
            .ops-helper-list { margin-top: 0.85rem; display: grid; gap: 0.45rem; font-size: 0.82rem; }
            .ops-result-head { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; }

            .ops-preview-list { margin-top: 1rem; display: grid; gap: 0.55rem; }
            .ops-preview-item {
                border: 1px solid var(--mcm-border, #D8E0EC);
                border-radius: 8px;
                padding: 0.75rem 0.9rem;
                background: var(--mcm-surface-soft, #F8FAFC);
                font-size: 0.82rem;
            }

            .ops-meta-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr)); }
            .ops-detail-grid { display: grid; gap: 1rem; grid-template-columns: 1.2fr 0.8fr; }
            .ops-list { margin-top: 0.8rem; display: grid; gap: 0.5rem; font-size: 0.82rem; }
            .ops-anchor-title { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; margin-bottom: 0.8rem; }

            .ops-hero-kicker {
                color: var(--mcm-muted, #6B7280);
                font-size: 0.72rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                margin-bottom: 0.3rem;
            }

            .ops-metric-strip { display: grid; gap: 0.75rem; grid-template-columns: repeat(4, minmax(0, 1fr)); }

            .ops-metric { min-width: 0; padding: 1rem 1.15rem; }
            .ops-metric-label { font-size: 0.72rem; font-weight: 600; }
            .ops-metric-value { font-size: 1.2rem; font-weight: 700; letter-spacing: -0.02em; line-height: 1.2; margin-top: 0.35rem; overflow-wrap: anywhere; color: var(--mcm-text-strong, #111111); }
            .ops-metric-sub { font-size: 0.78rem; margin-top: 0.3rem; }

            .ops-load-layout { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1.55fr) minmax(18rem, 0.85fr); align-items: start; }
            .ops-upload-card { padding: 1.25rem; }
            .ops-upload-grid { align-items: stretch; display: grid; gap: 0.9rem; grid-template-columns: minmax(0, 1.25fr) minmax(13rem, 0.75fr); margin-top: 1rem; }
            .ops-upload-grid--simple { grid-template-columns: minmax(0, 1fr); }
            .ops-field-span { grid-column: 1 / -1; }
            .ops-side-stack { display: grid; gap: 1rem; }
            .ops-panel { padding: 1.05rem; }
            .ops-panel-soft { background: var(--mcm-surface-soft, #F8FAFC); box-shadow: none; }

            .ops-step-list, .ops-compact-list { display: grid; font-size: 0.82rem; gap: 0.55rem; margin-top: 0.85rem; color: var(--mcm-muted, #6B7280); }
            .ops-step-list > div, .ops-compact-list > div { display: grid; gap: 0.15rem; }
            .ops-step-list strong, .ops-compact-list strong { color: var(--mcm-text-strong, #111111); }

            .ops-guide { border: 1px solid var(--mcm-border, #D8E0EC); border-radius: 12px; color: var(--mcm-muted, #6B7280); font-size: 0.82rem; overflow: hidden; }
            .ops-guide summary { color: var(--mcm-text-strong, #111111); cursor: pointer; font-weight: 600; list-style: none; padding: 0.85rem 0.95rem; }
            .ops-guide summary::-webkit-details-marker { display: none; }
            .ops-guide summary::after { content: "+"; float: right; font-weight: 700; }
            .ops-guide[open] summary::after { content: "-"; }
            .ops-guide-body { border-top: 1px solid var(--mcm-border, #D8E0EC); display: grid; gap: 0.5rem; padding: 0.85rem 0.95rem; }

            .ops-form-footer { align-items: center; display: flex; flex-wrap: wrap; gap: 0.85rem; justify-content: space-between; margin-top: 1rem; }
            .ops-result--quiet { box-shadow: none; }
            .ops-error-text { color: var(--mcm-red, #EF4444); font-size: 0.78rem; margin-top: 0.35rem; }
            .ops-history-anchor { scroll-margin-top: 6rem; }

            .ops-history-panel {
                background: var(--mcm-surface, #FFFFFF);
                border: 1px solid var(--mcm-border, #D8E0EC);
                border-radius: 12px;
                padding: 1rem 1.15rem;
                scroll-margin-top: 5rem;
            }

            /* Tabla de historial (EmbeddedTable) dentro de páginas de carga */
            body:has(.ops-shell) .fi-resource-relation-manager,
            body:has(.ops-shell) .fi-ta-ctn,
            body:has(.ops-shell) .fi-embedded-table {
                background: var(--mcm-surface, #FFFFFF);
                border: 1px solid var(--mcm-border, #D8E0EC);
                border-radius: 12px;
                overflow: hidden;
                box-shadow: none;
            }

            body:has(.ops-shell) .fi-ta-header-heading {
                font-size: 0.95rem;
                font-weight: 700;
                color: var(--mcm-text-strong, #111111);
            }

            body:has(.ops-shell) .fi-ta-actions .fi-ac-btn-action {
                font-size: 0.78rem;
            }

            .ops-file-dropzone {
                align-items: center;
                background: var(--mcm-surface-soft, #F8FAFC);
                border: 1.5px dashed var(--mcm-border-strong, #B0BEC5);
                border-radius: 12px;
                color: var(--mcm-text, #2B2B2B);
                cursor: pointer;
                display: grid;
                gap: 0.85rem;
                grid-template-columns: auto minmax(0, 1fr) auto;
                min-height: 5.25rem;
                overflow: hidden;
                padding: 1rem;
                position: relative;
            }

            .ops-file-dropzone:hover, .ops-file-dropzone:focus-within {
                background: var(--mcm-surface, #FFFFFF);
                border-color: var(--mcm-primary, #1E5AA8);
                box-shadow: var(--mcm-shadow-hover, 0 4px 14px rgba(15,23,42,0.08));
            }

            .ops-file-dropzone--filled {
                border-style: solid;
                border-color: var(--mcm-border-strong, #B0BEC5);
                background: var(--mcm-surface, #FFFFFF);
            }

            .ops-file-input { cursor: pointer; height: 100%; inset: 0; opacity: 0; position: absolute; width: 100%; z-index: 2; }

            .ops-period-card {
                align-content: center;
                background: var(--mcm-surface-soft, #F8FAFC);
                border: 1px solid var(--mcm-border, #D8E0EC);
                border-radius: 12px;
                display: grid;
                min-height: 5.25rem;
                padding: 0.9rem 1rem;
            }
            .ops-period-card .ops-input { background: var(--mcm-surface, #FFFFFF); margin-top: 0.45rem; min-height: 2.5rem; }
            .ops-period-hint { font-size: 0.72rem; margin-top: 0.35rem; }

            .ops-file-icon {
                align-items: center;
                background: var(--mcm-primary-soft, rgba(30,90,168,0.08));
                border: 1px solid var(--mcm-border, #D8E0EC);
                border-radius: 8px;
                color: var(--mcm-primary, #1E5AA8);
                display: inline-flex;
                font-size: 0.72rem;
                font-weight: 600;
                height: 2.75rem;
                justify-content: center;
                width: 2.75rem;
            }

            .ops-file-copy { display: grid; gap: 0.2rem; min-width: 0; }
            .ops-file-title { color: var(--mcm-text-strong, #111111); font-size: 0.88rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .ops-file-meta, .ops-file-loading { font-size: 0.78rem; }

            .ops-file-loading {
                display: none;
            }

            .ops-file-action {
                align-items: center;
                background: var(--mcm-primary, #1E5AA8);
                border-radius: 6px;
                color: #ffffff;
                display: inline-flex;
                font-size: 0.78rem;
                font-weight: 600;
                min-height: 2.35rem;
                padding: 0.5rem 0.75rem;
            }

            @media (max-width: 1100px) {
                .ops-metric-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .ops-load-layout { grid-template-columns: 1fr; }
            }

            @media (max-width: 700px) {
                .ops-metric-strip, .ops-upload-grid, .ops-form-grid, .ops-detail-grid { grid-template-columns: 1fr; }
                .ops-file-dropzone { grid-template-columns: auto minmax(0, 1fr); }
                .ops-file-action { grid-column: 1 / -1; justify-content: center; }
                .ops-period-card { min-height: auto; }
            }
        </style>
    @endpush
@endonce
