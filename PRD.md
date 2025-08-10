# 🎯 Product Requirements Document (PRD)

## Project: Inspired by The Andrej Effect – Distributed Vibe Code Level 2 Edition

---

## 1️⃣ Vision

To build a lightweight, open-source platform where great ideas don’t get lost online. Anyone can **fund ideas with tokens** 🍀 in a fun, transparent leaderboard system.
The platform doubles as a **Level 2 vibe coding** teaching repo—learn how to build from a PRD and task list, not just ad-hoc prompts.

---

## 2️⃣ Problem

- Great explainers, visualizers, and tools disappear in feeds.
- No low-friction way to **signal-boost and back ideas** without complex voting/funding mechanisms.
- Beginners in coding rarely learn how to read a PRD and execute tasks collaboratively.

---

## 3️⃣ Goals

- Build a **forkable PHP + static frontend** repo that anyone can deploy.
- Tokens = 🍀 or emojis for **fun, low-stakes funding vibes**.
- Teach vibe coding Level 2: **interpret requirements, break tasks, ship iteratively**.

---

## 4️⃣ Scope

### Must Have

- Submit an idea
- Vote/fund ideas with tokens
- Leaderboard display
- Backend API (PHP) with SQLite or MySQL
- Static frontend (HTML/CSS/JS)
- Simple, lightweight deployment

### Nice to Have

- Admin panel for idea moderation
- Basic authentication
- Export data (CSV/JSON)

### Out of Scope

- Full-fledged crowdfunding features
- Blockchain integrations
- Complex real-time websockets

---

## 5️⃣ Constraints

- Must run on **shared hosting** (low cost).
- Use **vanilla PHP and JS**, no heavy frameworks.
- Single database table for simplicity.
- Keep the vibe **playful and fork-friendly**.

---

## 6️⃣ Success Criteria

- Anyone can fork and deploy in under **15 minutes**.
- Token funding feels **light, fun, and social**.
- New devs learn **how to read a PRD and ship tasks**.

---

## 7️⃣ Deliverables

- **README.md:** Intro and quickstart
- **PRD.md:** This doc
- **TASKS.md:** Beginner → advanced vibe tasks
- **Backend:** Minimal API
- **Frontend:** Static leaderboard
- **Docs:** Guide to vibe coding

---

🍀 *Let’s teach the internet how to vibe code, Level 2 style.*

# ✅ TASKS.md – Vibe Code Level 2

This task list teaches you how to build "Tindlekit" repo step by step.
Pick your vibe: beginner 🟢, intermediate 🟡, advanced 🔴.

---

## 🟢 Beginner Vibes

1. **Setup Local Environment**
   - Clone the repo
   - Install PHP + SQLite or MySQL
   - Run `php -S localhost:8000 -t backend`

2. **Database Schema**
   - Import `schema.sql`
   - Verify table creation: `ideas` (id, title, votes)

3. **Static Leaderboard**
   - Open `frontend/index.html`
   - Add sample ideas to `ideas.json`

---

## 🟡 Intermediate Vibes

4. **Backend Endpoints**
   - `GET /ideas` returns all ideas
   - `POST /vote` increments vote count
   - `POST /idea` adds a new idea

5. **Connect Frontend to Backend**
   - Use `fetch()` in `main.js` to get data
   - Update leaderboard dynamically

6. **Basic Styling**
   - Add playful colors and token emojis 🍀 in `styles.css`

---

## 🔴 Advanced Vibes

7. **Deploy to Shared Hosting**
   - Prepare `.htaccess` for routing
   - Deploy via FTP or Git

8. **Add Admin Mode (Optional)**
   - Simple password-protected page for idea moderation

9. **Export Feature**
   - `/export` endpoint returns ideas as CSV or JSON

---

💡 *Pro Tip:* Follow PRD.md → TASKS.md → Ship → Fork → Share your vibes with the world!
