#!/usr/bin/env python3
"""WCAG contrast audit for the redesigned token system (TASK-012).
Checks every text/background pairing the new CSS introduces.

TASK-013 (Datum, ADR-015): PAIRS re-pinned to the light sage M3 token
set. Same audit contract, new palette."""

def lin(c):
    c = c / 255
    return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4

def lum(hexc):
    hexc = hexc.lstrip('#')
    r, g, b = (int(hexc[i:i+2], 16) for i in (0, 2, 4))
    return 0.2126 * lin(r) + 0.7152 * lin(g) + 0.0722 * lin(b)

def ratio(fg, bg):
    l1, l2 = lum(fg), lum(bg)
    hi, lo = max(l1, l2), min(l1, l2)
    return (hi + 0.05) / (lo + 0.05)

# name: (foreground, background, context, required ratio)
PAIRS = [
    # body + headings on base grounds (Datum M3: #f6fbed ground)
    ("on-surface on background (h1/h2 headings)",     "#181d15", "#f6fbed", 3.0),
    ("on-background on background (body)",            "#181d15", "#f6fbed", 4.5),
    ("on-surface-variant on background (muted)",      "#454743", "#f6fbed", 4.5),
    ("outline-ink on background (meta/hints)",         "#666861", "#f6fbed", 4.5),
    ("on-background on container-low (wash)",         "#181d15", "#f1f5e7", 4.5),
    ("on-surface-variant on container-low",           "#454743", "#f1f5e7", 4.5),
    ("on-background on white (cards)",                "#181d15", "#ffffff", 4.5),
    ("on-surface-variant on white (card body)",       "#454743", "#ffffff", 4.5),
    ("outline-ink on white (meta only)",              "#666861", "#ffffff", 4.5),
    ("on-surface-variant on container-high (demos)",  "#454743", "#e5eadc", 4.5),
    # accents as text
    ("data-strong on background (links/labels)",      "#2f5238", "#f6fbed", 4.5),
    ("data-strong on white (event labels)",           "#2f5238", "#ffffff", 4.5),
    ("data on white (mono values)",                   "#416e4a", "#ffffff", 4.5),
    ("on-tertiary-fixed-variant on bg (gold ink)",    "#594400", "#f6fbed", 4.5),
    ("on-tertiary-fixed-variant on white",            "#594400", "#ffffff", 4.5),
    ("on-secondary-container on secondary-container", "#636562", "#e2e3df", 4.5),
    # buttons
    ("white on primary (primary btn)",                "#ffffff", "#0e0f0e", 4.5),
    ("white on primary-container (primary hover)",    "#ffffff", "#242423", 4.5),
    ("on-surface on background (quiet btn)",          "#181d15", "#f6fbed", 4.5),
    ("on-tertiary-fixed on tertiary-fixed (gold btn)",  "#241a00", "#ffdf93", 4.5),
    ("on-tertiary-fixed on tertiary-fixed-dim (hover)","#241a00", "#ebc254", 4.5),
    # alert / error
    ("on-error-container on error-container (errors)", "#93000a", "#ffdad6", 4.5),
    ("error on white (invalid border, non-text)",     "#ba1a1a", "#ffffff", 3.0),
    # code chip / snippet (dark on light)
    ("on-primary-fixed on primary-fixed (snippet)",   "#1b1c1b", "#e5e2e0", 4.5),
    ("on-surface on primary-fixed (snippet b)",       "#181d15", "#e5e2e0", 4.5),
    ("data-strong on primary-fixed (snippet .s)",     "#2f5238", "#e5e2e0", 4.5),
    # sample-event (dark panel, product page)
    ("tertiary-fixed on primary-container (title)",   "#ffdf93", "#242423", 4.5),
    ("#dcdcd9 on primary-container (code body)",      "#dcdcd9", "#242423", 4.5),
    # selection
    ("on-tertiary-fixed on tertiary-fixed (sel)",     "#241a00", "#ffdf93", 4.5),
    # inverse surfaces (closing band + footer + featured tier)
    ("inverse-on-surface on inverse-surface (h2)",    "#eef3e5", "#2d3229", 3.0),
    ("inverse-on-surface on inverse-surface (body)",  "#eef3e5", "#2d3229", 4.5),
    ("#b8bdb0 on inverse-surface (soft body .72)",    "#b8bdb0", "#2d3229", 4.5),
    ("#acb1a5 on inverse-surface (footer-note .66)",  "#acb1a5", "#2d3229", 4.5),
    ("#979c90 on inverse-surface (footer-legal .55)", "#979c90", "#2d3229", 4.5),
    ("#d3d8cb on inverse-surface (links .86)",        "#d3d8cb", "#2d3229", 4.5),
    ("tertiary-fixed on inverse-surface (gold em)",   "#ffdf93", "#2d3229", 4.5),
    ("tertiary-fixed on primary (featured tier)",     "#ffdf93", "#0e0f0e", 4.5),
    ("#d3d8cb on primary (tier feats .88)",           "#d3d8cb", "#0e0f0e", 4.5),
    ("tertiary-fixed on primary (ledger card id)",    "#ffdf93", "#0e0f0e", 4.5),
]

fails = []
for name, fg, bg, need in PAIRS:
    r = ratio(fg, bg)
    ok = r >= need
    print(f"{'PASS' if ok else 'FAIL'}  {r:5.2f}:1 (need {need})  {name}")
    if not ok:
        fails.append((name, r, need))

print()
if fails:
    print(f"{len(fails)} FAILING PAIRS — adjust before shipping")
else:
    print("ALL PAIRS PASS")
