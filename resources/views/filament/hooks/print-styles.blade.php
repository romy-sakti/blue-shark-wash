<style>
    .report-sheet {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .report-print-header,
    .report-print-footer {
        display: none;
    }

    .report-kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(11.5rem, 1fr));
        gap: 0.75rem;
    }

    .report-kpi {
        border-radius: 0.75rem;
        background: #fff;
        padding: 1rem;
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.05);
        border: 1px solid rgb(15 23 42 / 0.08);
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }

    .report-kpi:hover {
        border-color: rgb(15 118 110 / 0.35);
        box-shadow: 0 4px 12px rgb(15 23 42 / 0.06);
    }

    .dark .report-kpi {
        background: rgb(15 23 42);
        border-color: rgb(255 255 255 / 0.1);
    }

    .report-kpi-label {
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgb(100 116 139);
    }

    .report-kpi-value {
        margin-top: 0.25rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: rgb(2 6 23);
        font-variant-numeric: tabular-nums;
    }

    .dark .report-kpi-value {
        color: #fff;
    }

    .report-kpi-hint {
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: rgb(100 116 139);
    }

    .report-table-wrap {
        overflow: hidden;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.05);
        border: 1px solid rgb(15 23 42 / 0.08);
    }

    .dark .report-table-wrap {
        background: rgb(15 23 42);
        border-color: rgb(255 255 255 / 0.1);
    }

    .report-table {
        width: 100%;
        font-size: 0.875rem;
        border-collapse: collapse;
    }

    .report-table thead {
        background: rgb(248 250 252);
        text-align: left;
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgb(100 116 139);
    }

    .dark .report-table thead {
        background: rgb(30 41 59);
    }

    .report-table th,
    .report-table td {
        padding: 0.65rem 0.9rem;
        vertical-align: middle;
    }

    .report-table tbody tr {
        border-top: 1px solid rgb(241 245 249);
        transition: background-color 150ms ease;
    }

    .dark .report-table tbody tr {
        border-top-color: rgb(30 41 59);
    }

    .report-table tbody tr:hover {
        background: rgb(248 250 252);
    }

    .dark .report-table tbody tr:hover {
        background: rgb(30 41 59 / 0.5);
    }

    .report-table .report-num {
        width: 1%;
        white-space: nowrap;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .report-panel {
        border-radius: 0.75rem;
        background: #fff;
        padding: 1.25rem;
        border: 1px solid rgb(15 23 42 / 0.08);
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.05);
    }

    .dark .report-panel {
        background: rgb(15 23 42);
        border-color: rgb(255 255 255 / 0.1);
    }

    .report-section-label {
        margin: 0;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgb(100 116 139);
    }

    .report-dl {
        margin: 0.5rem 0 0;
        font-size: 0.875rem;
    }

    .report-dl > div {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.35rem 0;
    }

    .report-dl dd {
        margin: 0;
        font-weight: 500;
        font-variant-numeric: tabular-nums;
    }

    .report-dl-total {
        margin-top: 0.35rem;
        padding-top: 0.5rem !important;
        border-top: 1px solid rgb(226 232 240);
        font-weight: 600;
    }

    .dark .report-dl-total {
        border-top-color: rgb(51 65 85);
    }

    .report-hint {
        margin: 0.5rem 0 0;
        font-size: 0.75rem;
        line-height: 1.45;
        color: #64748b;
    }

    .report-callout {
        border-radius: 0.75rem;
        border: 1px solid rgb(15 118 110 / 0.28);
        background: #f0fdfa;
        padding: 0.9rem 1.1rem;
    }

    .dark .report-callout {
        background: rgb(15 118 110 / 0.12);
        border-color: rgb(45 212 191 / 0.28);
    }

    .report-callout-kicker {
        margin: 0;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #0f766e;
    }

    .report-callout-formula {
        margin: 0.3rem 0 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.4;
    }

    .dark .report-callout-formula {
        color: #f8fafc;
    }

    .report-highlight {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        border-radius: 0.75rem;
        background: #0f766e;
        color: #fff;
        padding: 1rem 1.15rem;
        font-weight: 600;
        transition: filter 150ms ease;
    }

    .report-highlight-kicker {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.92;
    }

    .report-highlight-sub {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.8rem;
        font-weight: 500;
        opacity: 0.9;
    }

    .report-highlight-value {
        font-size: 1.5rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .report-highlight:hover {
        filter: brightness(1.05);
    }

    @media print {
        @page {
            margin: 12mm 10mm 14mm;
        }

        html,
        body {
            background: #fff !important;
            color: #0f172a !important;
        }

        .fi-sidebar,
        .fi-main-sidebar,
        .fi-sidebar-close-overlay,
        .fi-topbar,
        .fi-header,
        .fi-breadcrumbs,
        .fi-global-search-ctn,
        .report-print-hide,
        .admin-app-footer,
        .admin-topbar-info {
            display: none !important;
        }

        .fi-body,
        .fi-layout,
        .fi-main-ctn,
        .fi-main,
        .fi-page,
        .fi-page > section {
            display: block !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
            overflow: visible !important;
            height: auto !important;
        }

        .report-print-header {
            display: block !important;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #0f766e;
            text-align: left;
        }

        .report-print-brand {
            margin: 0;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #0f766e !important;
        }

        .report-print-header h1 {
            margin: 0.2rem 0 0;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a !important;
        }

        .report-print-period,
        .report-print-meta {
            margin: 0.15rem 0 0;
            font-size: 11px;
            color: #475569 !important;
        }

        .report-print-footer {
            display: block !important;
            margin-top: 1.25rem;
            padding-top: 0.5rem;
            border-top: 1px solid #cbd5e1;
            font-size: 10px;
            color: #64748b !important;
        }

        .report-sheet,
        .report-sheet * {
            color-adjust: exact !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            box-shadow: none !important;
        }

        .report-table-wrap {
            overflow: visible !important;
        }

        .report-dl,
        .report-dl dt,
        .report-dl dd,
        .report-section-label {
            color: #0f172a !important;
        }

        .report-dl-total {
            border-top-color: #cbd5e1 !important;
        }

        .fi-notifications {
            display: none !important;
        }

        .report-kpis {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important;
            gap: 8px !important;
            margin-bottom: 12px !important;
        }

        .report-kpi,
        .report-panel,
        .report-table-wrap {
            background: #fff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            break-inside: avoid;
        }

        .report-kpi-value,
        .report-panel,
        .report-table {
            color: #0f172a !important;
        }

        .report-table thead {
            background: #f1f5f9 !important;
            color: #475569 !important;
        }

        .report-table thead {
            display: table-header-group;
        }

        .report-table tbody tr {
            break-inside: avoid;
            background: #fff !important;
            border-top-color: #e2e8f0 !important;
        }

        .report-table tbody tr:hover {
            background: #fff !important;
        }

        .report-callout,
        .report-highlight {
            break-inside: avoid;
        }

        .report-callout {
            background: #fff !important;
            border: 1px solid #0f766e !important;
        }

        .report-highlight {
            background: #fff !important;
            color: #0f766e !important;
            border: 2px solid #0f766e !important;
        }

        .report-highlight-value {
            color: #0f766e !important;
        }

        .report-screen-only {
            display: none !important;
        }
    }

    .audit-values {
        display: grid;
        gap: 0;
        margin: 0;
        border: 1px solid rgb(15 23 42 / 0.08);
        border-radius: 0.75rem;
        overflow: hidden;
        background: #fff;
    }

    .dark .audit-values {
        background: rgb(15 23 42);
        border-color: rgb(255 255 255 / 0.1);
    }

    .audit-values-row {
        display: grid;
        grid-template-columns: minmax(8rem, 12rem) 1fr;
        gap: 1rem;
        padding: 0.65rem 0.9rem;
        border-bottom: 1px solid rgb(15 23 42 / 0.06);
        transition: background-color 150ms ease;
    }

    .audit-values-row:last-child {
        border-bottom: 0;
    }

    .audit-values-row:hover {
        background: rgb(15 118 110 / 0.04);
    }

    .audit-values-row dt {
        margin: 0;
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: rgb(100 116 139);
    }

    .audit-values-row dd {
        margin: 0;
        font-size: 0.875rem;
        color: rgb(15 23 42);
        font-variant-numeric: tabular-nums;
    }

    .dark .audit-values-row dd {
        color: #fff;
    }
</style>
