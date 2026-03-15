# Flight を使用した AI と開発者エクスペリエンス

## 概要

Flight は、AI 駆動のツールと現代的な開発者ワークフローを使用して PHP プロジェクトを強化することを容易にします。LLM (Large Language Model) プロバイダに接続するための組み込みコマンドと、プロジェクト固有の AI コーディング指示を生成するための機能により、Flight は GitHub Copilot、Cursor、Windsurf、Antigravity (Gemini) などの AI アシスタントを最大限に活用するのに役立ちます。

## 理解

AI コーディングアシスタントは、プロジェクトのコンテキスト、慣習、目標を理解しているときに最も役立ちます。Flight の AI ヘルパーは、以下のことを可能にします：
- プロジェクトを人気の LLM プロバイダ (OpenAI、Grok、Claude など) に接続
- AI ツール向けのプロジェクト固有の指示を生成および更新し、一貫性があり関連性の高いヘルプを全員に提供
- コンテキストの説明に費やす時間を減らし、チームを調整し生産性を維持

これらの機能は、Flight コア CLI と公式の [flightphp/skeleton](https://github.com/flightphp/skeleton) スタータープロジェクトに組み込まれています。

## 基本的な使用方法

### LLM 認証情報の設定

`ai:init` コマンドは、プロジェクトを LLM プロバイダに接続するための手順をガイドします。

```bash
php runway ai:init
```

以下のプロンプトが表示されます：
- プロバイダを選択 (OpenAI、Grok、Claude など)
- API キーを入力
- ベース URL とモデル名を設定

これにより、将来の LLM リクエストに必要な認証情報が作成されます。

**例:**
```
Welcome to AI Init!
Which LLM API do you want to use? [1] openai, [2] grok, [3] claude: 1
Enter the base URL for the LLM API [https://api.openai.com]:
Enter your API key for openai: sk-...
Enter the model name you want to use (e.g. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credentials saved to .runway-creds.json
```

### プロジェクト固有の AI 指示の生成

`ai:generate-instructions` コマンドは、プロジェクトに合わせた AI コーディングアシスタント向けの指示を作成または更新するのに役立ちます。

```bash
php runway ai:generate-instructions
```

プロジェクトの説明、データベース、テンプレート、セキュリティ、チームサイズなどについて数個の質問に答えます。Flight は LLM プロバイダを使用して指示を生成し、次のファイルに書き込みます：
- `.github/copilot-instructions.md` (GitHub Copilot 用)
- `.cursor/rules/project-overview.mdc` (Cursor 用)
- `.windsurfrules` (Windsurf 用)
- `.gemini/GEMINI.md` (Antigravity 用)

**例:**
```
Please describe what your project is for? My awesome API
What database are you planning on using? MySQL
What HTML templating engine will you plan on using (if any)? latte
Is security an important element of this project? (y/n) y
...
AI instructions updated successfully.
```

これで、AI ツールはプロジェクトの実ニーズに基づいた、より賢く関連性の高い提案を提供します。

## 高度な使用方法

- コマンドオプションを使用して認証情報や指示ファイルの場所をカスタマイズできます (各コマンドの `--help` を参照)。
- AI ヘルパーは、OpenAI 互換 API をサポートする任意の LLM プロバイダと連携するように設計されています。
- プロジェクトが進化したら指示を更新したい場合、`ai:generate-instructions` を再実行してプロンプトに再度答えてください。

## 関連項目

- [Flight Skeleton](https://github.com/flightphp/skeleton) – AI 統合付きの公式スターター
- [Runway CLI](/awesome-plugins/runway) – これらのコマンドを駆動する CLI ツールの詳細

## トラブルシューティング

- 「Missing .runway-creds.json」が表示された場合、まず `php runway ai:init` を実行してください。
- API キーが有効で、選択したモデルにアクセス可能であることを確認してください。
- 指示が更新されない場合、プロジェクトディレクトリのファイル権限を確認してください。

## 変更履歴

- v3.16.0 – AI 統合のための `ai:init` と `ai:generate-instructions` CLI コマンドを追加。