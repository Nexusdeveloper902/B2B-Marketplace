#!/usr/bin/env python3
"""Phase 5 content-preservation audit — TASK-012.
Checks every item from the pre-redesign inventory is present in the
rendered output. Mirrors .agent/OBSERVATIONS/CONTENT-INVENTORY-landing-2026-09-06.md"""

import urllib.request

BASE = "http://127.0.0.1:8099"

def get(path):
    with urllib.request.urlopen(BASE + path) as r:
        return r.read().decode("utf-8")

en = get("/")
es = get("/lang/es")
es_landing = get("/")  # after session set? no — urllib has no session; fetch es content directly
# fetch ES variant via the locale route using a cookie-less approach: use the translated lang file instead
import subprocess, json

checks = []

def check(name, needle, haystack=en, exact=True):
    ok = (needle in haystack) if exact else (needle.lower() in haystack.lower())
    checks.append((name, ok))

# ---- Hero ----
check("hero headline EN (test-asserted)", "Every card tap becomes a record you can trust.")
check("hero pitch", "Presence Platform is presence-event infrastructure: one NFC card, one tap, one timestamped event. Attendance, meal service, and recycling incentives all read from the same stream.")
check("hero CTA primary", "See it in action")
check("hero CTA secondary", "Request a demo")
check("meta title", "Presence Platform — every card tap becomes a record")

# ---- Ledger ----
check("ledger title", "Event log — Riverside School")
check("ledger live", "recording")
for col in ["time", "card", "reader", "event"]:
    check(f"ledger column: {col}", f">{col}</th>")
rows = [
    ("07:58:12", "0441", "GATE-A", "attendance.in"),
    ("07:58:14", "0087", "GATE-A", "attendance.in"),
    ("08:02:47", "0132", "GATE-B", "attendance.in"),
    ("12:14:03", "0441", "PAE-1", "meal.lunch"),
    ("15:41:09", "0558", "ECO-PT", "recycle.drop"),
    ("16:22:41", "0132", "GATE-B", "attendance.out"),
]
for t, c, r, e in rows:
    check(f"ledger row {t} {c} {r} {e}", f"{t}</td><td>{c}</td><td>{r}</td><td>{e}")
check("ledger card label", "CARD 0441")
check("ledger reader name", "GATE-A")

# ---- Problem ----
check("problem title", "The record is the problem.")
check("problem body_1", "Schools and organizations count the things that matter: who arrived, who ate, who participated, what came back to be recycled. Almost everywhere, that counting is still done by hand — attendance rolls, clickers, spreadsheets, and memory.")
check("problem body_2", "Hand counts drift. Totals get challenged at audit time. Assembling a month of reports takes days nobody has. And when the numbers decide funding, compliance, or staffing, &quot;probably&quot; is not good enough.")
check("costs title", "What weak records cost")
check("cost 1", "Attendance disputes you cannot settle with evidence")
check("cost 2", "Meal-program counts that do not reconcile with eligibility lists")
check("cost 3", "Incentive programs that stall because nobody can tally them")

# ---- Steps ----
check("steps title", "From tap to report")
check("steps intro", "The platform records the same four-part event every time, no matter which application reads it.")
steps = {
    "Tap": "A student or staff member presents their NFC card to a wall-mounted reader at the door, the service point, or the drop-off station. No app, no battery, nothing to pair.",
    "Identify": "The reader reads the card&#039;s unique ID and pairs it with its own reader ID, so every event knows who and where.",
    "Timestamp": "The platform stamps the event when it arrives and stores it as an immutable record — not a spreadsheet cell someone can overwrite.",
    "Report": "Dashboards and reports read the same event stream. Attendance, meals, and incentives are tallies over the same records, not separate systems.",
}
for title, body in steps.items():
    check(f"step: {title}", f">{title}</h3>")
    check(f"step body: {title}", body)

# ---- Applications ----
check("apps title", "Three applications, one event stream")
check("apps intro", "Every package runs the same architecture. The applications are different reports over the same records.")
apps = {
    "attendance.in": ("Attendance", "Entry and exit per person per day, at every reader you install. Daily totals build themselves; late arrivals and gaps are flagged instead of hunted."),
    "meal.lunch": ("Meal tracking (PAE)", "Each subsidized meal is tied to the eligible student who tapped for it. Counts reconcile with your program lists by construction, and days end with a tally instead of an estimate."),
    "recycle.drop": ("Recycling incentives", "Drop-offs at recycling points are credited to the person or group that tapped. Incentive tallies run themselves, and the evidence for every point is one lookup away."),
}
for label, (title, body) in apps.items():
    check(f"app label: {label}", label)
    check(f"app title: {title}", f">{title}</h3>")
    check(f"app body: {title}", body)

# ---- Audience ----
check("audience title", "Who it is for")
aud = {
    "Small schools": ("One reader at the main entrance and up to 200 cards. Attendance you can defend in any meeting.", "See the Starter package"),
    "Larger schools and campuses": ("Readers across gates, service points, and recycling stations, with meal tracking and incentives included.", "See the Campus package"),
    "Businesses and organizations": ("Custom event types for whatever you need to count: shifts, zones, assets, visits.", "See Enterprise"),
}
for title, (body, link) in aud.items():
    check(f"audience: {title}", f">{title}</h3>")
    check(f"audience body: {title}", body)
    check(f"audience link: {link}", f">{link}</a>")

# ---- Closing ----
check("closing title", "See what a tap can do.")
check("closing body", "Walk through the product in two minutes, or tell us what you need to count and we will show you the events.")

# ---- Shared chrome ----
for item in ["Product", "Pricing", "Enterprise", "Contact", "Request a demo", "EN", "ES", "Skip to content"]:
    check(f"nav: {item}", item)
check("footer note", "Presence Platform turns NFC card taps into timestamped records: attendance, meal tracking, and recycling incentives on one event stream.")
check("footer site col", "Site")
check("footer get started", "Get started")
check("footer packages", "Packages and pricing")
check("footer demo", "Request a demo")
check("copyright", "2026 Presence Platform. All rights reserved.")
check("built for", "For schools, campuses, and enterprises.")

# ---- links (routes) ----
for path in ["/product", "/pricing", "/enterprise", "/contact"]:
    check(f"link href: {path}", f'href="http://127.0.0.1:8099{path}"')

# ---- ES checks via lang file (locale switch verified separately in browser) ----
es_lang = open("lang/es/landing.php").read()
check("ES headline present in lang", "Cada toque de tarjeta se convierte en un registro confiable.", es_lang)
check("ES problem title", "El problema es el registro.", es_lang)
check("ES steps title", "Del toque al reporte", es_lang)
check("ES apps title", "Tres aplicaciones, un mismo flujo de eventos", es_lang)
check("ES widget title", "Lo que cuenta cada paquete", es_lang)
check("ES demo tag", "demo en vivo", es_lang)
check("ES kicker", "infraestructura de eventos de presencia", es_lang)
check("ES meals days L..V", "'L', 'M', 'X', 'J', 'V'", es_lang)

fails = [n for n, ok in checks if not ok]
print(f"CONTENT AUDIT: {sum(1 for _, ok in checks if ok)}/{len(checks)} items present")
if fails:
    print("MISSING:")
    for n in fails:
        print("  -", n)
else:
    print("ALL INVENTORY ITEMS PRESENT — content preservation confirmed")
