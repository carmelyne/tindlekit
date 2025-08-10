CONTEST CONTEXT

- This build is part of Andrej Karpathy's "PayoutChallenge" (<https://x.com/karpathy/status/1952076108565991588>).
- Goal: Create something that uplifts "team human" through explanation, visualization, inspiration, understanding, coordination, etc.
- Deliverable must be uniquely created for this challenge and would not exist otherwise.
- Judging criteria: execution, leverage, novelty, inspiration, aesthetics, amusement.
- "People's choice" factor: public voting via likes on X/Twitter post reply.
- Build decisions should maximize:
  1) Immediate understandability for first-time visitors (wow factor in < 30s).
  2) Aesthetics + polish that convey intentional design.
  3) Social shareability and a clear story hook.
  4) One or more features that explicitly inspire, coordinate, or visualize human ideas/support.
- CONTEST ENTRY — Tindlekit (what Claude should optimize for)
- One‑liner: A public signal board for human ideas worth supporting — pledge **AI Tokens** (convertible to AI API/compute credits), Time, or Mentorship to surface what should exist.
- Why it uplifts team human: Makes inspiration + coordination tangible; lowers friction for communities to rally around useful ideas.
- Uniqueness for this challenge: pledge types include AI Tokens, Time, and Mentorship; transparent momentum signals; creator profiles that build trust.
- Primary interactions to shine in demo: Browse by category → open idea → pledge AI Tokens (live increment) → view creator profile → submit an idea.
- 30‑sec judge path (optimize LCP + motion polish):
  1) Land on Leaderboard (cards animate in).
  2) Filter by category.
  3) Click an idea → detail shows AI Token stat + pledge block above summary.
  4) Pledge → AI Token count increments live; toast confirms.
  5) Click creator name → user profile shows totals + their ideas.
- People’s‑choice hooks: clean social preview (OG), copy button for share URL, subtle success micro‑anim on pledge.
- Scope guard: UI polish only — keep API and data contracts intact.
- Implementation note: Do **not** create "rounds" or post third-party ideas on their behalf. If needed, use the optional Spotlight banner only.

UNIQUENESS & PLATFORM INTENT
- This submission is created **specifically** for Andrej's PayoutChallenge and would not exist otherwise.
- Do **not** introduce a "rounds" concept in the product. Tindlekit is evergreen and accepts ideas continuously.
- Copy tone: invite people to support ideas and to run their own future rounds.

SPOTLIGHT (optional, UI-only; no data model changes)
- Purpose: briefly acknowledge an external challenge *without* modeling it in-app.
- Behavior:
  - Show a dismissible banner at the top of Leaderboard with custom text + external link.
  - No tags, no filters, no special views. Purely cosmetic and optional.
- Config (static JSON):
  - spotlight.enabled: boolean
  - spotlight.text: string (short)
  - spotlight.url: string (external)
- A11y: banner is focusable; dismiss button is keyboard accessible; remember dismissal in localStorage.

Tindlekit supports three pledge types:
- **AI Tokens** (convertible to AI API/compute credits — e.g., “we pooled 10B tokens to fund this idea’s AI costs”)
- **Time** (development, PR, management, etc.)
- **Mentorship** (guidance, coaching, or domain expertise)

These are surfaced equally in the UI. The backend (`api.php?action=express_interest`) already supports all three types.

ROLE You are a senior UI engineer. Implement UI/UX polish for Tindlekit without changing backend endpoints or data shapes.

CONTEXT (do not alter)

- APIs (fixed):
  - /api.php?action=list_ideas&category&limit&offset
  - /api.php?action=categories
  - /api.php?action=add_like&id
  - /api.php?action=create_idea
- DOM/event contract (keep working):
  - #leaderboardGrid, #lbStatus, .stretched-link
  - window.dispatchEvent(new CustomEvent('category:changed',{detail:{category}}))
  - Helpers must remain: escapeHtml, renderCategoryBadge, renderTagsChips
- Data shape: { id, title, summary, tags, category, tokens, likes }

GOAL
Ship an **Awwwards-grade** front-end layer using:

- **Tailwind + shadcn** components (Svelte flavor preferred; if not, React-free port of shadcn tokens/utilities is fine).
- **GSAP** for tasteful motion & **typographic micro-animations** (no paid plugins).
- **Three.js** for a subtle, performant background effect (degrades off on low-power).
- **Web-first** interaction polish (desktop → tablet → mobile). Keep hit targets ≥44px on touch, strong focus states, ESC/Trap on overlays.

