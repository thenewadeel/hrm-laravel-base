# Wittness Tech — 90-Day Materials Refresh Protocol (Salesman Agent Mode)

**Who runs this:** the sales agent assistant (or the salesperson) — every 90 days
**Scope:** all print materials in `docs/marketing/print/` + strategy doc + QR codes + contact info
**Goal:** keep every printed asset true, current, and testable, so a flyer from month 1 and month 4 tell the same story with up-to-date facts.

---

## 1. What changes on the 90-day cycle

| Material | Must be checked/replaced | Typical change |
|---|---|---|
| All assets | **QR codes** & booking links | Re-point demo QR; rotate tracked link/phone extension |
| One-pager, brochure | **Proof points & stats** | Swap in new customer results, numbers, testimonials |
| Brochure, one-pager | **Pricing / offer line** | New promo, price change, package rename |
| Direct-mail letter | **Contacts, offer, P.S.** | New target list, new offer, new signer |
| Business card | **Salesperson name/role/phone** | New rep, number change |
| Strategy doc (§5 pack, §6 launch) | **Publication names, budgets, calendars** | New episodes, new rates, new pilot city |
| All assets | **Contact placeholders** | Unfilled `[PHONE]/[EMAIL]/[WEBSITE]` must be filled |

**Two hard rules:**
1. **Never print a placeholder.** Any `[PHONE]`, `[EMAIL]`, `[WEBSITE]`, `[NAME]` or `[ROLE]` stub, a re-introduced `WT`/“goes here” logo stub, or "SCAN TO BOOK A DEMO" QR that is still a stub must block release. (Logos are final — `wittness-light.png` on dark-green bars, `wittness-dark.png` on white.)
2. **Never ship two versions of the same story.** When the offer/price/publisher changes, all materials change together in one pass, never one-off.

---

## 2. The refresh pass, step by step (90 minutes or less)

### Step 1 — Audit (15 min)
1. Open `docs/marketing/print/*.html` in a browser and print-preview each to PDF.
2. Grep every file for leftover stubs and date/data drift:

```bash
cd docs/marketing/print
rg -n "\[(PHONE|EMAIL|WEBSITE|NAME|SALESPERSON|CONTACT|DATE|ROLE)\]" *.html
rg -n "WT|goes here|SCAN TO" *.html
```

3. List findings per file in the checklist below. Do **not** fix anything yet — the whole pass is done in one edit burst.

### Step 2 — Content refresh (30 min)
- [ ] Update **contact block** (phone, email, website) identically in every file.
- [ ] Update **salesperson name/role** on business card + direct-mail letter.
- [ ] Replace **proof points/stats** on one-pager front & brochure with the newest verified numbers.
- [ ] Add **one fresh testimonial** to one-pager front or brochure CTA; keep it to one line.
- [ ] Confirm **module feature list** (Accounting / HR & Payroll / Inventory / Membership) is still accurate — if your product gained/lost a headline feature, update all four module blocks together.
- [ ] Update **offer line** ("20-minute demo", price, promo) in flyer, brochure CTA, letter CTA.
- [ ] Update **footer contact & QR** on every asset.

### Step 3 — QR & tracking refresh (15 min)
- [ ] Generate a **new QR code** pointing to the current demo-booking page (or a fresh tracked link per campaign).
- [ ] Replace every `.qr` block in all 6 assets with the new code.
- [ ] Keep the printed text next to each QR consistent ("SCAN TO BOOK A DEMO" / "SCAN TO BOOK A 20-MINUTE DEMO" — pick one phrase and use it everywhere).

### Step 4 — Strategy doc sync (10 min)
- Open `docs/marketing/ANZ-offline-marketing-strategy.md`:
- [ ] Update §4 list of publications if any title folded/renamed.
- [ ] Update §5 materials pack (inventory + sizes) if an asset was added/removed — keep it matching the actual `print/` folder.
- [ ] Update §6 90-day calendar to the *next* 90 days.
- [ ] Update §7 budget with real quotes/actual spend.

### Step 5 — Validate & release (20 min)
- [ ] Structural check on every HTML file (tags balanced) — see validation snippet below.
- [ ] Re-run the stub grep (Step 1, #2) — must return **zero** hits.
- [ ] Print-preview every PDF; confirm given page sizes still hold (A4, A5, 90×50, 50×50).
- [ ] Confirm every file opens without errors and prints to the intended size.
- [ ] Hand the updated PDF set + list of changes to the salesperson.

---

## 3. Validation helpers

**HTML structural check** (run from repo root):

```bash
python3 - <<'EOF'
import html.parser, pathlib, glob
V = {'meta','link','br','hr','img','input','area','base','embed','source','track','wbr'}
class P(html.parser.HTMLParser):
    def __init__(self):
        super().__init__(); self.s=[]; self.e=[]
    def handle_starttag(self,t,a):
        if t not in V: self.s.append(t)
    def handle_endtag(self,t):
        if t in V: return
        if self.s and self.s[-1]==t: self.s.pop()
        elif t in self.s:
            while self.s and self.s[-1]!=t: self.e.append(f'unclosed <{self.s.pop()}> before </{t}>')
            self.s.pop()
        else: self.e.append(f'stray </{t}>')
for f in sorted(glob.glob('docs/marketing/print/*.html')):
    p=P(); p.feed(pathlib.Path(f).read_text())
    print(('OK ' if not p.e and not p.s else 'BAD') , f)
    for e in p.e: print('   ', e)
EOF
```

**Expected output:** every file `OK`.

---

## 4. Asset inventory (keep this in sync)

| Asset | File | Page size | Fold / stack |
|---|---|---|---|
| One-pager (front/back) | `one-pager.html` | A4 portrait (×2) | n/a |
| Flyer | `flyer.html` | A5 portrait | n/a |
| Tri-fold brochure | `brochure-trifold.html` | A4 landscape (×2) | C-fold at 99/198mm, print double-sided flip on long edge |
| Business card | `business-card.html` | 90×50mm (×2 faces) | print double-sided |
| QR shelf / demo card | `qr-shelf-card.html` | A5, two 50×50 die-cut cards | cut, tent-fold for display |
| Direct-mail letter | `direct-mail-letter.html` | A4 portrait | mailer #10/DL + brochure |

---

## 5. Next-90 checklist (fill in at each refresh)

- **Next refresh date:** __________
- **Stub count after pass:** ____ must be 0
- **New QR set generated:** ☐
- **Tracked link/phone ext rotated:** ☐ value: __________
- **New testimonial added:** ☐ quote: __________
- **Offer / price updated:** ☐ to: __________
- **Publication list updated:** ☐ changes: __________
- **Signed off by:** __________

---

## 6. Do NOT do

- Do not redesign layouts during a refresh — refresh content only; visual redesign is a separate project.
- Do not change the primary brand colour `#1e3f20` / accent `#4c784e` / muted `#a3b899` without a brand decision.
- Do not delete old QR/campaign logs — keep last version in a `docs/marketing/archive/` folder for comparison.
- Do not add new modules/features to materials unless they appear in the product and its docs first.

---

*Version 1 · created with the initial campaign · refresh every 90 days.*