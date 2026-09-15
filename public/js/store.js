/**
 * Pulse — Marketplace Storefront
 * Store layer (TASK-014): quote list, drawer, contact prefill, shelf scroll.
 *
 * This is INTERACTION, not motion, so unlike app.js it also runs under
 * prefers-reduced-motion. Design rules:
 *  - The quote list is a per-visitor convenience kept in localStorage.
 *    It is NOT a cart: nothing is priced or bought; its only output is a
 *    prefilled message + package on the existing /contact form (ADR-013:
 *    the storefront stays stateless — nothing new reaches the server).
 *  - Storage can be missing or throw (private mode, blocked site data);
 *    every access is guarded and an in-memory list takes over.
 *  - Every control that needs JS ships `hidden` and is revealed here, so a
 *    no-JS visitor never meets a button that does nothing.
 *  - Item labels come from the server-rendered #quote-catalog JSON (the
 *    existing lang copy); DOM is built with textContent only.
 */
const catalogEl = document.getElementById('quote-catalog');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const STORAGE_KEY = 'pulse.quote.v1';

let catalog = null;
try {
    catalog = catalogEl ? JSON.parse(catalogEl.textContent) : null;
} catch (e) {
    catalog = null;
}

/* ------------------------------------------------------------------ */
/*  Persistence (guarded)                                              */
/* ------------------------------------------------------------------ */
let memoryList = [];

const readList = () => {
    let list = memoryList;
    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);
        if (raw !== null) {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) list = parsed;
        }
    } catch (e) { /* storage unavailable — keep the in-memory list */ }
    // Drop anything the current catalog no longer knows about.
    return list.filter((key) => catalog && Object.prototype.hasOwnProperty.call(catalog.items, key));
};

const writeList = (list) => {
    memoryList = list;
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    } catch (e) { /* storage unavailable — memory only */ }
};

/* ------------------------------------------------------------------ */
/*  Rendering                                                          */
/* ------------------------------------------------------------------ */
const packagesIn = (list) => list.filter((key) => catalog.items[key].group === 'pkg');

const buildItem = (key) => {
    const item = catalog.items[key];
    const li = document.createElement('li');
    li.className = 'quote-item';

    const group = document.createElement('span');
    group.className = 'quote-item-group';
    group.textContent = catalog.groups[item.group] || '';

    const title = document.createElement('span');
    title.className = 'quote-item-title';
    title.textContent = item.title;

    const meta = document.createElement('span');
    meta.className = 'quote-item-meta';
    meta.textContent = item.meta;

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'link-btn quote-item-remove';
    remove.dataset.quoteRemove = key;
    remove.textContent = catalog.remove;
    remove.setAttribute('aria-label', `${catalog.remove}: ${item.title}`);

    li.append(group, title, meta, remove);
    return li;
};

const render = () => {
    const list = readList();
    const count = list.length;

    document.querySelectorAll('[data-quote-count]').forEach((el) => {
        el.hidden = count === 0;
        if (el.firstChild && el.firstChild.nodeType === Node.TEXT_NODE) {
            el.firstChild.nodeValue = String(count);
        }
    });

    document.querySelectorAll('[data-quote-add]').forEach((btn) => {
        const on = list.includes(btn.dataset.quoteAdd);
        btn.hidden = false;
        btn.setAttribute('aria-pressed', String(on));
        const label = btn.querySelector('[data-quote-add-label]');
        if (label) label.textContent = on ? catalog.added : catalog.add;
    });

    document.querySelectorAll('[data-quote-list]').forEach((ul) => {
        ul.replaceChildren(...list.map(buildItem));
    });
    document.querySelectorAll('[data-quote-empty]').forEach((el) => {
        el.hidden = count > 0;
    });
    document.querySelectorAll('[data-quote-summary]').forEach((el) => {
        el.hidden = false;
    });

    // One package in the list → the drawer CTA preselects it on /contact.
    const pkgs = packagesIn(list);
    document.querySelectorAll('[data-quote-cta]').forEach((a) => {
        const url = new URL(a.href, window.location.href);
        if (pkgs.length === 1) {
            url.searchParams.set('tier', catalog.items[pkgs[0]].tier);
        } else {
            url.searchParams.delete('tier');
        }
        a.href = url.toString();
    });
};

