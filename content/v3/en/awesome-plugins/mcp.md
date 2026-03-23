# FlightPHP MCP Server

The FlightPHP MCP Server gives any MCP-compatible AI coding assistant instant, structured access to the entire FlightPHP documentation — routing, middleware, plugins, guides, and more. Instead of your AI hallucinating API details or guessing at method signatures, it fetches the real docs on demand. No API keys, no installation required for the hosted version.

Visit the [Github repository](https://github.com/flightphp/mcp) for the full source code and details.

## Quick Start

The server is publicly hosted and ready to use:

```
https://mcp.flightphp.com/mcp
```

Just add that URL to your AI coding extension. No signup, no credentials. See the [IDE Configuration](#ide--ai-extension-configuration) section below for copy-paste configs for the most popular tools.

## What It Does

Once connected, your AI assistant can:

- **Browse all available docs** — list every core topic, guide, and plugin page
- **Fetch any documentation page** — retrieve full content for routing, middleware, requests, security, and more
- **Look up plugin docs** — get full documentation for ActiveRecord, Session, Tracy, Runway, and all other official plugins
- **Follow step-by-step guides** — access complete walkthroughs for building blogs, REST APIs, and tested applications
- **Search across everything** — find relevant pages across core docs, guides, and plugins at once

### Key Points
- **Zero setup** — the hosted server at `https://mcp.flightphp.com/mcp` requires no installation or API keys.
- **Always current** — the server fetches docs live from [docs.flightphp.com](https://docs.flightphp.com), so it's always up to date.
- **Works everywhere** — any tool that supports the MCP Streamable HTTP transport can connect.
- **Self-hostable** — run your own instance with PHP >= 8.1 and Composer if you prefer.

## IDE / AI Extension Configuration

The server uses Streamable HTTP transport. Pick your extension below and paste in the config.

### Claude Code (CLI)

Run the following command to add it to your project:

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

Or add it manually to your project's `.mcp.json`:

```json
{
  "mcpServers": {
    "flightphp-docs": {
      "type": "http",
      "url": "https://mcp.flightphp.com/mcp"
    }
  }
}
```

### GitHub Copilot (VS Code)

Add to `.vscode/mcp.json` in your workspace:

```json
{
  "servers": {
    "flightphp-docs": {
      "type": "http",
      "url": "https://mcp.flightphp.com/mcp"
    }
  }
}
```

### Kilo Code (VS Code)

Add to your VS Code `settings.json`:

```json
{
  "kilocode.mcpServers": {
    "flightphp-docs": {
      "url": "https://mcp.flightphp.com/mcp",
      "transport": "streamable-http"
    }
  }
}
```

### Continue.dev (VS Code / JetBrains)

Add to `~/.continue/config.json`:

```json
{
  "mcpServers": [
    {
      "name": "flightphp-docs",
      "transport": {
        "type": "http",
        "url": "https://mcp.flightphp.com/mcp"
      }
    }
  ]
}
```

## Available Tools

The MCP server exposes the following tools to your AI assistant:

| Tool | Description |
|------|-------------|
| `list_docs_pages` | Lists all available core documentation topics with slugs and descriptions |
| `get_docs_page` | Fetches a core docs page by topic slug (e.g. `routing`, `middleware`, `security`) |
| `list_guide_pages` | Lists all available step-by-step guides |
| `get_guide_page` | Fetches a full guide by slug (e.g. `blog`, `unit-testing`) |
| `list_plugin_pages` | Lists all available plugin and extension pages |
| `get_plugin_docs` | Fetches full plugin documentation by slug (e.g. `active-record`, `session`, `jwt`) |
| `search_docs` | Searches across all docs, guides, and plugins for a keyword or topic |
| `fetch_url` | Fetches any page directly by its full `docs.flightphp.com` URL |

## Self-Hosting

Prefer to run your own instance? You'll need PHP >= 8.1 and Composer.

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

The server starts on `http://0.0.0.0:8890/mcp` by default. Update your IDE config to point at your local address:

```json
{
  "mcpServers": {
    "flightphp-docs": {
      "type": "http",
      "url": "http://localhost:8890/mcp"
    }
  }
}
```
