# 使用 Flight 的 AI 与开发者体验

## 概述

Flight 让您轻松为 PHP 项目注入 AI 驱动工具和现代开发者工作流程。通过内置命令连接 LLM（大型语言模型）提供商并生成项目特定的 AI 编码指令，Flight 帮助您和您的团队充分利用 GitHub Copilot、Cursor、Windsurf 和 Antigravity (Gemini) 等 AI 助手。

## 理解

AI 编码助手在理解项目上下文、约定和目标时最有帮助。Flight 的 AI 助手让您能够：
- 将项目连接到流行的 LLM 提供商（OpenAI、Grok、Claude 等）
- 生成和更新项目特定的 AI 工具指令，确保每个人获得一致、相关的帮助
- 保持团队对齐和高效，减少解释上下文的时间

这些功能内置于 Flight 核心 CLI 和官方 [flightphp/skeleton](https://github.com/flightphp/skeleton) 启动项目中。

## 基本用法

### 设置 LLM 凭据

`ai:init` 命令将引导您将项目连接到 LLM 提供商。

```bash
php runway ai:init
```

您将被提示：
- 选择您的提供商（OpenAI、Grok、Claude 等）
- 输入您的 API 密钥
- 设置基础 URL 和模型名称

这将创建必要的凭据，以便您进行未来的 LLM 请求。

**示例：**
```
欢迎使用 AI Init！
您想使用哪个 LLM API？[1] openai, [2] grok, [3] claude: 1
输入 LLM API 的基础 URL [https://api.openai.com]:
输入您的 openai API 密钥: sk-...
输入您想使用的模型名称（例如 gpt-4, claude-3-opus 等）[gpt-4o]:
凭据已保存到 .runway-creds.json
```

### 生成项目特定的 AI 指令

`ai:generate-instructions` 命令帮助您创建或更新针对项目的 AI 编码助手指令。

```bash
php runway ai:generate-instructions
```

您将回答一些关于项目的问题（描述、数据库、模板、安全、团队规模等）。Flight 使用您的 LLM 提供商生成指令，然后将它们写入：
- `.github/copilot-instructions.md`（用于 GitHub Copilot）
- `.cursor/rules/project-overview.mdc`（用于 Cursor）
- `.windsurfrules`（用于 Windsurf）
- `.gemini/GEMINI.md`（用于 Antigravity）

**示例：**
```
请描述您的项目用途？我的awesome API
您计划使用什么数据库？MySQL
您计划使用什么 HTML 模板引擎（如果有的话）？latte
安全是否是此项目的重要元素？(y/n) y
...
AI 指令更新成功。
```

现在，您的 AI 工具将基于项目真实需求提供更智能、更相关的建议。

## 高级用法

- 您可以使用命令选项自定义凭据或指令文件的保存位置（每个命令查看 `--help`）。
- AI 助手设计为与支持 OpenAI 兼容 API 的任何 LLM 提供商配合使用。
- 如果您想随着项目演进而更新指令，只需重新运行 `ai:generate-instructions` 并再次回答提示。

## 另请参阅

- [Flight Skeleton](https://github.com/flightphp/skeleton) – 带有 AI 集成的官方启动项目
- [Runway CLI](/awesome-plugins/runway) – 更多关于驱动这些命令的 CLI 工具的信息

## 故障排除

- 如果看到“Missing .runway-creds.json”，请先运行 `php runway ai:init`。
- 确保您的 API 密钥有效并有权访问选定的模型。
- 如果指令未更新，请检查项目目录中的文件权限。

## 更新日志

- v3.16.0 – 添加了 `ai:init` 和 `ai:generate-instructions` CLI 命令以支持 AI 集成。