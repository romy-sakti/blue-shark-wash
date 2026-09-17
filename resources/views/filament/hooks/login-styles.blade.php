<style>
    .fi-simple-layout {
        background:
            radial-gradient(ellipse 80% 50% at 50% -15%, rgba(37, 99, 235, 0.55), transparent 58%),
            radial-gradient(ellipse 45% 40% at 100% 100%, rgba(8, 47, 123, 0.7), transparent 55%),
            radial-gradient(ellipse 40% 35% at 0% 85%, rgba(14, 116, 184, 0.28), transparent 50%),
            linear-gradient(180deg, #061428 0%, #0a2a5c 48%, #071526 100%);
    }

    .fi-simple-layout .fi-simple-main-ctn {
        padding: 1.5rem 1rem 2.5rem;
    }

    .fi-simple-layout .fi-simple-main {
        background: #fff !important;
        border-radius: 1.35rem !important;
        border: 1px solid rgba(255, 255, 255, 0.18) !important;
        box-shadow:
            0 24px 60px rgba(2, 8, 23, 0.45),
            0 0 0 1px rgba(30, 95, 204, 0.12);
        transition: box-shadow 200ms ease, transform 200ms ease;
    }

    .fi-simple-layout .fi-simple-header .fi-logo {
        height: auto !important;
        width: auto;
        display: flex;
        justify-content: center;
    }

    .fi-simple-layout .brand-mark-copy {
        display: none;
    }

    .fi-simple-layout .brand-mark-logo {
        height: 9rem;
        width: 9rem;
        object-fit: cover;
        border-radius: 50%;
        background: #fff;
        box-shadow:
            0 10px 28px rgba(2, 8, 23, 0.35),
            0 0 0 4px rgba(255, 255, 255, 0.16);
        transition: transform 200ms ease, box-shadow 200ms ease;
    }

    .fi-simple-layout .brand-mark-logo:hover {
        transform: scale(1.03);
        box-shadow:
            0 14px 32px rgba(2, 8, 23, 0.4),
            0 0 0 5px rgba(255, 255, 255, 0.22);
    }

    .fi-simple-layout .fi-simple-header-heading {
        color: #0b1b3a !important;
        letter-spacing: -0.02em;
    }

    .fi-simple-layout .fi-simple-header-subheading {
        color: #5b6b85 !important;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-size: 0.7rem !important;
        font-weight: 600;
    }

    .fi-simple-layout .fi-btn.fi-color-primary {
        background-image: linear-gradient(180deg, #2b7fff 0%, #1a4fd8 100%) !important;
        box-shadow: 0 8px 18px rgba(26, 79, 216, 0.32);
        transition: filter 160ms ease, transform 160ms ease, box-shadow 160ms ease;
    }

    .fi-simple-layout .fi-btn.fi-color-primary:hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(26, 79, 216, 0.4);
    }

    .fi-simple-layout .fi-btn.fi-color-primary:focus-visible {
        outline: 2px solid #1a4fd8;
        outline-offset: 3px;
    }

    .login-brand-foot {
        margin: 0 0 2rem;
        text-align: center;
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(226, 232, 240, 0.72);
    }

    .fi-sidebar-header {
        height: auto !important;
        min-height: 4.5rem;
        padding-top: 0.7rem;
        padding-bottom: 0.7rem;
    }

    .fi-sidebar-header .fi-logo {
        height: auto !important;
        width: auto;
        max-width: 100%;
    }

    .brand-mark {
        display: inline-flex;
        align-items: center;
        gap: 0.7rem;
        min-width: 0;
    }

    .brand-mark-logo {
        display: block;
        height: 2.5rem;
        width: 2.5rem;
        flex-shrink: 0;
        object-fit: cover;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.08);
    }

    .brand-mark-copy {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 0.1rem;
    }

    .brand-mark-name {
        font-size: 0.92rem;
        font-weight: 700;
        line-height: 1.2;
        color: #0b1b3a;
        letter-spacing: -0.02em;
    }

    .brand-mark-tag {
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
    }

    .dark .brand-mark-name {
        color: #f8fafc;
    }

    .dark .brand-mark-tag {
        color: #94a3b8;
    }

    .admin-topbar-info {
        display: flex;
        min-width: 0;
        flex: 1;
        flex-direction: column;
        justify-content: center;
        gap: 0.05rem;
    }

    .admin-topbar-kicker {
        margin: 0;
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #0f766e;
    }

    .admin-topbar-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.2;
        color: #0b1b3a;
    }

    .admin-topbar-meta {
        margin: 0;
        font-size: 0.75rem;
        color: #64748b;
    }

    .dark .admin-topbar-title {
        color: #f8fafc;
    }

    .admin-app-footer {
        margin: 0 1rem 1.25rem;
        padding: 0.9rem 1.1rem;
        border-top: 1px solid rgb(15 23 42 / 0.08);
        color: #475569;
        font-size: 0.8rem;
        line-height: 1.45;
    }

    @media (min-width: 768px) {
        .admin-app-footer {
            margin-left: 1.5rem;
            margin-right: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
    }

    @media (min-width: 1024px) {
        .admin-app-footer {
            margin-left: 2rem;
            margin-right: 2rem;
        }
    }

    .admin-app-footer-main {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem 0.55rem;
        color: #0b1b3a;
    }

    .dark .admin-app-footer,
    .dark .admin-app-footer-main {
        color: #cbd5e1;
        border-top-color: rgb(255 255 255 / 0.1);
    }

    .admin-app-footer-meta {
        color: #64748b;
    }

    .vehicle-overview {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .vehicle-overview-head {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        padding-inline: 0.1rem;
    }

    .vehicle-overview-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #0b1b3a;
    }

    .dark .vehicle-overview-title {
        color: #f8fafc;
    }

    .vehicle-overview-hint {
        margin: 0;
        font-size: 0.8rem;
        color: #64748b;
    }

    .vehicle-overview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
        gap: 1.5rem;
    }

    .report-kpi-accent {
        background: #0f766e;
        border-color: #0d5f58;
        color: #fff;
    }

    .report-kpi-accent:hover {
        border-color: #0b4f49;
        box-shadow: 0 4px 12px rgb(15 118 110 / 0.28);
    }

    .report-kpi-accent .report-kpi-label,
    .report-kpi-accent .report-kpi-hint {
        color: rgb(204 251 241);
    }

    .report-kpi-accent .report-kpi-value {
        color: #fff;
    }

    @media (prefers-reduced-motion: reduce) {
        .fi-simple-layout .fi-simple-header .fi-logo,
        .fi-simple-layout .fi-btn.fi-color-primary,
        .fi-simple-layout .brand-mark-logo {
            transition: none;
        }

        .fi-simple-layout .fi-simple-header .fi-logo:hover,
        .fi-simple-layout .fi-btn.fi-color-primary:hover,
        .fi-simple-layout .brand-mark-logo:hover {
            transform: none;
        }
    }

    @media print {
        .fi-simple-layout {
            background: #fff !important;
        }
    }
</style>
