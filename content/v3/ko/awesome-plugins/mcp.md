# FlightPHP MCP 서버

FlightPHP MCP 서버는 MCP 호환 AI 코딩 어시스턴트에 FlightPHP 문서 전체 — 라우팅, 미들웨어, 플러그인, 가이드 등 — 에 대한 즉시 구조화된 액세스를 제공합니다. AI가 API 세부 사항을 환각하거나 메서드 시그니처를 추측하는 대신, 필요에 따라 실제 문서를 가져옵니다. API 키가 필요 없으며, 호스팅 버전의 경우 설치가 필요 없습니다.

전체 소스 코드와 세부 사항을 위해 [Github 저장소](https://github.com/flightphp/mcp)를 방문하세요.

## 빠른 시작

서버는 공개적으로 호스팅되어 있으며 사용 준비가 되었습니다:

```
https://mcp.flightphp.com/mcp
```

이 URL을 AI 코딩 확장에 추가하기만 하면 됩니다. 가입이나 자격 증명이 필요 없습니다. 아래 [IDE 구성](#ide--ai-extension-configuration) 섹션에서 가장 인기 있는 도구에 대한 복사-붙여넣기 구성 옵션을 확인하세요.

## 기능

연결되면 AI 어시스턴트는 다음을 할 수 있습니다:

- **모든 사용 가능한 문서 탐색** — 모든 핵심 주제, 가이드 및 플러그인 페이지를 나열
- **문서 페이지 가져오기** — 라우팅, 미들웨어, 요청, 보안 등에 대한 전체 콘텐츠 검색
- **플러그인 문서 조회** — ActiveRecord, Session, Tracy, Runway 및 모든 다른 공식 플러그인에 대한 전체 문서 가져오기
- **단계별 가이드 따르기** — 블로그, REST API 및 테스트된 애플리케이션 구축을 위한 완전한 워크스루 액세스
- **모든 것 검색** — 핵심 문서, 가이드 및 플러그인 전반에서 관련 페이지를 한 번에 찾기

### 주요 포인트
- **설정 제로** — `https://mcp.flightphp.com/mcp`의 호스팅 서버는 설치나 API 키가 필요 없습니다.
- **항상 최신** — 서버는 [docs.flightphp.com](https://docs.flightphp.com)에서 실시간으로 문서를 가져오므로 항상 최신 상태입니다.
- **어디서나 작동** — MCP Streamable HTTP 전송을 지원하는 모든 도구가 연결할 수 있습니다.
- **셀프 호스팅 가능** — PHP >= 8.1 및 Composer를 사용해 자신의 인스턴스를 실행할 수 있습니다.

## IDE / AI 확장 구성

서버는 Streamable HTTP 전송을 사용합니다. 아래 확장을 선택하고 구성을 붙여넣으세요.

### Claude Code (CLI)

프로젝트에 추가하려면 다음 명령어를 실행하세요:

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

또는 프로젝트의 `.mcp.json`에 수동으로 추가하세요:

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

워크스페이스의 `.vscode/mcp.json`에 추가하세요:

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

VS Code `settings.json`에 추가하세요:

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

`~/.continue/config.json`에 추가하세요:

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

## 사용 가능한 도구

MCP 서버는 AI 어시스턴트에 다음 도구를 노출합니다:

| 도구 | 설명 |
|------|-------------|
| `list_docs_pages` | 모든 사용 가능한 핵심 문서 주제를 슬러그와 설명과 함께 나열 |
| `get_docs_page` | 주제 슬러그(예: `routing`, `middleware`, `security`)로 핵심 문서 페이지 가져오기 |
| `list_guide_pages` | 모든 사용 가능한 단계별 가이드 나열 |
| `get_guide_page` | 슬러그(예: `blog`, `unit-testing`)로 전체 가이드 가져오기 |
| `list_plugin_pages` | 모든 사용 가능한 플러그인 및 확장 페이지 나열 |
| `get_plugin_docs` | 슬러그(예: `active-record`, `session`, `jwt`)로 전체 플러그인 문서 가져오기 |
| `search_docs` | 모든 문서, 가이드 및 플러그인 전반에서 키워드나 주제 검색 |
| `fetch_url` | 전체 `docs.flightphp.com` URL로 직접 페이지 가져오기 |

## 셀프 호스팅

자신의 인스턴스를 실행하는 것을 선호하나요? PHP >= 8.1 및 Composer가 필요합니다.

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

서버는 기본적으로 `http://0.0.0.0:8890/mcp`에서 시작됩니다. IDE 구성을 로컬 주소로 업데이트하세요:

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