const bump = () => {
    if (reducedMotion) return;
    document.querySelectorAll('[data-quote-count]').forEach((el) => {
        el.classList.remove('is-bump');
        void el.offsetWidth; // restart the keyframes
        el.classList.add('is-bump');
    });
};

/* ------------------------------------------------------------------ */
/*  Quote list behaviour                                               */
/* ------------------------------------------------------------------ */
function setupQuoteList() {
    const drawer = document.getElementById('quote-drawer');
    const canModal = drawer && typeof drawer.showModal === 'function';

    document.addEventListener('click', (event) => {
        const add = event.target.closest('[data-quote-add]');
        if (add) {
            const key = add.dataset.quoteAdd;
            const list = readList();
            const next = list.includes(key) ? list.filter((k) => k !== key) : [...list, key];
            writeList(next);
            render();
            if (next.length > list.length) bump();
            return;
        }

        const remove = event.target.closest('[data-quote-remove]');
        if (remove) {
            writeList(readList().filter((k) => k !== remove.dataset.quoteRemove));
            render();
            return;
        }

        if (event.target.closest('[data-quote-clear]')) {
            writeList([]);
            render();
            return;
        }

        const open = event.target.closest('[data-quote-open]');
        if (open && canModal) {
            event.preventDefault();
            const menu = open.closest('details');
            if (menu) menu.open = false;
            drawer.showModal();
            return;
        }

        if (event.target.closest('[data-quote-close]') && drawer) {
            drawer.close();
            return;
        }

        // A click on the ::backdrop lands on the dialog element itself.
        if (drawer && event.target === drawer) drawer.close();
    });

    // A package picker (product buy box) retargets its add button.
    document.querySelectorAll('[data-quote-follow]').forEach((btn) => {
        const form = btn.closest('form');
        if (!form) return;
        const sync = () => {
            const checked = form.querySelector(`input[name="${btn.dataset.quoteFollow}"]:checked`);
            if (checked) btn.dataset.quoteAdd = `pkg:${checked.value}`;
            render();
        };
        form.addEventListener('change', sync);
        sync();
    });

    // Another tab changed the list.
    window.addEventListener('storage', (event) => {
        if (event.key === STORAGE_KEY) render();
    });

    render();
}

/* ------------------------------------------------------------------ */
/*  /contact: carry the list into the existing form                   */
/* ------------------------------------------------------------------ */
function prefillContact() {
    const message = document.querySelector('[data-quote-message]');
    if (!message) return;
    const list = readList();
    if (list.length === 0) return;

    if (message.value.trim() === '') {
        const lines = list.map((key) => {
            const item = catalog.items[key];
            return `- ${catalog.groups[item.group]}: ${item.title} (${item.meta})`;
        });
        message.value = `${catalog.prefix}\n${lines.join('\n')}\n\n`;
    }

    const tier = document.getElementById('tier');
    const pkgs = packagesIn(list);
    if (tier && tier.value === '' && pkgs.length === 1) {
        tier.value = catalog.items[pkgs[0]].tier;
    }
}

/* ------------------------------------------------------------------ */
/*  Horizontal shelves: prev/next buttons                              */
/* ------------------------------------------------------------------ */
function setupShelves() {
    document.querySelectorAll('[data-shelf-prev], [data-shelf-next]').forEach((btn) => {
        const target = document.getElementById(btn.dataset.shelfPrev || btn.dataset.shelfNext);
        if (!target) return;
        btn.hidden = false;
        const dir = btn.dataset.shelfPrev ? -1 : 1;
        btn.addEventListener('click', () => {
            target.scrollBy({
                left: dir * target.clientWidth * 0.8,
                behavior: reducedMotion ? 'auto' : 'smooth',
            });
        });
    });
}

if (catalog) {
    setupQuoteList();
    prefillContact();
}
setupShelves();
