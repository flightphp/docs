# AI & 开发者体验与 Flight

Flight 旨在帮助您更快、更智能地构建，并减少摩擦——尤其是在使用 AI 驱动工具和现代开发者工作流程时。本页介绍了 Flight 如何轻松地为您的项目注入 AI 动力，以及如何开始使用框架和骨架项目中内置的新 AI 助手。

---

## 默认支持 AI：骨架项目

官方 [flightphp/skeleton](https://github.com/flightphp/skeleton) 入门项目现在包含了流行 AI 编码助手的说明和配置：

- **GitHub Copilot**
- **Cursor**
- **Windsurf**

这些工具预先配置了项目特定的说明，因此您和您的团队可以在编码时获得最相关、基于上下文的帮助。这意味着：

- AI 助手了解您的项目目标、风格和要求
- 为所有贡献者提供一致的指导
- 减少解释上下文的时间，更多时间用于构建

> **为什么这很重要？**
>
> 当您的 AI 工具了解您的项目意图和约定时，它们可以帮助您构建功能、重构代码，并避免常见错误——从第一天起让您（和您的团队）更高效。

---

## Flight 核心中的新 AI 命令

_v3.16.0+_

Flight 核心现在包括两个强大的 CLI 命令，以帮助您使用 AI 设置和引导您的项目：

### 1. `ai:init` — 连接到您喜欢的 LLM 提供者

此命令将引导您设置 LLM（大型语言模型）提供者的凭据，例如 OpenAI、Grok 或 Anthropic（Claude）。

**示例：**
```bash
php runway ai:init
```
您将被提示选择您的提供者、输入您的 API 密钥并选择一个模型。这使得轻松地将您的项目连接到最新的 AI 服务——无需手动配置。

### 2. `ai:generate-instructions` — 项目感知 AI 编码说明

此命令帮助您创建或更新项目特定的 AI 编码助手说明。它会询问您几个关于您的项目的问题（例如它是做什么的、使用什么数据库、团队规模等），然后使用您的 LLM 提供者生成量身定制的说明。

如果您已经有说明，它将根据您提供的答案更新它们。这些说明将自动写入：
- `.github/copilot-instructions.md` (for Github Copilot)
- `.cursor/rules/project-overview.mdc` (for Cursor)
- `.windsurfrules` (for Windsurf)

**示例：**
```bash
php runway ai:generate-instructions
```

> **为什么这有帮助？**
>
> 通过最新的、特定项目的说明，您的 AI 工具可以：
> - 提供更好的代码建议
> - 了解您项目的独特需求
> - 帮助新贡献者更快上手
> - 减少摩擦和混淆，因为您的项目不断演进

---

## 不只是用于构建 AI 应用

虽然您绝对可以使用 Flight 来构建 AI 驱动的功能（如聊天机器人、智能 API 或集成），但真正的力量在于 Flight 如何帮助您作为开发者更好地使用 AI 工具。这是关于：

- **通过 AI 辅助编码提升生产力**
- **通过共享、不断演进的说明保持团队一致**
- **为新贡献者简化入职**
- **让您专注于构建，而非与工具斗争**

---

## 了解更多并入门

- 查看 [Flight Skeleton](https://github.com/flightphp/skeleton) 以获取一个即用、AI 友好的入门项目
- 查看 [Flight 文档](/learn) 的其余部分，以获取有关构建快速、现代 PHP 应用的提示