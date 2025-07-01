# AI と開発者体験 with Flight

Flight は、より速く、より賢く、摩擦を少なくして構築するのを助けるものです。特に、AI 駆動のツールや現代の開発ワークフローで作業する場合です。このページでは、Flight がプロジェクトを AI で強化しやすくする方法、そしてフレームワークとスケルトンプロジェクトに組み込まれた新しい AI ヘルパーの使い方を説明します。

---

## AI-Ready by Default: The Skeleton Project

公式の [flightphp/skeleton](https://github.com/flightphp/skeleton) スターターには、以下の人気の AI コーディングアシスタントの指示と設定が含まれています：

- **GitHub Copilot**
- **Cursor**
- **Windsurf**

これらのツールは、プロジェクト固有の指示で事前に設定されているため、コードを書く際に最も関連性が高く、文脈を考慮した助けを得られます。つまり：

- AI アシスタントは、プロジェクトの目標、スタイル、要件を理解します
- すべての貢献者に対して一貫したガイダンスを提供します
- 文脈を説明する時間を減らし、構築する時間を増やします

> **なぜこれが重要ですか？**
>
> AI ツールがプロジェクトの意図と規約を知っている場合、機能のスキャフォールディング、コードのリファクタリング、一般的なミスの回避を助けてくれます。これにより、初日からあなた（とあなたのチーム）がより生産的になります。

---

## New AI Commands in Flight Core

_v3.16.0+_

Flight core には、プロジェクトを設定し、AI で導くのに役立つ 2 つの強力な CLI コマンドが含まれています：

### 1. `ai:init` — Connect to Your Favorite LLM Provider

このコマンドは、OpenAI、Grok、または Anthropic (Claude) などの LLM (Large Language Model) プロバイダーの資格情報を設定する手順を案内します。

**Example:**
```bash
php runway ai:init
```
プロバイダーの選択、API キーの入力、モデルの選択を求められます。これにより、プロジェクトを最新の AI サービスに簡単に接続できます—手動設定は不要です。

### 2. `ai:generate-instructions` — Project-Aware AI Coding Instructions

このコマンドは、プロジェクト固有の指示を AI コーディングアシスタント用に作成または更新します。プロジェクトの用途、使用するデータベース、チームの規模など、いくつかの簡単な質問をします。その後、LLM プロバイダーを使用して、調整された指示を生成します。

指示がすでに存在する場合、提供した回答を反映して更新します。これらの指示は自動的に以下に書き込まれます：
- `.github/copilot-instructions.md` (for Github Copilot)
- `.cursor/rules/project-overview.mdc` (for Cursor)
- `.windsurfrules` (for Windsurf)

**Example:**
```bash
php runway ai:generate-instructions
```

> **なぜこれが役立つのですか？**
>
> 最新のプロジェクト固有の指示があれば、AI ツールは次のようにできます：
> - より良いコードの提案を提供します
> - プロジェクトの独自のニーズを理解します
> - 新しい貢献者のオンボーディングを速めます
> - プロジェクトが進化するにつれて、摩擦と混乱を減らします

---

## Not Just for Building AI Apps

AI 駆動の機能（例：チャットボット、スマート API、または統合）を構築するために Flight を使用することもできますが、真の強みは、開発者として AI ツールをより良く活用する点にあります。それは：

- **生産性を向上させる** AI 支援コーディング
- **チームを揃える** 共有され、進化する指示
- **新しい貢献者のオンボーディングを容易にする**
- **構築に集中し、ツールとの戦いを避ける**

---

## Learn More & Get Started

- See the [Flight Skeleton](https://github.com/flightphp/skeleton) for a ready-to-go, AI-friendly starter
- Check out the rest of the [Flight documentation](/learn) for tips on building fast, modern PHP apps