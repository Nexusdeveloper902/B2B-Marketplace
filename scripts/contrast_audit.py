#!/usr/bin/env python3
"""WCAG contrast audit for the redesigned token system (TASK-012).
Checks every text/background pairing the new CSS introduces."""

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
    # body + headings on base grounds
    ("grey-50 on grey-950 (h1/h2 headings)",       "#f3f1f3", "#121013", 3.0),
    ("grey-100 on grey-950 (body)",                "#e6e3e8", "#121013", 4.5),
    ("grey-200 on grey-950 (strong body)",         "#cec7d1", "#121013", 4.5),
    ("grey-300 on grey-950 (muted small)",         "#b5acb9", "#121013", 4.5),
    ("grey-400 on grey-950 (muted/meta)",          "#9c90a2", "#121013", 4.5),
    ("grey-100 on grey-900 (body on raised)",      "#e6e3e8", "#1a171c", 4.5),
    ("grey-200 on grey-900",                       "#cec7d1", "#1a171c", 4.5),
    ("grey-300 on grey-900",                       "#b5acb9", "#1a171c", 4.5),
    ("grey-100 on grey-800 (body on surface)",     "#e6e3e8", "#352e38", 4.5),
    ("grey-300 on grey-800 (muted on surface)",    "#b5acb9", "#352e38", 4.5),
    ("grey-400 on grey-800 (meta on surface)",     "#9c90a2", "#352e38", 4.5),
    # accents as text
    ("scarlet-400 on grey-950 (small accent)",     "#f53d56", "#121013", 4.5),
    ("scarlet-500 on grey-950 (LARGE accent only)","#f20d2b", "#121013", 3.0),
    ("scarlet-300 on grey-800 (accent on surface)","#f76e80", "#352e38", 4.5),
    ("scarlet-400 on grey-900",                    "#f53d56", "#1a171c", 4.5),
    ("teal-400 on grey-950",                       "#80b3a7", "#121013", 4.5),
    ("teal-500 on grey-950",                       "#609f92", "#121013", 4.5),
    ("teal-400 on grey-800",                       "#80b3a7", "#352e38", 4.5),
    ("teal-500 on grey-900",                       "#609f92", "#1a171c", 4.5),
    ("orange-400 on grey-950 (badge/hover)",       "#f59f3d", "#121013", 4.5),
    ("orange-500 on grey-950",                     "#f2870d", "#121013", 4.5),
    ("orange-400 on grey-800",                     "#f59f3d", "#352e38", 4.5),
    # buttons
    ("white on scarlet-600 (primary btn)",         "#ffffff", "#c20a23", 4.5),
    ("white on scarlet-700 (primary hover)",       "#ffffff", "#91081a", 4.5),
    ("grey-100 on transparent+grey-950 (quiet btn)","#e6e3e8", "#121013", 4.5),
    # alert / success
    ("scarlet-400 on scarlet-950 (form error)",    "#f53d56", "#220206", 4.5),
    ("scarlet-300 on scarlet-950 (err small)",     "#f76e80", "#220206", 4.5),
    ("teal-400 on teal-950 (success note)",        "#80b3a7", "#0d1614", 4.5),
    # code chip / selection
    ("teal-300 on grey-900 (code chip text)",      "#9fc6bd", "#1a171c", 4.5),
    ("white on scarlet-600 (selection)",           "#ffffff", "#c20a23", 4.5),
    ("grey-200 on teal-900 (chip on teal tint)",   "#cec7d1", "#13201d", 4.5),
    # links
    ("scarlet-400 links on grey-950",              "#f53d56", "#121013", 4.5),
    ("teal-400 links on grey-950",                 "#80b3a7", "#121013", 4.5),
    ("scarlet-300 links on grey-900",              "#f76e80", "#1a171c", 4.5),
    ("orange-400 on grey-900 (kicker alt)",        "#f59f3d", "#1a171c", 4.5),
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
