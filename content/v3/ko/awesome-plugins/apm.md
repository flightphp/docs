# FlightPHP APM 문서

FlightPHP APM에 오신 것을 환영합니다—앱의 개인 성능 코치입니다! 이 가이드는 FlightPHP와 함께 Application Performance Monitoring (APM)을 설정, 사용, 마스터하는 로드맵입니다. 느린 요청을 추적하든 지연 차트에 몰두하든, 모든 것을 다루겠습니다. 앱을 더 빠르게, 사용자를 더 행복하게, 디버깅 세션을 쉽게 만들어 보겠습니다!

Flight Docs 사이트의 대시보드 데모를 [여기](https://flightphp-docs-apm.sky-9.com/apm/dashboard)에서 확인하세요.

![FlightPHP APM](/images/apm.png)

## APM이 중요한 이유

이 장면을 상상해 보세요: 앱이 바쁜 레스토랑입니다. 주문 처리 시간이 얼마나 걸리는지나 주방이 어디서 지연되는지 추적할 방법이 없으면, 고객이 왜 불만족스럽게 떠나는지 추측할 수밖에 없습니다. APM은 부주방장 역할을 합니다—들어오는 요청부터 데이터베이스 쿼리까지 모든 단계를 감시하고, 속도를 늦추는 것을 플래그합니다. 느린 페이지는 사용자를 잃게 만듭니다 (연구에 따르면 사이트 로딩이 3초 이상 걸리면 53%가 이탈합니다!), APM은 이러한 문제를 *미리* 잡아줍니다. 사전적 안심—더 적은 "이게 왜 고장 났지?" 순간, 더 많은 "이게 얼마나 부드럽게 작동하나 봐!" 승리입니다.

## 설치

Composer로 시작하세요:

```bash
composer require flightphp/apm
```

필요한 것:
- **PHP 7.4+**: LTS Linux 배포판과 호환성을 유지하면서 현대 PHP를 지원합니다.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: 우리가 강화하는 경량 프레임워크입니다.

## 지원 데이터베이스

FlightPHP APM은 현재 메트릭을 저장하기 위해 다음 데이터베이스를 지원합니다:

- **SQLite3**: 간단하고 파일 기반으로, 로컬 개발이나 작은 앱에 적합합니다. 대부분의 설정에서 기본 옵션입니다.
- **MySQL/MariaDB**: 더 큰 프로젝트나 프로덕션 환경에 이상적이며, 강력하고 확장 가능한 저장소를 필요로 합니다.

구성 단계(아래 참조)에서 데이터베이스 유형을 선택할 수 있습니다. PHP 환경에 필요한 확장(pdo_sqlite 또는 pdo_mysql 등)이 설치되어 있는지 확인하세요.

## 시작하기

APM의 멋진 단계별 가이드입니다:

### 1. APM 등록

추적을 시작하기 위해 `index.php` 또는 `services.php` 파일에 다음을 추가하세요:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\PdoWrapper;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// 데이터베이스 연결을 추가하는 경우
// Tracy Extensions의 PdoWrapper 또는 PdoQueryCapture여야 합니다
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- APM 추적을 활성화하려면 True가 필요합니다.
$Apm->addPdoConnection($pdo);
```

**여기서 무슨 일이 일어나나요?**
- `LoggerFactory::create()`는 구성(곧 자세히 설명)을 가져와 로거를 설정합니다—기본적으로 SQLite입니다.
- `Apm`은 스타입니다—Flight의 이벤트(요청, 라우트, 오류 등)를 듣고 메트릭을 수집합니다.
- `bindEventsToFlightInstance($app)`은 모든 것을 Flight 앱에 연결합니다.

**프로 팁: 샘플링**
앱이 바쁘면 *모든* 요청을 로깅하면 과부하가 올 수 있습니다. 샘플 레이트(0.0에서 1.0)를 사용하세요:

```php
$Apm = new Apm($ApmLogger, 0.1); // 요청의 10%를 로깅합니다
```

이렇게 하면 성능을 유지하면서도 견고한 데이터를 얻을 수 있습니다.

### 2. 구성

`.runway-config.json`을 생성하려면 다음을 실행하세요:

```bash
php vendor/bin/runway apm:init
```

**이게 뭘 하나요?**
- 원시 메트릭의 출처(소스)와 처리된 데이터의 목적지(대상)를 묻는 마법사를 시작합니다.
- 기본은 SQLite—예: 소스에 `sqlite:/tmp/apm_metrics.sqlite`, 대상에 다른 하나.
- 다음과 같은 구성으로 끝납니다:
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

> 이 과정에서 이 설정에 대한 마이그레이션을 실행할지 물어봅니다. 처음 설정하는 경우 답은 yes입니다.

**왜 두 위치인가요?**
원시 메트릭은 빠르게 쌓입니다(필터링되지 않은 로그를 생각하세요). 워커가 이를 구조화된 대상으로 처리하여 대시보드를 위한 것입니다. 깔끔하게 유지합니다!

### 3. 워커로 메트릭 처리

워커는 원시 메트릭을 대시보드 준비 데이터로 변환합니다. 한 번 실행하세요:

```bash
php vendor/bin/runway apm:worker
```

**무엇을 하나요?**
- 소스(예: `apm_metrics.sqlite`)에서 읽습니다.
- 최대 100개 메트릭(기본 배치 크기)을 대상으로 처리합니다.
- 완료되거나 메트릭이 없을 때 중지합니다.

**지속 실행**
라이브 앱의 경우 지속적인 처리를 원할 것입니다. 옵션은 다음과 같습니다:

- **데몬 모드**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  영원히 실행되며, 메트릭이 오면 처리합니다. 개발이나 작은 설정에 좋습니다.

- **Crontab**:
  크론탭(`crontab -e`)에 추가하세요:
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  매분 실행—프로덕션에 완벽합니다.

- **Tmux/Screen**:
  분리 가능한 세션을 시작하세요:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, 그 다음 D로 분리; `tmux attach -t apm-worker`로 재연결
  ```
  로그아웃해도 실행됩니다.

- **커스텀 조정**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: 한 번에 50개 메트릭 처리.
  - `--max_messages 1000`: 1000개 메트릭 후 중지.
  - `--timeout 300`: 5분 후 종료.

**왜 신경 쓰나요?**
워커 없이 대시보드는 비어 있습니다. 원시 로그와 실행 가능한 인사이트 사이의 다리입니다.

### 4. 대시보드 실행

앱의 생체 신호를 확인하세요:

```bash
php vendor/bin/runway apm:dashboard
```

**이게 뭔가요?**
- `http://localhost:8001/apm/dashboard`에서 PHP 서버를 시작합니다.
- 요청 로그, 느린 라우트, 오류 비율 등을 보여줍니다.

**커스터마이징**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: 모든 IP에서 접근 가능(원격 보기 편함).
- `--port 8080`: 8001이 사용 중이면 다른 포트 사용.
- `--php-path`: PATH에 없으면 PHP를 가리키세요.

브라우저에서 URL을 열고 탐색하세요!

#### 프로덕션 모드

프로덕션에서 대시보드를 실행하려면 방화벽과 기타 보안 조치로 인해 몇 가지 기술을 시도해야 할 수 있습니다. 몇 가지 옵션:

- **리버스 프록시 사용**: Nginx나 Apache를 설정하여 요청을 대시보드로 전달.
- **SSH 터널**: 서버에 SSH로 접속할 수 있으면 `ssh -L 8080:localhost:8001 youruser@yourserver`를 사용하여 대시보드를 로컬 머신으로 터널링.
- **VPN**: 서버가 VPN 뒤에 있으면 연결 후 직접 접근.
- **방화벽 구성**: 8001 포트를 IP나 서버 네트워크에 열기(또는 설정한 포트).
- **Apache/Nginx 구성**: 애플리케이션 앞에 웹 서버가 있으면 도메인이나 서브도메인으로 구성. 이 경우 문서 루트를 `/path/to/your/project/vendor/flightphp/apm/dashboard`로 설정.

#### 다른 대시보드를 원하나요?

원하는 대시보드를 직접 만들 수 있습니다! 데이터를 제시하는 방법에 대한 아이디어를 위해 vendor/flightphp/apm/src/apm/presenter 디렉토리를 확인하세요!

## 대시보드 기능

대시보드는 APM 본부입니다—여기서 볼 수 있는 것:

- **요청 로그**: 타임스탬프, URL, 응답 코드, 총 시간과 함께 모든 요청. "Details"를 클릭하여 미들웨어, 쿼리, 오류 확인.
- **가장 느린 요청**: 시간 많이 소모하는 상위 5개 요청(예: "/api/heavy" 2.5s).
- **가장 느린 라우트**: 평균 시간 기준 상위 5개 라우트—패턴 발견에 좋음.
- **오류 비율**: 실패한 요청 비율(예: 2.3% 500s).
- **지연 백분위**: 95번째(p95)와 99번째(p99) 응답 시간—최악 시나리오 알기.
- **응답 코드 차트**: 시간에 따른 200s, 404s, 500s 시각화.
- **긴 쿼리/미들웨어**: 느린 데이터베이스 호출과 미들웨어 레이어 상위 5개.
- **캐시 히트/미스**: 캐시가 얼마나 자주 도움이 되는지.

**추가**:
- "Last Hour," "Last Day," 또는 "Last Week"로 필터.
- 늦은 밤 세션에 다크 모드 토글.

**예시**:
`/users` 요청은 다음과 같이 보일 수 있음:
- 총 시간: 150ms
- 미들웨어: `AuthMiddleware->handle` (50ms)
- 쿼리: `SELECT * FROM users` (80ms)
- 캐시: `user_list` 히트 (5ms)

## 커스텀 이벤트 추가

API 호출이나 결제 프로세스처럼 아무거나 추적:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**어디에 나타나나요?**
대시보드의 요청 세부 정보에서 "Custom Events" 아래—예쁜 JSON 형식으로 확장 가능.

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
이제 그 API가 앱을 끌어내리는지 볼 수 있습니다!

## 데이터베이스 모니터링

PDO 쿼리를 이렇게 추적:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- APM 추적을 활성화하려면 True가 필요합니다.
$Apm->addPdoConnection($pdo);
```

**얻는 것**:
- 쿼리 텍스트(예: `SELECT * FROM users WHERE id = ?`)
- 실행 시간(예: 0.015s)
- 행 수(예: 42)

**주의**:
- **선택적**: DB 추적이 필요 없으면 건너뛰기.
- **PdoWrapper만**: 코어 PDO는 아직 연결되지 않음—기다려 주세요!
- **성능 경고**: DB 중심 사이트에서 모든 쿼리를 로깅하면 느려질 수 있음. 부하를 줄이기 위해 샘플링(`$Apm = new Apm($ApmLogger, 0.1)`) 사용.

**예시 출력**:
- 쿼리: `SELECT name FROM products WHERE price > 100`
- 시간: 0.023s
- 행: 15

## 워커 옵션

워커를 원하는 대로 조정:

- `--timeout 300`: 5분 후 중지—테스트에 좋음.
- `--max_messages 500`: 500개 메트릭으로 제한—유한하게 유지.
- `--batch_size 200`: 한 번에 200개 처리—속도와 메모리 균형.
- `--daemon`: 중단 없이 실행—라이브 모니터링에 이상적.

**예시**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
한 시간 동안 실행되며, 한 번에 100개 메트릭 처리.

## 앱의 요청 ID

각 요청은 추적을 위한 고유 요청 ID를 가집니다. 앱에서 이 ID를 사용하여 로그와 메트릭을 상관관계지을 수 있습니다. 예를 들어 오류 페이지에 요청 ID를 추가:

```php
Flight::map('error', function($message) {
	// 응답 헤더 X-Flight-Request-Id에서 요청 ID 가져오기
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// 추가로 Flight 변수에서 가져올 수 있음
	// swoole나 다른 비동기 플랫폼에서는 이 방법이 잘 작동하지 않음.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## 업그레이드

APM의 최신 버전으로 업그레이드할 때 데이터베이스 마이그레이션이 필요할 수 있습니다. 다음 명령으로 실행:

```bash
php vendor/bin/runway apm:migrate
```
이것은 데이터베이스 스키마를 최신 버전으로 업데이트하기 위해 필요한 모든 마이그레이션을 실행합니다.

**참고:** APM 데이터베이스가 크면 이 마이그레이션은 시간이 걸릴 수 있습니다. 피크 시간 외에 실행하는 것이 좋습니다.

### 0.4.3 -> 0.5.0 업그레이드

0.4.3에서 0.5.0으로 업그레이드할 때 다음 명령을 실행:

```bash
php vendor/bin/runway apm:config-migrate
```

이것은 `.runway-config.json` 파일을 사용하는 이전 형식에서 `config.php` 파일에 키/값을 저장하는 새 형식으로 구성을 마이그레이션합니다.

## 오래된 데이터 삭제

데이터베이스를 깔끔하게 유지하려면 오래된 데이터를 삭제할 수 있습니다. 특히 바쁜 앱을 실행 중이고 데이터베이스 크기를 관리하려면 유용합니다.
다음 명령으로 실행:

```bash
php vendor/bin/runway apm:purge
```
이것은 데이터베이스에서 30일 이상 된 모든 데이터를 제거합니다. `--days` 옵션으로 다른 값을 전달하여 일수 조정:

```bash
php vendor/bin/runway apm:purge --days 7
```
이것은 데이터베이스에서 7일 이상 된 모든 데이터를 제거합니다.

## 문제 해결

막혔나요? 다음을 시도:

- **대시보드 데이터 없음?**
  - 워커가 실행 중인가요? `ps aux | grep apm:worker` 확인.
  - 구성 경로가 맞나요? `.runway-config.json` DSN이 실제 파일을 가리키는지 확인.
  - 보류 중인 메트릭을 처리하려면 `php vendor/bin/runway apm:worker`를 수동 실행.

- **워커 오류?**
  - SQLite 파일 확인(예: `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - PHP 로그에서 스택 트레이스 확인.

- **대시보드 시작 안 됨?**
  - 8001 포트 사용 중? `--port 8080` 사용.
  - PHP 찾을 수 없음? `--php-path /usr/bin/php` 사용.
  - 방화벽 차단? 포트 열기 또는 `--host localhost` 사용.

- **너무 느림?**
  - 샘플 레이트 낮추기: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - 배치 크기 줄이기: `--batch_size 20`.

- **예외/오류 추적 안 됨?**
  - 프로젝트에 [Tracy](https://tracy.nette.org/)가 활성화되어 있으면 Flight의 오류 처리를 재정의합니다. Tracy를 비활성화하고 `Flight::set('flight.handle_errors', true);`가 설정되어 있는지 확인.

- **데이터베이스 쿼리 추적 안 됨?**
  - 데이터베이스 연결에 `PdoWrapper`를 사용 중인지 확인.
  - 생성자에서 마지막 인수가 `true`인지 확인.