-THEME TOKENS (use everywhere)
- Base palette for Tindlekit. Apply to Tailwind config, CSS vars, and components so there is no hex drift.
- JSON (authoritative):
```json
{
  "bg": "#0b1020",
  "card": "#11162a",
  "border": "#24324a",
  "fg": "#e6edf3",
  "muted": "#9fb3c8",
  "accent": "#22d3ee",
  "accentStart": "#06b6d4",
  "success": "#22c55e"
}
```
- Usage guidance:
  - Background: `bg` or gradient `radial-gradient(1000px 600px at 50% -20%, #14203a, #0b1020)`
  - Cards: `card` with `border`.
  - Text: `fg`; muted text: `muted`.
  - Primary buttons: gradient `accentStart → accent` with dark text `#07101f`.
  - Success badges/positive states: `success`.
  - Accessibility: **All color pairings must meet WCAG 2.1 AA** contrast ratios — ≥4.5:1 for body text and ≥3:1 for large/semibold text and UI elements. If any token pairing falls short in context (e.g., badge on card), derive an accessible variant (e.g., darken/lighten by 6–12% or add an overlay) rather than changing the base palette.

## ICONOGRAPHY SPEC
- **Icon Set:** [Iconoir](https://iconoir.com) (MIT Licensed)
- **Style:** Consistent stroke width 1.5px, round line caps, no fills unless specified.
- **Format:** Inline SVG (optimized for Tailwind: `fill-current`, `stroke-current`).
- **Size:** Default `24x24px`.
- **Accessibility:** Include `<title>` tags for screen readers; title text = icon’s semantic name.
- **Naming:** Match Iconoir’s PascalCase icon names exactly for future auto-import.
- **Usage Example:**
  ```html
  <svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    width="24"
    height="24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.5"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-labelledby="iconTitle"
  >
    <title id="iconTitle">Idea</title>
    <path d="..."/>
  </svg>
  ```
- **Component API:** `<Icon name="LightBulb" class="w-6 h-6 text-blue-500" />`

-NAVIGATION (global header)

- Primary items (desktop): Leaderboard, Submit an Idea, Community Support.
- Community Support lives at **/community**. Migrate content from about.php (origin story, tokens explainer, FAQ, CTA). Retire about.php.
- Current-page state: add aria-current="page" on active link.
- Desktop: persistent header with subtle underline-on-hover and active state.
- Tablet/Mobile: shadcn NavigationMenu or Drawer; GSAP slide-in; focus trap; ESC closes; inert background; touch targets ≥44px.

USER FLOWS (inspiration: Kickstarter + GoFundMe + Kiva)

USER PROFILE PAGE (user.php)

- Purpose: Display a submitter's public profile, aggregate stats, and list of their submitted ideas.
- Inputs: email (query param, e.g., /user.php?email=<foo@example.com>)
- Data contract: read-only from server; no new API endpoints in this pass.
- Layout:
  - Header: submitter name (or email if no name), back link to Leaderboard.
  - Stats grid: Ideas count, total AI Tokens, total likes (3 equal columns).
  - Idea list: card/list layout matching LeaderboardCard style; each links to idea.php?id=...
- Responsive:
  - Web-first → tablet → mobile.
  - Stats grid collapses to stacked on mobile.
- A11y:
  - Aria-labels for profile name, stats.
  - Focus styles consistent with site.
- Motion:
  - Fade/slide-in for header + stats; staggered entrance for idea list.
- Styling:
  - Use shadcn/Tailwind tokens; no inline CSS.
  - Reuse LeaderboardCard components where possible for idea list items.
- Known constraints:
  - No edit functionality in this pass.
  - If no ideas, show an empty state message.

INVESTOR INTEREST (delta; no API/DB changes)
- Goal: allow VCs/angels to signal interest without affecting AI Token counts.
- UI (idea detail): compact section "Investor Interest".
  - CTA: "I’m interested in funding this" (opens modal).
  - Modal fields: name, firm, email, stage (multi), ticket range (select), focus tags, note, NDA opt-in.
  - Submit → POST /api.php?action=express_interest with:
    - pledge_type='capital'
    - pledge_details JSON: { stage:[], ticket:"25k–100k", focus:[], note:"...", nda:true/false }
    - no tokens field (no increment)
  - Success: toast + close; show counter "Investor interests: N".
- List (read): include 'capital' rows in interests with a shield icon and masked email.
- Card badge (optional): "Investable" when investor interests ≥ 1.
- A11y: modal trap + ESC; buttons ≥44px; motion-safe.
- Legal copy: "Tindlekit surfaces interest only and is not an investment platform. No securities are offered here."

1) Discover → Evaluate → Support (Leaderboard-first)
   - Browse cards, filter by category/tags.
   - Open Idea detail: hero + AI Token Stat card + pledge block above summary.
   - Support: Like (optimistic), Pledge AI Tokens (updates live), Share.
