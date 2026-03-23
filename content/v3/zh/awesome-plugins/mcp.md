# FlightPHP MCP 服务器

FlightPHP MCP 服务器为任何兼容 MCP 的 AI 编码助手提供即时、结构化的访问权限，访问整个 FlightPHP 文档——路由、中间件、插件、指南等。您的 AI 不会再幻觉 API 细节或猜测方法签名，而是按需获取真实文档。托管版本无需 API 密钥，无需安装。

访问 [Github 仓库](https://github.com/flightphp/mcp) 以获取完整源代码和详细信息。

## 快速开始

服务器已公开托管并可立即使用：

```
https://mcp.flightphp.com/mcp
```

只需将该 URL 添加到您的 AI 编码扩展中。无需注册，无需凭据。请参阅下面的 [IDE 配置](#ide--ai-extension-configuration) 部分，获取最受欢迎工具的复制粘贴配置。

## 功能概述

连接后，您的 AI 助手可以：

- **浏览所有可用文档** — 列出每个核心主题、指南和插件页面
- **获取任何文档页面** — 检索路由、中间件、请求、安全等完整内容
- **查找插件文档** — 获取 ActiveRecord、Session、Tracy、Runway 和所有其他官方插件的完整文档
- **遵循逐步指南** — 访问构建博客、REST API 和测试应用程序的完整演练
- **跨一切搜索** — 同时在核心文档、指南和插件中查找相关页面

### 关键点
- **零设置** — 托管服务器 `https://mcp.flightphp.com/mcp` 无需安装或 API 密钥。
- **始终最新** — 服务器从 [docs.flightphp.com](https://docs.flightphp.com) 实时获取文档，因此始终保持最新。
- **处处可用** — 任何支持 MCP Streamable HTTP 传输的工具均可连接。
- **可自托管** — 如果您喜欢，可以使用 PHP >= 8.1 和 Composer 运行自己的实例。

## IDE / AI 扩展配置

服务器使用 Streamable HTTP 传输。选择您的扩展并粘贴配置。

### Claude Code (CLI)

运行以下命令将其添加到您的项目：

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

或手动添加到项目的 `.mcp.json`：

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

添加到工作区中的 `.vscode/mcp.json`：

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

添加到 VS Code 的 `settings.json`：

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

添加到 `~/.continue/config.json`：

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

## 可用工具

MCP 服务器向您的 AI 助手暴露以下工具：

| 工具 | 描述 |
|------|-------------|
| `list_docs_pages` | 列出所有可用核心文档主题，包括 slugs 和描述 |
| `get_docs_page` | 通过主题 slug 获取核心文档页面（例如 `routing`、`middleware`、`security`） |
| `list_guide_pages` | 列出所有可用的逐步指南 |
| `get_guide_page` | 通过 slug 获取完整指南（例如 `blog`、`unit-testing`） |
| `list_plugin_pages` | 列出所有可用的插件和扩展页面 |
| `get_plugin_docs` | 通过 slug 获取完整插件文档（例如 `active-record`、`session`、`jwt`） |
| `search_docs` | 在所有文档、指南和插件中搜索关键词或主题 |
| `fetch_url` | 通过完整的 `docs.flightphp.com` URL 直接获取任何页面 |

## 自托管

更喜欢运行自己的实例？您需要 PHP >= 8.1 和 Composer。

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

服务器默认启动在 `http://0.0.0.0:8890/mcp`。更新您的 IDE 配置以指向本地地址：

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