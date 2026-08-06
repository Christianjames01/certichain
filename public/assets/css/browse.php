/* browse.php — certificate catalog inside the student dashboard shell.
   Reuses .catalog-grid / .cert-tile tokens from index.css but restyles
   the search bar and section headers to sit inside .dash-content. */

.dash-content .catalog-search {
    margin: 4px 0 8px;
}

.dash-content .catalog-search input {
    width: 100%;
    max-width: 100%;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid var(--line);
    font-family: var(--font-body);
    font-size: 14px;
    background: var(--white);
}

.dash-content .catalog-search input:focus {
    outline: 2px solid var(--hcdc-gold);
    outline-offset: 1px;
}

.dash-content .catalog-section {
    margin-bottom: 8px;
}

.dash-content .catalog-cat-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 36px 0 14px;
}

.dash-content .catalog-cat-title:first-child {
    margin-top: 28px;
}

.dash-content .catalog-cat-title .category-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: var(--parchment-dim);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.dash-content .catalog-cat-title .category-icon i {
    color: var(--hcdc-navy);
    font-size: 16px;
}

.dash-content .catalog-cat-title h2 {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 600;
    color: var(--hcdc-navy);
    margin: 0;
}

.dash-content .catalog-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}

.dash-content .cert-tile {
    display: block;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: 16px 18px;
    transition: border-color .15s, transform .15s;
}

.dash-content .cert-tile:hover {
    border-color: var(--hcdc-gold);
    transform: translateY(-2px);
}

.dash-content .cert-tile .name {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 6px;
    line-height: 1.35;
}

.dash-content .cert-tile .desc {
    font-size: 12.5px;
    color: var(--ink-soft);
    line-height: 1.5;
}

.dash-content .cert-tile .cta {
    margin-top: 10px;
    font-size: 12px;
    font-weight: 600;
    color: var(--hcdc-red);
}

.dash-content .link-muted {
    color: var(--hcdc-red);
    font-weight: 600;
    border-bottom: 1px solid transparent;
}

.dash-content .link-muted:hover {
    border-bottom-color: var(--hcdc-red);
}

@media (max-width: 700px) {
    .dash-content .catalog-grid {
        grid-template-columns: 1fr;
    }
}