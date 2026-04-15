@once
@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════
   MCM Design System — Blue Corporate
   Paleta azul MCM (#1E5AA8) + slate neutrals. Light & Dark.
   ══════════════════════════════════════════════════════════════ */

.mcm-modern-page {
    --mcm-primary: #1E5AA8;
    --mcm-primary-hover: #15427A;
    --mcm-primary-strong: #0F2A50;
    --mcm-primary-light: #dbeafe;
    --mcm-primary-soft: rgba(30, 90, 168, 0.08);
    --mcm-bg: #F4F6F9;
    --mcm-surface: #FFFFFF;
    --mcm-surface-soft: #F8FAFC;
    --mcm-surface-strong: #EFF2F7;
    --mcm-border: #D8E0EC;
    --mcm-border-strong: #B0BEC5;
    --mcm-text: #2B2B2B;
    --mcm-text-strong: #111111;
    --mcm-muted: #6B7280;
    --mcm-soft: #94A3B8;
    --mcm-accent: #1E5AA8;
    --mcm-accent-strong: #15427A;
    --mcm-accent-soft: rgba(30, 90, 168, 0.06);
    --mcm-green: #22C55E;
    --mcm-green-bg: #DCFCE7;
    --mcm-amber: #F59E0B;
    --mcm-amber-bg: #FEF3C7;
    --mcm-red: #EF4444;
    --mcm-red-bg: #FEE2E2;
    --mcm-blue: #1E5AA8;
    --mcm-blue-bg: #DBEAFE;
    --mcm-shadow-soft: 0 1px 3px rgba(15, 23, 42, 0.06);
    --mcm-shadow-hover: 0 4px 14px rgba(15, 23, 42, 0.08);
    --mcm-shadow-card: 0 10px 26px rgba(15, 23, 42, 0.06);
    --mcm-radius: 16px;
    --mcm-radius-sm: 10px;
    --mcm-radius-xs: 6px;
    width: min(100%, 86rem);
    margin-inline: auto;
    color: var(--mcm-text);
    font-variant-numeric: tabular-nums;
}

.dark .mcm-modern-page {
    --mcm-primary: #3B82F6;
    --mcm-primary-hover: #60A5FA;
    --mcm-primary-strong: #93C5FD;
    --mcm-primary-light: rgba(59, 130, 246, 0.15);
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
    --mcm-accent: #3B82F6;
    --mcm-accent-strong: #60A5FA;
    --mcm-accent-soft: rgba(59, 130, 246, 0.1);
    --mcm-green: #4ADE80;
    --mcm-green-bg: rgba(34, 197, 94, 0.15);
    --mcm-amber: #FBBF24;
    --mcm-amber-bg: rgba(245, 158, 11, 0.15);
    --mcm-red: #F87171;
    --mcm-red-bg: rgba(239, 68, 68, 0.15);
    --mcm-blue: #60A5FA;
    --mcm-blue-bg: rgba(59, 130, 246, 0.15);
    --mcm-shadow-soft: 0 1px 3px rgba(0, 0, 0, 0.2);
    --mcm-shadow-hover: 0 4px 14px rgba(0, 0, 0, 0.25);
    --mcm-shadow-card: 0 10px 26px rgba(0, 0, 0, 0.2);
}

body:has(.mcm-modern-page),
body:has(.mcm-modern-page) .fi-main,
body:has(.mcm-modern-page) .fi-page,
body:has(.mcm-modern-page) .fi-page-content {
    background: #F4F6F9;
}

.dark body:has(.mcm-modern-page),
.dark body:has(.mcm-modern-page) .fi-main,
.dark body:has(.mcm-modern-page) .fi-page,
.dark body:has(.mcm-modern-page) .fi-page-content {
    background: #0F172A;
}

body:has(.mcm-modern-page) .fi-header {
    display: none;
}

body:has(.mcm-modern-page) .fi-main {
    padding-block: 0.75rem 2rem;
}

/* ── Power BI Header ──────────────────────────────────────── */

.mcm-modern-page .pbi-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 0.85rem 1.5rem;
    background: linear-gradient(135deg, #1E5AA8 0%, #15427A 100%);
    border-radius: var(--mcm-radius-sm);
    color: #FFFFFF;
    flex-wrap: wrap;
    box-shadow: 0 4px 20px rgba(15, 42, 80, 0.25);
}

.dark .mcm-modern-page .pbi-header {
    background: linear-gradient(135deg, #1E3A5F 0%, #0F2A50 100%);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.mcm-modern-page .pbi-header-logo {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1;
    flex-shrink: 0;
}

.mcm-modern-page .pbi-header-logo small {
    display: block;
    font-size: 0.55rem;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    opacity: 0.7;
    margin-top: 2px;
}

.mcm-modern-page .pbi-header-divider {
    width: 1px;
    height: 2.5rem;
    background: rgba(255, 255, 255, 0.2);
    flex-shrink: 0;
}

.mcm-modern-page .pbi-header-kpi {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    min-width: 6rem;
}

.mcm-modern-page .pbi-header-kpi-value {
    font-size: 1.45rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.1;
}

.mcm-modern-page .pbi-header-kpi-label {
    font-size: 0.65rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    opacity: 0.75;
}

.mcm-modern-page .pbi-header-period {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: auto;
}

.mcm-modern-page .pbi-header-period label {
    font-size: 0.72rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    opacity: 0.75;
}

.mcm-modern-page .pbi-header-period select {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: var(--mcm-radius-xs);
    color: #FFFFFF;
    padding: 0.4rem 0.6rem;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    min-width: 8rem;
}

.mcm-modern-page .pbi-header-period select:hover {
    background: rgba(255, 255, 255, 0.22);
}

.mcm-modern-page .pbi-header-period select:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
}

.mcm-modern-page .pbi-header-period select option {
    background: #1E293B;
    color: #E2E8F0;
}

.mcm-modern-page .pbi-header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.mcm-modern-page .pbi-header-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0.45rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: var(--mcm-radius-xs);
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.12);
    color: #FFFFFF;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
}

