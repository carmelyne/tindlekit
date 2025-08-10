# 🤖 LLM Helper Prompts for The Andrej Effect

Copy-paste these prompts to get AI help building the platform! Each prompt includes context about our PRD and goals.

---

## 🏗️ Backend Development Prompts

### API Endpoint Development

```
I'm building "Tindlekit - Inspired by The Andrej Effect" - a lightweight token-based idea funding platform.

Context from PRD:
- PHP backend with SQLite/MySQL
- Must run on shared hosting (vanilla PHP, no frameworks)
- Simple token voting system with 🍀 emojis
- Goal: anyone can fork and deploy in 15 minutes

I need help implementing: [SPECIFIC_ENDPOINT]

Requirements:
- Clean, readable PHP code
- Proper error handling with JSON responses
- SQLite-compatible queries
- Security best practices for shared hosting

Current database schema:
```sql
CREATE TABLE ideas (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  votes INTEGER DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Please provide the complete PHP code with error handling and testing suggestions.

```

### Database Schema Optimization
```

I'm designing a minimal database for "Tindlekit - Inspired by The Andrej Effect" idea voting platform.

Requirements:

- SQLite for easy deployment
- Support idea submissions with title/description
- Token-based voting (🍀 counts)
- Optional: user sessions, moderation flags
- Keep it simple - single developer can deploy in 15 minutes

Current schema:
[PASTE_CURRENT_SCHEMA]

Please suggest:

1. Schema improvements for better performance
2. Indexes for common queries
3. Migration strategy for updates
4. Sample data for testing

Focus on shared hosting compatibility and simplicity.

```

### PHP Configuration & Security
```

I'm deploying "The Andrej Effect" to shared hosting. Need help with:

1. .htaccess configuration for clean URLs
2. PHP security best practices
3. Rate limiting for voting endpoints
4. CORS handling for frontend-backend communication

Platform constraints:

- Vanilla PHP (no composer/frameworks)
- SQLite database
- Static frontend files
- Must be fork-friendly and educational

Please provide complete configuration files with comments explaining each security measure.

```

---

## 🎨 Frontend Development Prompts

### UI/UX Enhancement
```

I'm building the frontend for "Tindlekit - Inspired by The Andrej Effect" - a playful token-based idea leaderboard.

Design goals:

- Fun, approachable vibe (🍀 token theme)
- Mobile-friendly leaderboard
- Animated token interactions
- Educational feel (teaching vibe coding)

Current HTML structure:
[PASTE_CURRENT_HTML]

Current CSS:
[PASTE_CURRENT_CSS]

Please enhance with:

1. Token drop animations when voting
2. Responsive grid layout for idea cards
3. Playful hover effects and micro-interactions
4. Better typography and color scheme
5. Loading states and error handling UI

Keep it vanilla JS/CSS - no frameworks. Make it feel welcoming for new developers to learn from.

```

### JavaScript Functionality
```

I need help with the JavaScript for "Tindlekit - Inspired by The Andrej Effect" leaderboard platform.

API endpoints available:

- GET /backend/routes/get-ideas.php (returns idea list)
- POST /backend/routes/post-vote.php (increment votes)
- POST /backend/routes/add-idea.php (create new idea)

Requirements:

1. Fetch and display ideas dynamically
2. Handle token voting with optimistic UI updates
3. Add new idea form with validation
4. Error handling and user feedback
5. Simple animations for vote interactions

Please provide:

- Complete main.js with modern ES6+ features
- Error handling for network requests
- Local storage for vote tracking (prevent spam)
- Accessibility considerations
- Comments explaining the vibe coding approach

Keep it educational - a new developer should understand every line.

```

---

## 📚 Documentation & Content Prompts

### Educational Content Creation
```

I'm creating educational content for "Tindlekit - Inspired by The Andrej Effect" - a platform that teaches "Level 2 vibe coding" (structured development from PRD to shipping).

Context:

- Target audience: developers learning to work from requirements
- Teaching structured collaboration vs ad-hoc coding
- Open source, fork-friendly approach
- Playful tone with serious educational value

I need help writing:
[SPECIFIC_DOC_TYPE: tutorial/guide/explanation]

Topics to cover:

1. Reading and interpreting PRDs
2. Breaking down tasks collaboratively
3. Version control best practices
4. Code review culture that's educational, not harsh
5. Shipping iteratively with user feedback

Tone: Friendly mentor, encouraging experimentation, emphasizing learning through building real projects.

Please create content that helps developers level up their product thinking, not just coding skills.

```

### Contribution Guidelines
```

I need contribution guidelines for "Tindlekit - Inspired by The Andrej Effect" open source project.

Project context:

- Educational platform teaching structured development
- Token-based idea funding with playful UI
- Target: accessible to new developers
- Emphasis on "vibe coding" (positive, collaborative culture)

Guidelines should cover:

1. How to read the PRD before contributing
2. Task selection and claiming process
3. Code style and commit message conventions
4. Review process that's educational, not intimidating
5. Recognition system for contributors

Create guidelines that encourage learning, experimentation, and positive community interaction. Make contributing feel welcoming for developers at all levels.

```

---

## 🚀 Deployment & DevOps Prompts

### Shared Hosting Deployment
```

I need to deploy "Tindlekit - Inspired by The Andrej Effect" platform to shared hosting (like cPanel/Bluehost).

Stack:

- PHP backend with SQLite
- Static HTML/CSS/JS frontend
- No frameworks or complex build processes
- Goal: 15-minute deployment for anyone

Please provide:

1. Step-by-step deployment guide
2. File structure for shared hosting
3. Common troubleshooting issues and solutions
4. How to handle database initialization
5. SSL and domain configuration tips

Include screenshots or ASCII diagrams where helpful. Make it accessible to developers new to deployment.

```

### Performance Optimization
```

I want to optimize "Tindlekit - Inspired by The Andrej Effect" platform for shared hosting performance.

Current stack:

- Vanilla PHP with SQLite
- Static frontend assets
- Token voting system (frequent reads/writes)

Optimization areas:

1. Database query performance
2. Caching strategies (no Redis available)
3. Frontend asset optimization
4. Rate limiting implementation
5. Graceful degradation under load

Constraints:

- No advanced server configuration access
- Must remain fork-friendly and simple
- Educational value is important

Please suggest optimizations that teach good performance practices while staying within shared hosting limitations.

```

---

## 🎯 Project Planning Prompts

### Feature Roadmap Planning
```

Help me plan the roadmap for "Tindlekit - Inspired by The Andrej Effect" platform post-launch.

Current MVP features:

- Idea submission and voting
- Token-based leaderboard
- Admin moderation
- Educational vibe coding docs

Potential future features:

- User accounts and profiles
- Idea categories and tags
- Email notifications
- API for third-party integrations
- Mobile app
- Analytics dashboard

Consider:

1. Which features align with educational goals?
2. What maintains the "15-minute deployment" promise?
3. How to keep it fork-friendly as complexity grows?
4. Community-driven feature prioritization

Please suggest a roadmap that balances growth with simplicity, keeping the educational mission central.

```

---

## 💡 Pro Tips for Using These Prompts

**1. Context is Key**
- Always include relevant parts of the PRD
- Paste current code when asking for improvements
- Mention the educational goals and constraints

**2. Be Specific**
- Replace `[SPECIFIC_ENDPOINT]` with actual endpoint names
- Include error messages when debugging
- Specify your skill level for appropriate responses

**3. Iterate and Learn**
- Ask follow-up questions about implementation choices
- Request explanations of complex concepts
- Get suggestions for learning resources

**4. Share Results**
- Document successful prompts in your fork
- Improve prompts based on what works
- Contribute better prompts back to the community

---

🍀 *Remember: These prompts teach you to think in PRDs and structured development. Level up your product thinking, not just your coding!*