2) Submit an Idea (guided)
   - Minimal form: title, summary, category (combobox), tags, optional link/image.
   - Server uses existing create_idea endpoint; validate and show toast.
   - Post-submit: route to Idea detail with highlight pulse on AI Token Stat.
3) Community Support (/community)
   - Education-first: origin story, how AI Tokens/pledges work, FAQs, safety.
   - Clear CTA back to Leaderboard and Submit an Idea.

NON-GOALS

- No API/DB changes. No framework rewrite of PHP endpoints. Progressive enhancement only.

DELIVERABLES

1) **UI kit & tokens**
   - Add Tailwind, shadcn tokens (CSS vars), and component primitives.
   - Create a minimal **/ui** folder:
     - /ui/tokens.css         (CSS vars: radius, spacing, brand, bg/fg)
     - /ui/motion.ts          (GSAP timelines/utilities)
     - /ui/typography.ts      (splitText util for word/char anims—no GSAP SplitText)
     - /ui/components/*       (Drawer, Card, Badge, Button, Combobox, Toast)
     - /ui/three/*            (initThreeBackground.ts for a grain/particles layer)
   - Respect **no inline CSS**. Use classes and CSS vars.

2) **Component upgrades**
   - **LeaderboardCard**
     - Use shadcn Card + accessible focus ring.
     - Improve spacing/typography, keep rank circle + 🍀 AI Token pill + category badge.
     - Add hover-lift (transform/opacity), prefers-reduced-motion safe.
   - **Category Drawer**
     - shadcn Drawer: slide-in 260ms cubic; focus trap; ESC closes; inert background.
     - Searchable **Combobox** using existing categories; Enter selects; validates.
   - **Tag Chips**
     - Badge variant with consistent wrap/gaps; keyboard focusable.
   - **Idea Header**
     - 70/30 layout; AI Token Stat card subtly emphasized; pledge block above summary.

3) **Motion & Type**
   - GSAP entrance timeline per view:
     - stagger in cards (y: 12–16px, opacity), drawer slide, AI Token stat pop.
   - Typographic micro-animations:
     - On idea title: split to words; animate 1–2 frames per word (opacity + y).
     - On hover for .stretched-link: underline reveal (clip-path) + letter-spacing +0.2px.

4) **Three.js background**
   - Lightweight effect behind main content (e.g., slow-moving gradient particles or perlin-ish noise plane).
   - Adaptive: pause on tab blur; disable on `prefers-reduced-motion` or low DPR.
   - Mount once on `#app-bg` (add a background div if missing).

5) **Feedback & Empty States**
   - **Skeletons** for first paint + load-more.
   - **Empty states** for 0 results and end-of-feed.
   - **Toasts** (non-blocking) for like/pledge success/fail; auto-dismiss; a11y friendly.

6) **Error Surface (dev only)**
   - When fetch fails, show small collapsible JSON snippet panel (dev flag).

7) **Responsive priorities (Web → Tablet → Mobile)**
   - Desktop (default): multi-column grid (2–3–4 as width allows); hover affordances; keyboard parity.
   - Tablet: prefer 2-col grid; maintain spacing; drawer works with touch + keyboard; hit targets ≥44px.
   - Mobile: single-column; reduce motion; collapse non-essential chrome.

8) **Feature flags**
   - `window.__TK_FLAGS = { three:true, motion:true, dev:false }`
   - Respect flags to disable Three/Motion during QA.

DEFINITION OF DONE (checklist)

- [ ] Category drawer traps focus; ESC closes; background inert; restores focus.
- [ ] /category/Open%20Source loads without console errors.
- [ ] Load more appends, hides at end, keeps active category filter.
- [ ] Pledge increments UI AI Tokens live; like uses optimistic UI (no full reload).
- [ ] A11y: tab order sane; badges/pills have aria-labels; **all theme usages** meet WCAG 2.1 AA contrast (≥4.5:1 body, ≥3:1 large UI text).
- [ ] Performance: LCP < 2.5s on mid-tier tablet; Three.js paused on blur; motion reduces on PRM.
- [ ] No inline CSS. Names consistent with existing helpers/IDs.
- [ ] Submitting 'capital' interest does not change AI Token totals.
- [ ] Investor interests counter shows on idea detail; no console errors.