.mcm-modern-page .pbi-header-btn:hover {
    background: rgba(255, 255, 255, 0.22);
    border-color: rgba(255, 255, 255, 0.45);
}

.mcm-modern-page .pbi-header-btn svg {
    width: 0.95rem;
    height: 0.95rem;
    flex-shrink: 0;
}

/* ── Hero (legacy compat) ────────────────────────────────── */

.mcm-modern-page .page-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 1.5rem 1.75rem;
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: var(--mcm-radius);
}

.mcm-modern-page .page-hero h1 {
    color: var(--mcm-text-strong);
    font-size: 1.75rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.15;
}

.mcm-modern-page .page-hero p,
.mcm-modern-page .kpi-sub,
.mcm-modern-page .filter-label {
    color: var(--mcm-muted);
    font-size: 0.82rem;
    line-height: 1.5;
}

/* ── Cards, Surfaces ───────────────────────────────────────── */

.mcm-modern-page .filter-bar,
.mcm-modern-page .chart-card,
.mcm-modern-page .kpi-card,
.mcm-modern-page .inbox-card,
.mcm-modern-page .bucket-section,
.mcm-modern-page .settings-card {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: var(--mcm-radius);
}

.mcm-modern-page .filter-bar,
.mcm-modern-page .chart-card,
.mcm-modern-page .settings-card {
    padding: 1.25rem 1.5rem;
}

/* ── Tabs (Power BI style) ─────────────────────────────────── */

.mcm-modern-page .pbi-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid var(--mcm-border);
    background: var(--mcm-surface);
    border-radius: var(--mcm-radius-sm) var(--mcm-radius-sm) 0 0;
    overflow: hidden;
}

