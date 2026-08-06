/* ===================================================================
   Employee dashboard — companion to student-dashboard.css
   (which already supplies .app-shell, .sidebar, .side-nav,
   .chain-status, .topbar, .dash-card, .req-table, .badge, etc.)
   Only the employee-specific bits live here.
   =================================================================== */

/* Assigned-program badge, sits under the sidebar brand */
.program-badge {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 12px 12px;
    margin: 0 0 6px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 8px;
}

.program-badge-label {
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.5);
}

.program-badge-name {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--white);
    display: flex;
    align-items: baseline;
    gap: 8px;
    flex-wrap: wrap;
}

.program-badge-code {
    font-family: var(--font-mono);
    font-size: 11px;
    font-weight: 500;
    color: var(--hcdc-gold-light);
}

/* Small count pill next to a card heading */
.req-count-pill {
    font-family: var(--font-mono);
    font-size: 11.5px;
    color: var(--ink-soft);
    background: var(--parchment-dim);
    border: 1px solid var(--line);
    padding: 4px 10px;
    border-radius: 999px;
    white-space: nowrap;
}

/* Search box above the full requests table */
.req-search {
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 6px;
    background: var(--parchment);
}

.req-search i {
    color: var(--ink-soft);
    font-size: 15px;
}

.req-search input {
    border: none;
    background: none;
    outline: none;
    font-family: var(--font-body);
    font-size: 13.5px;
    color: var(--ink);
    width: 100%;
}

.req-search input::placeholder {
    color: var(--ink-soft);
}