# FlightPHP MCP Server

FlightPHP MCP Server は、MCP 互換の AI コーディングアシスタントに、FlightPHP のドキュメント全体（ルーティング、ミドルウェア、プラグイン、ガイドなど）への即時で構造化されたアクセスを提供します。AI が API の詳細を幻覚したり、メソッドシグネチャを推測したりする代わりに、必要に応じて実際のドキュメントを取得します。API キー不要、ホスト版ではインストールも不要です。

完全なソースコードと詳細については、[Github repository](https://github.com/flightphp/mcp) をご覧ください。

## Quick Start

サーバーは公開ホストされており、すぐに使用可能です：

```
https://mcp.flightphp.com/mcp
```

この URL を AI コーディング拡張機能に追加するだけです。サインアップ不要、認証情報不要。人気のツール向けのコピー&ペースト設定については、以下の [IDE Configuration](#ide--ai-extension-configuration) セクションを参照してください。

## What It Does

接続後、AI アシスタントは以下の操作が可能です：

- **すべての利用可能なドキュメントを閲覧** — すべてのコアトピック、ガイド、プラグインページをリストアップ
- **任意のドキュメントページを取得** — ルーティング、ミドルウェア、リクエスト、セキュリティなどの完全なコンテンツを取得
- **プラグインドキュメントを検索** — ActiveRecord、Session、Tracy、Runway、およびその他の公式プラグインの完全なドキュメントを取得
- **ステップバイステップのガイドに従う** — ブログ、REST API、テスト済みアプリケーションの構築のための完全なウォークスルーにアクセス
- **すべてを横断的に検索** — コアドキュメント、ガイド、プラグイン全体で関連ページを一度に検索

### Key Points
- **ゼロセットアップ** — `https://mcp.flightphp.com/mcp` のホストサーバーはインストールや API キーが不要です。
- **常に最新** — サーバーは [docs.flightphp.com](https://docs.flightphp.com) からライブでドキュメントを取得するため、常に最新です。
- **どこでも動作** — MCP Streamable HTTP トランスポートをサポートする任意のツールで接続可能です。
- **セルフホスト可能** — PHP >= 8.1 と Composer で独自のインスタンスを実行できます。

## IDE / AI Extension Configuration

サーバーは Streamable HTTP トランスポートを使用します。以下の拡張機能を選択して設定を貼り付けてください。

### Claude Code (CLI)

プロジェクトに追加するには、以下のコマンドを実行します：

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

または、プロジェクトの `.mcp.json` に手動で追加します：

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

ワークスペースの `.vscode/mcp.json` に追加します：

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

VS Code の `settings.json` に追加します：

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

`~/.continue/config.json` に追加します：

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

MCP サーバーは、AI アシスタントに以下のツールを公開します：

| Tool | Description |
|------|-------------|
| `list_docs_pages` | すべての利用可能なコアドキュメントトピックをスラッグと説明付きでリストアップ |
| `get_docs_page` | トピックスラッグ（例: `routing`、`middleware`、`security`）でコアドキュメントページを取得 |
| `list_guide_pages` | すべての利用可能なステップバイステップガイドをリストアップ |
| `get_guide_page` | スラッグ（例: `blog`、`unit-testing`）で完全なガイドを取得 |
| `list_plugin_pages` | すべての利用可能なプラグインと拡張ページをリストアップ |
| `get_plugin_docs` | スラッグ（例: `active-record`、`session`、`jwt`）で完全なプラグインドキュメントを取得 |
| `search_docs` | すべてのドキュメント、ガイド、プラグイン全体でキーワードやトピックを検索 |
| `fetch_url` | 完全な `docs.flightphp.com` URL で任意のページを直接取得 |

## Self-Hosting

独自のインスタンスを実行したいですか？ PHP >= 8.1 と Composer が必要です。

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

サーバーはデフォルトで `http://0.0.0.0:8890/mcp` で開始します。IDE 設定をローカルアドレスに更新します：

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