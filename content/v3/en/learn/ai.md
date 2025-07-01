# AI & Developer Experience with Flight

Flight is all about helping you build faster, smarter, and with less friction—especially when it comes to working with AI-powered tools and modern developer workflows. This page covers how Flight makes it easy to supercharge your projects with AI, and how to get started with the new AI helpers built right into the framework and skeleton project.

---

## AI-Ready by Default: The Skeleton Project

The official [flightphp/skeleton](https://github.com/flightphp/skeleton) starter now comes with instructions and configuration for popular AI coding assistants:

- **GitHub Copilot**
- **Cursor**
- **Windsurf**

These tools are pre-configured with project-specific instructions, so you and your team can get the most relevant, context-aware help as you code. This means:

- AI assistants understand your project's goals, style, and requirements
- Consistent guidance for all contributors
- Less time spent explaining context, more time building

> **Why does this matter?**
>
> When your AI tools know your project's intent and conventions, they can help you scaffold features, refactor code, and avoid common mistakes—making you (and your team) more productive from day one.

---

## New AI Commands in Flight Core

_v3.16.0+_

Flight core now includes two powerful CLI commands to help you set up and steer your project with AI:

### 1. `ai:init` — Connect to Your Favorite LLM Provider

This command walks you through setting up credentials for an LLM (Large Language Model) provider, such as OpenAI, Grok, or Anthropic (Claude).

**Example:**
```bash
php runway ai:init
```
You'll be prompted to select your provider, enter your API key, and choose a model. This makes it easy to connect your project to the latest AI services—no manual config required.

### 2. `ai:generate-instructions` — Project-Aware AI Coding Instructions

This command helps you create or update project-specific instructions for AI coding assistants. It asks you a few simple questions about your project (like what it's for, what database you use, team size, etc.), then uses your LLM provider to generate tailored instructions.

If you already have instructions, it will update them to reflect the answers you provide. These instructions are automatically written to:
- `.github/copilot-instructions.md` (for Github Copilot)
- `.cursor/rules/project-overview.mdc` (for Cursor)
- `.windsurfrules` (for Windsurf)

**Example:**
```bash
php runway ai:generate-instructions
```

> **Why is this helpful?**
>
> With up-to-date, project-specific instructions, your AI tools can:
> - Give better code suggestions
> - Understand your project's unique needs
> - Help onboard new contributors faster
> - Reduce friction and confusion as your project evolves

---

## Not Just for Building AI Apps

While you can absolutely use Flight to build AI-powered features (like chatbots, smart APIs, or integrations), the real power is in how Flight helps you work better with AI tools as a developer. It's about:

- **Boosting productivity** with AI-assisted coding
- **Keeping your team aligned** with shared, evolving instructions
- **Making onboarding easier** for new contributors
- **Letting you focus on building**, not fighting your tools

---

## Learn More & Get Started

- See the [Flight Skeleton](https://github.com/flightphp/skeleton) for a ready-to-go, AI-friendly starter
- Check out the rest of the [Flight documentation](/learn) for tips on building fast, modern PHP apps
