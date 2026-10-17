# FlightPHP APM 문서

FlightPHP APM에 오신 것을 환영합니다—여러분의 앱을 위한 개인 성능 코치입니다! 이 가이드는 FlightPHP로 APM(애플리케이션 성능 모니터링)을 설정하고, 사용하고, 마스터하기 위한 지침서입니다. 느린 요청을 찾아내거나 지연 시간 차트를 분석하고 싶다면, 이곳이 바로 그 해답입니다. 앱을 더 빠르게, 사용자를 더 행복하게, 디버깅 세션을 더 수월하게 만들어 봅시다!

Flight 문서 사이트의 대시보드 [데모](https://flightphp-docs-apm.sky-9.com/apm/dashboard)를 확인해 보세요.

![FlightPHP APM](/images/apm.png)

## APM이 왜 중요한가

이런 상황을 상상해 보세요: 여러분의 앱은 바쁜 레스토랑입니다. 주문이 얼마나 걸리는지, 주방이 어디에서 병목이 되는지 추적할 방법이 없다면, 왜 고객들이 불만을 품고 떠나는지 짐작만 할 수 있습니다. APM은 여러분의 서브 셰프와 같아서, 들어오는 요청부터 데이터베이스 쿼리까지 모든 단계를 지켜보고 속도를 늦추는 모든 것을 표시합니다. 느린 페이지는 사용자를 잃게 됩니다(연구에 따르면 사이트 로드 시간이 3초를 넘으면 53%가 이탈합니다!), 그리고 APM은 이러한 문제를 *문제가 되기 전에* 잡는 데 도움을 줍니다. 이는 사전 예방적인 마음의 평안입니다—"왜 이게 고장 났지?"라는 순간이 줄어들고, "이게 얼마나 매끄럽게 돌아가는지 봐!"라는 성공이 늘어납니다.

## 설치

Composer로 시작하세요:

```bash
composer require flightphp/apm
```

필요한 것들:
- **PHP 7.4+**: LTS Linux 배포판과 호환성을 유지하면서 최신 PHP도 지원합니다.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: 우리가 강화하고 있는 경량 프레임워크입니다.

## 지원되는 데이터베이스

FlightPHP APM은 현재 메트릭 저장을 위해 다음 데이터베이스를 지원합니다:

- **SQLite3**: 간단하고 파일 기반이며 로컬 개발이나 소규모 앱에 적합합니다. 대부분의 설정에서 기본 옵션입니다.
- **MySQL/MariaDB**: 견고하고 확장 가능한 스토리지가 필요한 대규모 프로젝트나 프로덕션 환경에 이상적입니다.

설정 단계에서 데이터베이스 유형을 선택할 수 있습니다(아래 참조). PHP 환경에 필요한 확장 프로그램(예: `pdo_sqlite` 또는 `pdo_mysql`)이 설치되어 있는지 확인하세요.

## 시작하기

APM을 최대한 활용하기 위한 단계별 가이드입니다:

### 1. APM 등록

`index.php` 또는 `services.php` 파일에 다음을 넣어 추적을 시작하세요:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\SimplePdo;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// 데이터베이스 연결을 추가하는 경우
// SimplePdo(또는 개발 시 Tracy Extensions의 PdoQueryCapture)를 권장합니다.
// 옵션 배열(5번째 인자)을 통해 APM 쿼리 추적을 활성화하세요.
$pdo = new SimplePdo('mysql:host=localhost;dbname=example', 'user', 'pass', null, [
	'trackApmQueries' => true, // APM용 쿼리 캡처에 필수
]);
$Apm->addPdoConnection($pdo);
```

**여기서 무슨 일이 일어나나요?**
- `LoggerFactory::create()`는 설정(곧 자세히 설명)을 가져와 로거를 설정합니다—기본적으로 SQLite입니다.
- `Apm`은 핵심입니다—Flight의 이벤트(요청, 라우트, 오류 등)를 수신하고 메트릭을 수집합니다.
- `bindEventsToFlightInstance($app)`는 이를 Flight 앱에 모두 연결합니다.

**프로 팁: 샘플링**
앱이 바쁘다면 *모든* 요청을 로깅하는 것은 과부하를 일으킬 수 있습니다. 샘플 비율(0.0 ~ 1.0)을 사용하세요:

```php
$Apm = new Apm($ApmLogger, 0.1); // 요청의 10%만 로깅
```

이렇게 하면 성능은 빠르게 유지하면서도 충분한 데이터를 얻을 수 있습니다.

### 2. 설정하기

다음을 실행하여 `.runway-config.json`을 생성하세요:

```bash
php vendor/bin/runway apm:init
```

**이것은 무엇을 하나요?**
- 원시 메트릭이 어디에서 오는지(소스)와 처리된 데이터가 어디로 가는지(대상)를 묻는 마법사를 실행합니다.
- 기본값은 SQLite입니다—예: 소스는 `sqlite:/tmp/apm_metrics.sqlite`, 대상도 SQLite입니다.
- 다음과 같은 설정이 생성됩니다:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> 이 과정에서 이 설정에 대한 마이그레이션을 실행할지도 물어봅니다. 처음 설정하는 경우라면 '예'라고 답하세요.

**왜 두 개의 위치인가요?**
원시 메트릭은 빠르게 쌓입니다(필터링되지 않은 로그라고 생각하세요). 워커가 이를 구조화된 대상으로 처리하여 대시보드에 제공합니다. 정리를 유지할 수 있습니다!

### 3. 워커로 메트릭 처리하기

워커는 원시 메트릭을 대시보드에서 사용할 수 있는 데이터로 변환합니다. 한 번 실행하세요:

```bash
php vendor/bin/runway apm:worker
```

**무엇을 하나요?**
- 소스(예: `apm_metrics.sqlite`)에서 읽습니다.
- 최대 100개의 메트릭(기본 배치 크기)을 대상으로 처리합니다.
- 완료되었거나 남은 메트릭이 없으면 중지됩니다.

**계속 실행하기**
실시간 앱의 경우 지속적인 처리가 필요합니다. 옵션은 다음과 같습니다:

- **데몬 모드**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  메트릭이 들어오는 대로 처리하며 계속 실행됩니다. 개발이나 소규모 설정에 적합합니다.

- **크론탭**:
  크론탭(`crontab -e`)에 다음을 추가하세요:
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  매 분 실행됩니다—프로덕션에 완벽합니다.

- **Tmux/Screen**:
  분리 가능한 세션을 시작하세요:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, 그리고 D를 눌러 분리; `tmux attach -t apm-worker`로 다시 연결
  ```
  로그아웃해도 계속 실행됩니다.

- **사용자 정의 조정**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: 한 번에 50개의 메트릭을 처리합니다.
  - `--max_messages 1000`: 1000개의 메트릭 후 중지합니다.
  - `--timeout 300`: 5분 후 종료합니다.

**왜 필요한가요?**
워커가 없으면 대시보드는 비어 있습니다. 원시 로그와 실행 가능한 인사이트 사이의 다리 역할을 합니다.

### 4. 대시보드 실행하기

앱의 핵심 지표를 확인하세요:

```bash
php vendor/bin/runway apm:dashboard
```

**이것은 무엇인가요?**
- `http://localhost:8001/apm/dashboard`에서 PHP 서버를 실행합니다.
- 요청 로그, 느린 라우트, 오류율 등을 보여줍니다.

**사용자 정의하기**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: 모든 IP에서 접근 가능(원격 열람에 편리).
- `--port 8080`: 8001이 사용 중이면 다른 포트를 사용합니다.
- `--php-path`: PHP가 PATH에 없으면 경로를 지정합니다.

브라우저에서 URL에 접속하여 탐색해 보세요!

#### 프로덕션 모드

프로덕션 환경에서는 방화벽 및 기타 보안 조치가 있을 수 있으므로 대시보드를 실행하기 위해 몇 가지 방법을 시도해야 할 수 있습니다. 몇 가지 옵션은 다음과 같습니다:

- **리버스 프록시 사용**: Nginx 또는 Apache를 설정하여 대시보드로 요청을 전달합니다.
- **SSH 터널**: 서버에 SSH로 접속할 수 있다면 `ssh -L 8080:localhost:8001 youruser@yourserver`를 사용하여 대시보드를 로컬 머신으로 터널링하세요.
- **VPN**: 서버가 VPN 뒤에 있다면 VPN에 연결하고 대시보드에 직접 접속하세요.
- **방화벽 설정**: 귀하의 IP 또는 서버 네트워크에 대해 포트 8001을 엽니다(또는 설정한 포트).
- **Apache/Nginx 설정**: 애플리케이션 앞에 웹 서버가 있다면 도메인이나 서브도메인에 연결하도록 구성할 수 있습니다. 해당 호스트를 `dashboard/index.php`를 가리키게 하세요(내장 서버가 이를 라우터로 사용합니다). 정적 파일은 `dashboard/css` 및 `dashboard/js`에 함께 위치합니다.
- **기본 인증**: runway 설정에 `apm.dashboard_auth.user`와 `apm.dashboard_auth.password`를 설정하세요. 둘 중 하나라도 비어 있으면 대시보드는 열려 있습니다. 이는 단일 공유 비밀번호이며 애플리케이션 사용자 계정이 아닙니다.

#### 다른 대시보드를 원하세요?

원한다면 직접 대시보드를 만들 수 있습니다! 자체 대시보드를 위한 데이터 표현 방법에 대한 아이디어는 vendor/flightphp/apm/src/apm/presenter 디렉토리를 참조하세요!

## 대시보드 기능

대시보드는 숫자, 차트, 요청 로그 순서로 열립니다.

- **통계 행**: 요청 수, 오류율, p95, p99, 평균 지연 시간, 분당 처리량, 캐시 적중률.
- **워커 백로그**: `apm_metrics_log`에 아직 남아 있는 원시 메트릭 수. 높은 숫자는 워커가 지연되고 있음을 의미합니다. 백로그가 0인 빈 대시보드는 수집된 것이 없음을 의미합니다.
- **지연 시간 차트**: 동일한 버킷에 대한 평균 지연 시간과 요청 수. 지난 1시간은 5분 버킷, 지난 하루는 15분 버킷, 지난 주는 6시간 버킷을 사용합니다. 사용자 정의 범위는 약 40개의 포인트를 유지하는 버킷을 선택합니다. 시간은 UTC로 저장되며 선택한 시간대에 맞게 그려집니다.
- **상태 차트**: `2xx`, `3xx`, `4xx`, `5xx` 개수를 누적하여 표시합니다. 시리즈를 클릭하면 해당 클래스로 로그가 필터링됩니다.
- **느린 라우트, 미들웨어, 쿼리 및 요청**: 각각 5개입니다. 쿼리는 `WHERE id = 1`과 `WHERE id = 2`가 하나의 문장으로 계산되도록 그룹화됩니다. 라우트, 느린 요청 또는 오류 그룹을 클릭하면 로그가 필터링됩니다.
- **사용자 정의 이벤트**: 앱이 발생시키는 모든 사용자 정의 이벤트 유형과 그 개수, 마지막으로 확인된 시간, 선택한 기간에 대한 작은 추세선. 개수별로 정렬됩니다. 시간 범위, 봇 숨기기 및 기타 필터를 적용합니다. 유형을 클릭하면 로그가 해당 유형으로 필터링됩니다.
- **요청 로그**: 메서드, URL, 상태, 시간, 메모리, 호스트, 봇 플래그. 행을 열면 세부 정보가 로드되며 타이밍 바, 뷰, 쿼리 매개변수, 오류 추적 및 사용자 정의 이벤트가 포함됩니다.
- **봇 숨기기**: 집계 및 로그에서 봇 트래픽을 제외합니다. 검색 크롤러와 LLM 크롤러(GPTBot, ClaudeBot, PerplexityBot, SearxNG 등)는 봇으로 표시됩니다.
- **사용자 정의 범위**: 지난 1시간, 하루, 주 외에도 시작-종료 기간을 선택할 수 있습니다.
- **필터**: URL, 상태, 요청 ID, 라우트, IP, 호스트, 세션, 사용자 에이전트, 사용자 정의 이벤트 유형, 이벤트 데이터 및 오류 메시지. 쿼리 문자열은 새로 고침 후에도 이를 유지합니다.

**예시**:
`/users`에 대한 요청은 다음을 보여줄 수 있습니다:
- 총 시간: 150ms
- 미들웨어: `AuthMiddleware->handle` (50ms)
- 쿼리: `SELECT * FROM users` (80ms)
- 뷰 렌더링 시간
- 캐시: `user_list`에서 적중 (5ms)

**예시**:
`/users`에 대한 요청은 다음을 보여줄 수 있습니다:
- 총 시간: 150ms
- 미들웨어: `AuthMiddleware->handle` (50ms)
- 쿼리: `SELECT * FROM users` (80ms)
- 캐시: `user_list`에서 적중 (5ms)

## 사용자 정의 이벤트 추가하기

API 호출이나 결제 프로세스와 같은 모든 것을 추적하세요:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**어디에 표시되나요?**
대시보드의 요청 세부 정보의 "Custom Events" 아래에 표시되며, 예쁜 JSON 형식으로 펼칠 수 있습니다. **Custom events** 패널은 각 유형의 개수도 계산하므로, 간단한 `docs.markdown_served` 이벤트만으로 "이게 얼마나 자주 발생하나요?"라는 질문에 답할 수 있습니다.

**사용 사례**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
이제 해당 API가 앱을 느리게 만드는지 확인할 수 있습니다!

## 데이터베이스 모니터링

PDO 쿼리를 다음과 같이 추적하세요:

```php
use flight\database\SimplePdo;

$pdo = new SimplePdo('sqlite:/path/to/db.sqlite', null, null, null, [
	'trackApmQueries' => true, // APM용 쿼리 캡처에 필수
]);
$Apm->addPdoConnection($pdo);
```

**얻을 수 있는 것**:
- 쿼리 텍스트 (예: `SELECT * FROM users WHERE id = ?`)
- 실행 시간 (예: 0.015초)
- 행 수 (예: 42)

**주의 사항**:
- **선택 사항**: DB 추적이 필요 없으면 건너뛰세요.
- **SimplePdo(권장)**: `