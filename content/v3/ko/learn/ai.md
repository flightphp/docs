# Flight와 함께하는 AI 및 개발자 경험

## 개요

Flight는 AI 기반 도구와 현대적인 개발자 워크플로우로 PHP 프로젝트를 쉽게 강화할 수 있게 해줍니다. LLM(대형 언어 모델) 제공자에 연결하는 내장 명령어와 프로젝트별 AI 코딩 지침을 생성하는 기능을 통해, Flight는 GitHub Copilot, Cursor, Windsurf와 같은 AI 어시스턴트에서 최대한의 이점을 얻도록 당신과 팀을 도와줍니다.

## 이해하기

AI 코딩 어시스턴트는 프로젝트의 맥락, 규칙, 목표를 이해할 때 가장 도움이 됩니다. Flight의 AI 도우미는 다음을 가능하게 합니다:
- 프로젝트를 인기 있는 LLM 제공자(OpenAI, Grok, Claude 등)에 연결
- AI 도구를 위한 프로젝트별 지침을 생성하고 업데이트하여 모두가 일관되고 관련성 있는 도움을 받도록 함
- 맥락 설명에 시간을 덜 들이고 팀을 일치시키며 생산성을 유지

이 기능들은 Flight 코어 CLI와 공식 [flightphp/skeleton](https://github.com/flightphp/skeleton) 시작 프로젝트에 내장되어 있습니다.

## 기본 사용법

### LLM 자격 증명 설정

`ai:init` 명령어는 프로젝트를 LLM 제공자에 연결하는 과정을 안내합니다.

```bash
php runway ai:init
```

다음으로 안내됩니다:
- 제공자 선택 (OpenAI, Grok, Claude 등)
- API 키 입력
- 기본 URL 및 모델 이름 설정

이로 인해 프로젝트 루트에 `.runway-creds.json` 파일이 생성되며 (`.gitignore`에 포함되도록 보장합니다).

**예시:**
```
Welcome to AI Init!
Which LLM API do you want to use? [1] openai, [2] grok, [3] claude: 1
Enter the base URL for the LLM API [https://api.openai.com]:
Enter your API key for openai: sk-...
Enter the model name you want to use (e.g. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credentials saved to .runway-creds.json
```

### 프로젝트별 AI 지침 생성

`ai:generate-instructions` 명령어는 프로젝트에 맞춤형 AI 코딩 어시스턴트 지침을 생성하거나 업데이트하는 데 도움을 줍니다.

```bash
php runway ai:generate-instructions
```

프로젝트에 대한 몇 가지 질문(설명, 데이터베이스, 템플릿, 보안, 팀 규모 등)에 답변합니다. Flight는 LLM 제공자를 사용하여 지침을 생성한 후 다음에 작성합니다:
- `.github/copilot-instructions.md` (GitHub Copilot용)
- `.cursor/rules/project-overview.mdc` (Cursor용)
- `.windsurfrules` (Windsurf용)

**예시:**
```
Please describe what your project is for? My awesome API
What database are you planning on using? MySQL
What HTML templating engine will you plan on using (if any)? latte
Is security an important element of this project? (y/n) y
...
AI instructions updated successfully.
```

이제 AI 도구는 프로젝트의 실제 요구사항에 기반한 더 스마트하고 관련성 있는 제안을 제공합니다.

## 고급 사용법

- 자격 증명 또는 지침 파일의 위치를 명령어 옵션을 사용하여 사용자 지정할 수 있습니다 (각 명령어의 `--help` 참조).
- AI 도우미는 OpenAI 호환 API를 지원하는 모든 LLM 제공자와 작동하도록 설계되었습니다.
- 프로젝트가 발전함에 따라 지침을 업데이트하려면 `ai:generate-instructions`를 다시 실행하고 프롬프트에 답변하세요.

## 관련 자료

- [Flight Skeleton](https://github.com/flightphp/skeleton) – AI 통합이 포함된 공식 시작 프로젝트
- [Runway CLI](/awesome-plugins/runway) – 이러한 명령어를 구동하는 CLI 도구에 대한 자세한 내용

## 문제 해결

- "Missing .runway-creds.json"이 보이면 먼저 `php runway ai:init`을 실행하세요.
- API 키가 유효하고 선택된 모델에 액세스할 수 있는지 확인하세요.
- 지침이 업데이트되지 않으면 프로젝트 디렉토리의 파일 권한을 확인하세요.

## 변경 로그

- v3.16.0 – AI 통합을 위한 `ai:init` 및 `ai:generate-instructions` CLI 명령어 추가.