.mcm-modern-page .pbi-tab {
    padding: 0.75rem 1.5rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--mcm-muted);
    cursor: pointer;
    border: none;
    background: transparent;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    white-space: nowrap;
}

.mcm-modern-page .pbi-tab:hover {
    color: var(--mcm-text-strong);
    background: var(--mcm-surface-soft);
}

.mcm-modern-page .pbi-tab.active {
    color: var(--mcm-primary);
    border-bottom-color: var(--mcm-primary);
    background: var(--mcm-primary-soft);
}

.mcm-modern-page .pbi-tab-panel {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-top: none;
    border-radius: 0 0 var(--mcm-radius-sm) var(--mcm-radius-sm);
    padding: 1.25rem 1.5rem;
}

/* ── Checkbox filters (Canal / Cliente tabs) ─────────────── */

.mcm-modern-page .pbi-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
    gap: 0.5rem;
}

.mcm-modern-page .pbi-filter-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.6rem;
    border-radius: var(--mcm-radius-xs);
    cursor: pointer;
    font-size: 0.8rem;
    color: var(--mcm-text);
}

.mcm-modern-page .pbi-filter-check:hover {
    background: var(--mcm-surface-strong);
}

.mcm-modern-page .pbi-filter-check input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    accent-color: var(--mcm-primary);
    cursor: pointer;
    flex-shrink: 0;
}

.mcm-modern-page .pbi-filter-search {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--mcm-border);
    border-radius: var(--mcm-radius-xs);
    background: var(--mcm-surface-soft);
    color: var(--mcm-text);
    font-size: 0.82rem;
    outline: none;
    margin-bottom: 0.75rem;
}

.mcm-modern-page .pbi-filter-search:focus {
    border-color: var(--mcm-primary);
    box-shadow: 0 0 0 2px var(--mcm-primary-soft);
}

.mcm-modern-page .pbi-select-all {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--mcm-primary);
    cursor: pointer;
    margin-bottom: 0.5rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

/* ── KPI Cards ─────────────────────────────────────────────── */

.mcm-modern-page .kpi-card {
    min-height: 7rem;
    padding: 1.15rem 1.35rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 0.2rem;
}

.mcm-modern-page .kpi-label,
.mcm-modern-page .dash-section-title,
.mcm-modern-page .section-title,
.mcm-modern-page .chart-title {
    color: var(--mcm-muted);
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.mcm-modern-page .kpi-value {
    color: var(--mcm-text-strong);
    font-size: 1.65rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.08;
    overflow-wrap: anywhere;
}

.mcm-modern-page .kpi-value.sm {
    font-size: 1.1rem;
}

.mcm-modern-page .kpi-value.text-red-500 { color: var(--mcm-red); }
.mcm-modern-page .kpi-value.text-red-400 { color: var(--mcm-red); }
.mcm-modern-page .kpi-value.text-amber-500 { color: var(--mcm-amber); }
.mcm-modern-page .kpi-value.text-emerald-500,
.mcm-modern-page .kpi-value.text-emerald-600 { color: var(--mcm-green); }
.mcm-modern-page .kpi-value.text-blue-600 { color: var(--mcm-blue); }
.dark .mcm-modern-page .kpi-value.text-blue-400 { color: var(--mcm-blue); }
.mcm-modern-page .kpi-value.text-gray-700 { color: var(--mcm-muted); }
.dark .mcm-modern-page .kpi-value.dark\:text-gray-300 { color: var(--mcm-muted); }
.mcm-modern-page .kpi-value.text-gray-300 { color: var(--mcm-soft); }

.mcm-modern-page .kpi-sub {
    font-size: 0.72rem;
    margin-top: 0.1rem;
    color: var(--mcm-muted);
}

/* ── Chart Cards ───────────────────────────────────────────── */

.mcm-modern-page .chart-title {
    color: var(--mcm-text-strong);
    font-size: 0.88rem;
    font-weight: 600;
    letter-spacing: 0;
    text-transform: none;
    margin-bottom: 0.75rem;
}

/* ── Filter Inputs ─────────────────────────────────────────── */

.mcm-modern-page .filter-input {
    width: 100%;
    min-height: 2.35rem;
    border: 1px solid var(--mcm-border);
    border-radius: var(--mcm-radius-xs);
    background: var(--mcm-surface);
    color: var(--mcm-text);
    padding: 0.4rem 0.7rem;
    font-size: 0.82rem;
    outline: none;
}

.mcm-modern-page .filter-input:focus {
    border-color: var(--mcm-primary);
    box-shadow: 0 0 0 2px var(--mcm-primary-soft);
}

.mcm-modern-page .filter-label {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
    color: var(--mcm-muted);
}

/* ── Buttons ───────────────────────────────────────────────── */

.mcm-modern-page .btn-primary,
.mcm-modern-page .btn-ghost,
.mcm-modern-page .tab-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    min-height: 2.35rem;
    padding: 0.5rem 0.9rem;
    font-size: 0.82rem;
    font-weight: 600;
    line-height: 1.1;
    white-space: nowrap;
    text-decoration: none;
    cursor: pointer;
    border-radius: var(--mcm-radius-xs);
}

