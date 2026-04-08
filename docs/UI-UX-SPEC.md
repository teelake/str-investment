# STR Investment Services Ltd — UI/UX specification (v1)

**Role:** Product + brand direction for the marketing site.  
**Stack:** HTML, CSS, vanilla JS; optional small libraries only where they earn their bytes.  
**Goal:** A **human, credible, Nigerian microcredit** presence — **not** a generic fintech or “AI landing page” clone.

---

## 1. Brand foundation (from logo + global tokens)

**Logo language (reference):** Deep **emerald / forest green**, **metallic champagne / gold** wordmark, moss/sage atmosphere, premium depth (3D in logo → **flat dignity** on web: no fake 3D UI chrome).

**Canonical CSS tokens** (single source: `assets/styles.css` `:root`):

| Token | Hex / value | Use |
|--------|-------------|-----|
| Forest | `#0c1810` | Primary ink, footer, dark bands |
| Forest mid / soft | `#142920` / `#1e3d2d` | Gradients, hover states, depth |
| Paper | `#f7f4ee` | Default page background |
| Paper warm | `#efe8dc` | Section alternation, warmth |
| Sage / sage muted | `#dde8df` / `#c8d5cc` | Soft sections, table headers |
| Gold | `#b8942e` | Accents, rules, focus hints, primary CTA on dark |
| Gold soft | `#d4c9a8` | Secondary accent, borders with gold intent |
| Ink muted / faint | rgba on forest | Body secondary, captions |

**Rules**

- **Green carries trust and stability; gold carries prestige and CTA emphasis** — not decoration everywhere.
- **No** purple/blue SaaS gradients as the brand spine.
- **No** neon greens or rainbow pastel cards.

---

## 2. Design principles (humanized, anti-clone)

1. **Typography first** — Layout serves reading order; we don’t “decorate” weak hierarchy with icons.
2. **Editorial rhythm** — Alternate **full-bleed bands** (forest / sage / paper), **inset** content width, **one** strong pull-quote or asymmetric block per scroll “chapter.”
3. **Intentional asymmetry** — Grids may break: wide + narrow + offset cells; hero text vs image **not** 50/50 mirror cliché.
4. **One bespoke motif** — Ledger lines + optional single SVG “growth” stroke (already in CSS/HTML). **Do not** add stock fintech 3D illustrations.
5. **Photography over illustration** — Real people, offices, SMEs, parents — **license-free** with honest captions. Same radius + shadow language as cards.
6. **Restraint in motion** — If a library adds motion: **subtle** (opacity/translate on scroll), respect `prefers-reduced-motion`. No autoplay carousels as hero.

---

## 3. Typography system

| Role | Family | Notes |
|------|--------|--------|
| Display / H1–H2 | **Fraunces** (with Georgia fallback) | Credibility + character; not “startup sans.” |
| UI / body | **DM Sans** | Legibility, slightly warm; **not** Inter-only. |

- **Modular scale** ~1.2 from 16px base (see `--fs-*` in CSS).
- **Headlines:** Tight line-height, negative letter-spacing where needed.
- **Body:** ~1.65 line-height; max line length ~65ch for long copy.
- **Labels:** Uppercase + letter-spacing (`--ls-caps`), small size — use sparingly.

---

## 4. Layout & page blueprint

### Global

- **Max content width:** `--container` (~68rem), generous horizontal gutter.
- **Sticky header:** Light paper glass, thin border; nav links as **pills**, current page clearly marked.
- **Footer:** Forest band, gold-tinted links, email prominent.

### Home (story order)

1. **Hero (dark band):** One H1, motto, short value prop, **two** CTAs (primary + ghost), **asymmetric** image (Unsplash → replace).
2. **Products (paper):** **Bento** — unequal cells; avoid three identical cards.
3. **Why / credibility (sage):** Split + **pull-quote** (human voice).
4. **Testimonials (paper):** Three is OK only if copy is **specific**; consider 2 + “more stories” later.
5. **FAQ (paper):** Accordion, keyboard-friendly.
6. **Newsletter (dark band):** Single focused block; mailto-only OK for static site.

### About / Services / Products / Contact

- **One** visual “breather” (image or quote) per page minimum.
- **Products:** Long-form sections per product; pricing table **indicative** + legal tone.

---

## 5. Components (inventory)

| Component | Behavior |
|-----------|----------|
| Buttons | Primary (green gradient), ghost (outline), on-dark (gold). Pill radius. |
| Cards / bento cells | White/surface on paper; border + soft shadow; large radius. |
| FAQ | One open at a time optional; `aria-expanded`, focus ring. |
| Forms | Clear labels; mailto submit for static hosting. |
| WhatsApp FAB | Fixed; replace placeholder number. |

---

## 6. Optional libraries (only if needed)

| Need | Library | Rule |
|------|---------|------|
| Icons | **Phosphor**, **Lucide** (subset), or inline SVG | One set; no icon soup. |
| Light motion | **GSAP** or **CSS only** | Hero/section reveal only; degrade gracefully. |
| Sliders | Avoid; if testimonials need carousel, **Swiper** once | Not in hero. |

**Do not** add Bootstrap, Tailwind, or heavy UI kits for this brand-led site.

---

## 7. Accessibility & SEO (non-negotiable)

- One **`<h1>`** per page; logical heading order.
- Focus visible on all interactive elements (gold-tint ring).
- `alt` text describes **content**, not “image of …” filler.
- Meta title/description, canonical, OG; FAQPage JSON-LD where FAQs exist; `sitemap.xml`, `robots.txt`.

---

## 8. “Humanized” checklist (before shipping)

- [ ] Would a visitor **remember one visual moment** (hero asymmetry, quote, or photo) — not just “green site”?
- [ ] Is any section **indistinguishable** from a thousand SaaS templates? If yes, **change layout**, not only copy.
- [ ] Do testimonials and FAQs sound **specific to microcredit in Nigeria**?
- [ ] Are brand colors **disciplined** (gold used for emphasis, not noise)?

---

## 9. Next implementation step

When you approve this spec, implementation work is: **tighten visual execution** against §2–§5 (spacing, photography crop, bento balance, micro-copy), **without** changing stack. Optional: add **one** motion library pass scoped to §6.

**Document owner:** Product + frontend; **living file** — update when brand or compliance requirements change.
