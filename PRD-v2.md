# Mode: QUICK LOOK
# Stakes: Low | Time cap: 45s | Token cap: 1.5k out / 2k in
# Scope: This file only: /index.php
# Tools: ❌ No web, ❌ no repo-wide search
# Output: One paragraph + one unified diff (if fix is obvious). No prose commentary (inline code comments allowed).


# Product Requirements Document (PRD) – Tindlekit

## 1. Overview
**Tindlekit** is an open, global platform where anyone can submit ideas, vote, and pledge support using symbolic or real AI tokens. It blends crowdfunding, hackathon, and collaborative build culture into a single commons. Inspired by Andrej Karpathy’s open challenge, Tindlekit ensures *no idea goes unnoticed* and removes gatekeepers from innovation.

---

## 2. Goals
- **Enable anyone, anywhere** to submit an idea.
- **Foster collaboration** by allowing pledges of tokens, time, or mentorship.
- **Support open source and proprietary projects** with transparent licensing.
- **Provide visibility** through a public leaderboard and detailed idea pages.
- **Encourage learning** by sharing PRDs, guides, and vibe coding tutorials.

---

## 3. Core Features

### 3.1 Submit an Idea
- **Form Fields:**
  - **Idea Title**
  - **Short Description** (max 280 chars)
  - **Detailed Description** (Markdown-supported)
  - **Category** (dropdown: AI, Sustainability, Education, Arts, Open Tools, etc.)
  - **Attachments:**
    - File Upload (PDF, Images, Markdown)
    - Link Embed (YouTube, Vimeo, Website)
  - **Open Source Checkbox**:
    - If checked → License dropdown (MIT, Apache 2.0, GPL, CC BY, Other)
  - **Submitter Info:**
    - Name / Alias
    - Email (private)
    - Location (optional)
    - Profile Link (GitHub, LinkedIn, etc.)
  - **Support Needed** (checkboxes):
    - AI Tokens
    - Dev Time
    - Mentorship
    - Other (text field)

- **Post-Submission Redirect**:
  - Redirects to the newly created Idea Page.

---

### 3.2 Idea Page
- **Header**: Idea Title, Category badge, License badge.
- **Media Section**:
  - Embedded video if link provided (YT/Vimeo)
  - Inline preview of PDFs/images/Markdown.
- **Description**: Full detailed description.
- **Supporters Section**:
  - AI Tokens pledged (symbolic/real)
  - List of supporters (name or alias) + type of pledge (tokens, time, mentorship).
- **Get Involved CTA**:
  - “Pledge Tokens” button
  - “Volunteer Time” button
  - “Mentor This Idea” button
- **Progress/Updates Feed**: Creator posts updates; supporters get notified.

---

### 3.3 Leaderboard
- Displays ranked list of ideas based on:
  - Total pledged tokens
  - Number of supporters
  - Popularity (views, likes)
- Mobile-friendly responsive layout.

---

## 4. User Roles
- **Guest**: Browse ideas, view leaderboard.
- **Registered User**: Submit ideas, pledge support, comment, receive updates.
- **Admin**: Moderate content, manage licensing defaults, feature ideas.

---

## 5. Tech Stack
- **Frontend**: SvelteKit + TailwindCSS
- **Backend**: Node.js (Express) or Supabase/Hasura for quick setup
- **Database**: PostgreSQL
- **Storage**: AWS S3 or Supabase Storage for file uploads
- **Auth**: Supabase Auth or Clerk
- **Payments/Tokens**: Placeholder for symbolic tokens; future integration with Stripe, crypto wallets, or AI provider APIs.
- **Embedding**: oEmbed for YT/Vimeo, PDF.js for PDF preview.

---

## 6. Roadmap

### Phase 1 – MVP (Target: Aug 11–12, 2025)
- Submit Idea form (with file & link upload)
- Idea Page (with embeds)
- Leaderboard (responsive)
- Story Page (origin story, about Tindlekit)
- Basic Auth
- Symbolic AI Tokens (static count)

### Phase 2 – Collaboration & Token System
- Real token integration (buy, donate)
- Volunteer/Mentorship tracking
- Notifications
- Search & Filters for ideas

### Phase 3 – Learning Commons
- PRD library
- Vibe coding tutorials
- Templates for forking projects

---

## 7. Success Metrics
- Number of submitted ideas
- Number of supporters and pledges
- Diversity of submitters (global reach)
- Engagement rate (comments, updates)
- Number of ideas built into real projects

---

## 8. Risks & Mitigation
- **Spam submissions** → Add moderation + simple CAPTCHA.
- **Token misuse** → Start symbolic, add verification before real currency.
- **Low engagement** → Feature ideas on homepage, social campaigns.

---

## 9. The Story
Tindlekit was born from a dormant domain in the founder’s pool, discovered during Andrej Karpathy’s open AI challenge in Aug 2025. Built to democratize innovation, it exists to ensure no idea is ignored, and that humanity rises alongside AI.
