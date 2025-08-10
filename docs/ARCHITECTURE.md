# System Architecture

## 1. Overview
The **Tindlekit** system is designed to allow distributed, low-friction idea funding using a playful token-based approach.

## 2. Core Components
- **Frontend:** Static site for idea submissions and token allocations.
- **Backend:** Lightweight PHP API handling submissions, votes, and balances.
- **Database:** Shared relational DB storing ideas, token transactions, and leaderboards across all connected app instances. Requires access control and review of GitHub repository permissions to prevent accidental or malicious DB modifications.

## 3. Workflow
1. User submits idea via frontend.
2. Backend validates and records it.
3. Tokens are allocated by other users to "fund" the idea.
4. Leaderboard dynamically updates to reflect top-funded ideas.

## 4. Deployment
- Containerized setup for easy deployment.
- Environment variables define DB endpoints and token parameters.
- Optional distributed version allowing multiple community instances to connect to a shared funding pool.

## 5. Future Enhancements
- AI-powered suggestion ranking.
- Multi-chain or federated database support for global scaling.
- Integration with vibe-coding pipelines for automated idea prototyping.


## 6. Vibe Coder's Learning Guides

As part of making **Tindlekit** accessible and fun, we plan to include a set of **Vibe Coder Learning Guides**. These guides are not traditional technical manuals—they explain key concepts with a playful twist, matching the spirit of vibe coding.

### Topics to Cover:
- **What is a PRD (Product Requirements Document)?**
  A story-like guide explaining PRDs as "maps for ideas," helping vibe coders understand why clear goals matter before building.

- **What is a Database?**
  A conversational explanation of databases as "memory palaces for ideas and tokens," introducing core concepts like tables, relationships, and queries.

- **Types of Databases**
  A fun guide comparing relational vs. non-relational DBs, using metaphors like "structured libraries vs. freestyle zines."

- **Shared Databases and Access Control**
  Explaining why shared DBs need locks and keys, using simple analogies about community kitchens and recipe books.

- **Vibe Coding Pipelines**
  How ideas move from spark to prototype, showing vibe coders how their token-funded ideas can turn into reality.

- **Copy-Paste Prompts for LLMs**
  Each guide includes ready-to-use prompts that vibe coders can copy and paste into any LLM to get tailored, conversational explanations or deeper dives on each topic. This makes learning interactive and lets beginners ask follow-up questions directly to AI tutors.

- **Prompt Styles for Vibe Coders**
  A companion section teaching different prompt styles (e.g., concise prompts, chain-of-thought, role-based prompting, and exploratory questioning). Each guide will show how prompt style impacts responses, helping vibe coders experiment and learn how to communicate effectively with LLMs.

---
These guides will live alongside the technical docs, making it easier for newcomers to join, learn, and contribute to the vibe coding ecosystem without being overwhelmed by jargon.