.mcm-modern-page .btn-primary {
    color: #FFFFFF;
    background: var(--mcm-primary);
    border: 1px solid var(--mcm-primary);
}

.mcm-modern-page .btn-primary:hover {
    background: var(--mcm-primary-hover);
    border-color: var(--mcm-primary-hover);
}

.mcm-modern-page .btn-ghost,
.mcm-modern-page .tab-btn {
    color: var(--mcm-text);
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
}

.mcm-modern-page .btn-ghost:hover,
.mcm-modern-page .tab-btn:hover {
    background: var(--mcm-surface-soft);
    border-color: var(--mcm-border-strong);
}

.mcm-modern-page .tab-btn.active {
    color: #FFFFFF;
    background: var(--mcm-primary);
    border-color: var(--mcm-primary);
}

.mcm-modern-page .btn-primary svg,
.mcm-modern-page .btn-ghost svg,
.mcm-modern-page .tab-btn svg {
    width: 0.95rem;
    height: 0.95rem;
    flex: 0 0 0.95rem;
    stroke-width: 2;
}

.mcm-modern-page .btn-primary .btn-icon,
.mcm-modern-page .btn-ghost .btn-icon {
    display: inline-grid;
    width: 1.6rem;
    height: 1.6rem;
    place-items: center;
    border-radius: var(--mcm-radius-xs);
}

.mcm-modern-page .btn-primary .btn-icon {
    background: rgba(255, 255, 255, 0.18);
}

.mcm-modern-page .btn-ghost .btn-icon {
    background: var(--mcm-surface-strong);
}

.mcm-modern-page .btn-label {
    display: inline-flex;
    align-items: center;
}

.mcm-modern-page [wire\:loading] {
    display: none;
}

/* ── Badges ────────────────────────────────────────────────── */

.mcm-modern-page .badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.03em;
}

.mcm-modern-page .badge-green { background: var(--mcm-green-bg); color: var(--mcm-green); }
.mcm-modern-page .badge-amber { background: var(--mcm-amber-bg); color: var(--mcm-amber); }
.mcm-modern-page .badge-red { background: var(--mcm-red-bg); color: var(--mcm-red); }
.mcm-modern-page .badge-blue { background: var(--mcm-blue-bg); color: var(--mcm-blue); }
.mcm-modern-page .badge-gray { background: var(--mcm-surface-strong); color: var(--mcm-muted); }

/* ── Tables ────────────────────────────────────────────────── */

.mcm-modern-page .data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.82rem;
}

.mcm-modern-page .data-table th {
    padding: 0.6rem 0.85rem;
    text-align: left;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    color: var(--mcm-muted);
    background: var(--mcm-surface-soft);
    border-bottom: 1px solid var(--mcm-border);
}

