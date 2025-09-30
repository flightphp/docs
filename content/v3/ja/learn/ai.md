# Flight での AI と開発者エクスペリエンス

## 概要

Flight は、AI 駆動のツールと現代的な開発者ワークフローで PHP プロジェクトを強化することを容易にします。LLM (Large Language Model) プロバイダへの接続のための組み込みコマンドと、プロジェクト固有の AI コーディング指示を生成するためのコマンドにより、Flight は GitHub Copilot、Cursor、Windsurf などの AI アシスタントを最大限に活用するのに役立ちます。

## 理解

AI コーディングアシスタントは、プロジェクトのコンテキスト、慣習、目標を理解しているときに最も役立ちます。Flight の AI ヘルパーを使用すると、次のことができます：
- プロジェクトを人気の LLM プロバイダ (OpenAI、Grok、Claude など) に接続
- AI ツール向けのプロジェクト固有の指示を生成および更新し、一貫性のある関連性の高いヘルプを提供
- チームを揃え、生産性を維持し、コンテキストの説明に費やす時間を減らす

これらの機能は、Flight コア CLI と公式の [flightphp/skeleton](https://github.com/flightphp/skeleton) スタータープロジェクトに組み込まれています。

## 基本的な使用方法

### 1. LLM 認証情報の設定

`ai:init` コマンドは、プロジェクトを LLM プロバイダに接続するための手順を案内します。

```bash
php runway ai:init
```

プロンプトで次のように求められます：
- プロバイダを選択 (OpenAI、Grok、Claude など)
- API キーを入力
- ベース URL とモデル名を設定

これにより、プロジェクトルートに `.runway-creds.json` ファイルが作成され (`.gitignore` に追加されることを確認)、。

**例:**
```
Welcome to AI Init!
どの LLM API を使用しますか？ [1] openai, [2] grok, [3] claude: 1
LLM API のベース URL を入力 [https://api.openai.com]:
openai の API キーを入力: sk-...
使用するモデル名を入力 (例: gpt-4, claude-3-opus など) [gpt-4o]:
.runway-creds.json に認証情報を保存しました
```

### 2. プロジェクト固有の AI 指示の生成

`ai:generate-instructions` コマンドは、プロジェクトに合わせて調整された AI コーディングアシスタント向けの指示を作成または更新するのに役立ちます。

```bash
php runway ai:generate-instructions
```

プロジェクトについて (説明、データベース、テンプレート、セキュリティ、チームサイズなど) のいくつかの質問に答えます。Flight は LLM プロバイダを使用して指示を生成し、次のファイルに書き込みます：
- `.github/copilot-instructions.md` (GitHub Copilot 用)
- `.cursor/rules/project-overview.mdc` (Cursor 用)
- `.windsurfrules` (Windsurf 用)

**例:**
```
プロジェクトの目的を説明してください？ My awesome API
どのデータベースを使用する予定ですか？ MySQL
どの HTML テンプレートエンジンを使用する予定ですか (該当する場合)？ latte
このプロジェクトでセキュリティが重要な要素ですか？ (y/n) y
...
AI 指示を正常に更新しました。
```

これで、AI ツールはプロジェクトの実ニーズに基づいた、より賢く関連性の高い提案を提供します。

## 高度な使用方法

- コマンドオプションを使用して、認証情報や指示ファイルの場所をカスタマイズできます (各コマンドの `--help` を参照)。
- AI ヘルパーは、OpenAI 互換 API をサポートする任意の LLM プロバイダで動作するように設計されています。
- プロジェクトが進化したら指示を更新したい場合、`ai:generate-instructions` を再実行してプロンプトに答えてください。

## 関連項目

- [Flight Skeleton](https://github.com/flightphp/skeleton) – AI 統合付きの公式スターター
- [Runway CLI](/awesome-plugins/runway) – これらのコマンドを駆動する CLI ツールの詳細

## トラブルシューティング

- 「Missing .runway-creds.json」が表示された場合、まず `php runway ai:init` を実行してください。
- API キーが有効で、選択したモデルにアクセス可能であることを確認してください。
- 指示が更新されない場合、プロジェクトディレクトリのファイルパーミッションを確認してください。

## 変更履歴

- v3.16.0 – AI 統合のための `ai:init` と `ai:generate-instructions` CLI コマンドを追加。