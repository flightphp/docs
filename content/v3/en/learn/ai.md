# AI & Developer Experience with Flight

## Overview

Flight makes it easy to supercharge your PHP projects with AI-powered tools and modern developer workflows. With built-in commands for connecting to LLM (Large Language Model) providers and generating project-specific AI coding instructions, Flight helps you and your team get the most out of AI assistants like GitHub Copilot, Cursor, and Windsurf.

## Understanding

AI coding assistants are most helpful when they understand your project's context, conventions, and goals. Flight's AI helpers let you:
- Connect your project to popular LLM providers (OpenAI, Grok, Claude, etc.)
- Generate and update project-specific instructions for AI tools, so everyone gets consistent, relevant help
- Keep your team aligned and productive, with less time spent explaining context

These features are built into the Flight core CLI and the official [flightphp/skeleton](https://github.com/flightphp/skeleton) starter project.

## Basic Usage

### Setting Up LLM Credentials

The `ai:init` command walks you through connecting your project to an LLM provider.

```bash
php runway ai:init
```

You'll be prompted to:
- Choose your provider (OpenAI, Grok, Claude, etc.)
- Enter your API key
- Set the base URL and model name

This creates a `.runway-creds.json` file in your project root (and ensures it's in your `.gitignore`).

**Example:**
```
Welcome to AI Init!
Which LLM API do you want to use? [1] openai, [2] grok, [3] claude: 1
Enter the base URL for the LLM API [https://api.openai.com]:
Enter your API key for openai: sk-...
Enter the model name you want to use (e.g. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credentials saved to .runway-creds.json
```

### Generating Project-Specific AI Instructions

The `ai:generate-instructions` command helps you create or update instructions for AI coding assistants, tailored to your project.

```bash
php runway ai:generate-instructions
```

You'll answer a few questions about your project (description, database, templating, security, team size, etc.). Flight uses your LLM provider to generate instructions, then writes them to:
- `.github/copilot-instructions.md` (for GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (for Cursor)
- `.windsurfrules` (for Windsurf)

**Example:**
```
Please describe what your project is for? My awesome API
What database are you planning on using? MySQL
What HTML templating engine will you plan on using (if any)? latte
Is security an important element of this project? (y/n) y
...
AI instructions updated successfully.
```

Now, your AI tools will give smarter, more relevant suggestions based on your project's real needs.

## Advanced Usage

- You can customize the location of your credentials or instructions files using command options (see `--help` for each command).
- The AI helpers are designed to work with any LLM provider that supports OpenAI-compatible APIs.
- If you want to update your instructions as your project evolves, just rerun `ai:generate-instructions` and answer the prompts again.

## See Also

- [Flight Skeleton](https://github.com/flightphp/skeleton) – The official starter with AI integration
- [Runway CLI](/awesome-plugins/runway) – More about the CLI tool powering these commands

## Troubleshooting

- If you see "Missing .runway-creds.json", run `php runway ai:init` first.
- Make sure your API key is valid and has access to the selected model.
- If instructions aren't updating, check file permissions in your project directory.

## Changelog

- v3.16.0 – Added `ai:init` and `ai:generate-instructions` CLI commands for AI integration.