.mcm-modern-page .data-table td {
    padding: 0.6rem 0.85rem;
    color: var(--mcm-text);
    border-bottom: 1px solid var(--mcm-border);
}

.mcm-modern-page .data-table tr:hover td {
    background: var(--mcm-surface-soft);
}

.mcm-modern-page .data-table tr:last-child td {
    border-bottom: none;
}

/* ── Money values ──────────────────────────────────────────── */

.mcm-modern-page .money-value {
    color: var(--mcm-text-strong);
    font-family: var(--font-mono, ui-monospace, SFMono-Regular, Menlo, Consolas, monospace);
    font-weight: 600;
    font-variant-numeric: tabular-nums;
}

/* ── Empty States ──────────────────────────────────────────── */

.mcm-modern-page .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.65rem;
    padding: 2.5rem;
    color: var(--mcm-muted);
    text-align: center;
}

/* ── Section Titles ────────────────────────────────────────── */

.mcm-modern-page .dash-section-title {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--mcm-muted);
    margin-bottom: 0.65rem;
}

/* ── Timeline ──────────────────────────────────────────────── */

.mcm-modern-page .timeline-dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 0.35rem;
}

/* ── Comparison Cards ──────────────────────────────────────── */

.mcm-modern-page .compare-card {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: var(--mcm-radius);
    padding: 1.25rem 1.5rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

/* ── Delta indicators ──────────────────────────────────────── */

.mcm-modern-page .delta-up   { color: var(--mcm-green); font-size: 0.72rem; font-weight: 700; }
.mcm-modern-page .delta-down { color: var(--mcm-red); font-size: 0.72rem; font-weight: 700; }
.mcm-modern-page .delta-flat { color: var(--mcm-muted); font-size: 0.72rem; }

/* ── Promise circles ───────────────────────────────────────── */

.mcm-modern-page .promise-circle {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
}

/* ── Score ring ────────────────────────────────────────────── */

.mcm-modern-page .score-ring-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.mcm-modern-page .score-card {
    min-height: 100%;
}

.mcm-modern-page .score-card svg {
    width: 8rem;
    height: 8rem;
}

.mcm-modern-page .score-card circle:first-child {
    stroke: var(--mcm-surface-strong);
}

.mcm-modern-page .score-card text:last-child {
    fill: var(--mcm-muted);
}

/* ── Catalog Pills ─────────────────────────────────────────── */

.mcm-modern-page .catalog-pill {
    border-radius: var(--mcm-radius-xs);
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    color: var(--mcm-text);
}

/* ── Clases de reveal (sin animación) ──────────────────────── */

.mcm-reveal, .mcm-stagger > * { opacity: 1; }

/* ── Responsive ────────────────────────────────────────────── */

@media (max-width: 760px) {
    .mcm-modern-page .page-hero {
        align-items: stretch;
        flex-direction: column;
        padding: 1rem;
    }
    .mcm-modern-page .page-hero h1 { font-size: 1.35rem; }
    .mcm-modern-page .btn-primary,
    .mcm-modern-page .btn-ghost { width: 100%; }
    .mcm-modern-page .compare-card { grid-template-columns: 1fr; }

    .mcm-modern-page .pbi-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
    }
    .mcm-modern-page .pbi-header-divider { display: none; }
    .mcm-modern-page .pbi-header-period { margin-left: 0; width: 100%; }
    .mcm-modern-page .pbi-header-period select { flex: 1; }
    .mcm-modern-page .pbi-header-actions { width: 100%; flex-wrap: wrap; }
    .mcm-modern-page .pbi-header-actions .pbi-header-btn { flex: 1; }
    .mcm-modern-page .pbi-tabs { overflow-x: auto; }
    .mcm-modern-page .pbi-filter-grid { grid-template-columns: 1fr; }
}
</style>
@endpush
@endonce
