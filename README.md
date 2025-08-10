# Tindlekit — MVP

This is the proof-of-concept / MVP repository for **Tindlekit**, an open platform to surface and support bold ideas of all kinds — from open-source projects and community initiatives to films, educational resources, and creative works.

## Collaboration & Development

Carmelyne Thompson × ChatGPT 5 × Claude Code Sonnet 4 — HITL Collaboration

This project was built through a Human-In-The-Loop workflow, pairing Carmelyne Thompson with two AI coding partners — ChatGPT 5 and Claude Code Sonnet 4 — to design, implement, and test features end-to-end.

We worked against a defined Product Requirements Document (PRD), guided by task lists and structured prompts (see /docs, PRD.md, TASKS.md, and prompt-for-tests-agent.md). Development included:

- Schema and migration updates
- Frontend & backend integration
- Security hardening (Cloudflare Turnstile)
- Unit & E2E testing suites
- CI/CD workflow in GitHub Actions

HITL Process Highlights

- PRD reviewed and refined with human oversight
- Implementation steps validated against acceptance criteria
- Automated tests authored and run in-loop until green
- Migration scripts aligned with API contracts
- Documentation updated alongside commits

## Documentation

Full documentation can be found in the [`docs/`](./docs) directory:

- `ARCHITECTURE.md` — High-level system design
- `CONTRIBUTORS.md` — Project contributors
- `DEPLOYMENT_GUIDE.md` — How to deploy the project
- `HOW_TO_VIBECODE.md` — Guide for Level 2 vibe coding
- `LLM_PROMPTS.md` — Prompt engineering and LLM usage reference
- `PHILOSOPHY.md` — The guiding philosophy of the project

📋 Planning & Development Docs

- PRD-v2.md — Latest product requirements document for the MVP, including feature scope, acceptance criteria, and design updates.
- PRD.md — Original product requirements document for the earliest concept and design planning.
- TASKS.md — Development task tracker with feature breakdowns, priorities, and progress notes.
- TEST.md — Manual and automated testing guidelines, including instructions for running unit and end-to-end tests.

## Prompts & Agent Workflow

This project used a multi-agent, human-in-the-loop approach.
Two dedicated prompt files guided each AI agent’s responsibilities:

- `prompt-for-tests-agent.md` — Defines the scope, style, and coverage for automated tests.
- `prompt-for-ui-agent.md` — Outlines UI/UX goals, component specs, and accessibility requirements.

Both prompts are part of the reproducible build process. They define the agent-specific responsibilities that were followed during development, ensuring consistent outputs even when re-run by different AI systems. By keeping them versioned in the repo, other contributors can adapt or extend the same workflows without losing the original intent.
