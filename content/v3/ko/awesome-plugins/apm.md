# FlightPHP APM 문서

FlightPHP APM에 오신 것을 환영합니다—당신 앱의 개인 성능 코치입니다! 이 가이드는 FlightPHP와 함께 Application Performance Monitoring (APM)을 설정, 사용, 그리고 마스터하는 로드맵입니다. 느린 요청을 추적하거나 지연 시간 차트를 살펴보고 싶으신지, 우리는 모든 것을 다루고 있습니다. 당신의 앱을 더 빠르게 만들고, 사용자들을 더 행복하게 하며, 디버깅 세션을 쉽게 만들어 보겠습니다!

## APM의 중요성

이것을 상상해 보세요: 당신의 앱은 바쁜 레스토랑입니다. 주문이 얼마나 걸리는지 또는 주방이 어디서 지체되는지 추적할 방법 없이, 왜 고객들이 불만족스럽게 떠나는지 추측만 하게 됩니다. APM은 당신의 수석 셰프 역할을 합니다—수신 요청부터 데이터베이스 쿼리까지 모든 단계를 감시하고, 느려지는 부분을 표시합니다. 느린 페이지는 사용자를 잃게 합니다 (연구에 따르면 사이트가 3초 이상 걸리면 53%가 이탈한다고 합니다!), APM은 이러한 문제를 *발생하기 전에* 포착하여 도와줍니다. 이는 사전적인 안정감—더 적은 "이게 왜 고장 났지?" 순간, 더 많은 "이게 얼마나 매끄럽게 작동하나 봐!" 승리를 가져옵니다.

## 설치

Composer로 시작하세요:

```bash
# composer require flightphp/apm을 사용하세요
composer require flightphp/apm
```

필요한 것:
- **PHP 7.4+**: LTS Linux 배포판과 호환되면서 현대 PHP를 지원합니다.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: 우리가 강화하는 경량 프레임워크입니다.

## 시작하기

APM의 멋진 단계별 가이드입니다:

### 1. APM 등록

`index.php` 또는 `services.php` 파일에 다음을 추가하여 추적을 시작하세요:

```php
# flight\apm\logger\LoggerFactory를 사용합니다
use flight\apm\logger\LoggerFactory;
# flight\Apm을 사용합니다
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**여기서 무슨 일이 일어나나요?**
- `LoggerFactory::create()`는 당신의 구성(곧 더 자세히 설명)을 가져와 로거를 설정합니다—기본적으로 SQLite입니다.
- `Apm`은 주인공입니다—Flight의 이벤트(요청, 라우트, 오류 등)를 듣고 메트릭스를 수집합니다.
- `bindEventsToFlightInstance($app)`은 이를 Flight 앱에 연결합니다.

**프로 팁: 샘플링**
앱이 바쁘면 모든 요청을 로깅하면 과부하가 발생할 수 있습니다. 샘플 속도(0.0에서 1.0)를 사용하세요:

```php
# 10%의 요청만 로깅합니다
$Apm = new Apm($ApmLogger, 0.1); // Logs 10% of requests
```

이렇게 하면 성능을 유지하면서도 안정적인 데이터를 얻을 수 있습니다.

### 2. 구성하기

`.runway-config.json`을 생성하려면 다음을 실행하세요:

```bash
# php vendor/bin/runway apm:init을 실행하세요
php vendor/bin/runway apm:init
```

**이게 무슨 일을 하나요?**
- 원시 메트릭스의 소스(출처)와 처리된 데이터의 목적지(대상)를 묻는 마법사를 시작합니다.
- 기본은 SQLite—예를 들어, 소스로 `sqlite:/tmp/apm_metrics.sqlite`, 또 다른 하나를 대상으로 합니다.
- 결과적으로 다음과 같은 구성이 됩니다:
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

> 이 과정에서 이 설정에 대한 마이그레이션을 실행할지 묻습니다. 처음 설정하는 경우, 대답은 예입니다.

**왜 두 위치인가요?**
원시 메트릭스는 빠르게 쌓입니다(필터링되지 않은 로그를 생각하세요). 워커가 이를 처리하여 대시보드용 구조화된 목적지로 이동합니다. 정리된 상태를 유지합니다!

### 3. 워커로 메트릭스 처리

워커는 원시 메트릭스를 대시보드 준비 데이터로 변환합니다. 한 번 실행하세요:

```bash
# php vendor/bin/runway apm:worker을 실행하세요
php vendor/bin/runway apm:worker
```

**이게 무슨 일을 하나요?**
- 소스(예: `apm_metrics.sqlite`)에서 읽습니다.
- 최대 100개의 메트릭스(기본 배치 크기)를 처리하여 목적지로 이동합니다.
- 완료되거나 메트릭스가 남지 않으면 중지합니다.

**계속 실행하기**
실제 앱의 경우, 지속적인 처리가 필요합니다. 옵션은 다음과 같습니다:

- **데몬 모드**:
  ```bash
  # php vendor/bin/runway apm:worker --daemon을 실행하세요
  php vendor/bin/runway apm:worker --daemon
  ```
  영원히 실행되며, 메트릭스가 들어오면 처리합니다. 개발이나 소규모 설정에 적합합니다.

- **Crontab**:
  crontab에 추가하세요 (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  매 분마다 실행—프로덕션에 적합합니다.

- **Tmux/Screen**:
  분리 가능한 세션을 시작하세요:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, then D를 눌러 분리; `tmux attach -t apm-worker`로 재연결
  ```
  로그아웃해도 계속 실행됩니다.

- **커스텀 조정**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: 한 번에 50개의 메트릭스를 처리합니다.
  - `--max_messages 1000`: 1000개의 메트릭스 후 중지합니다.
  - `--timeout 300`: 5분 후 종료합니다.

**왜 해야 하나요?**
워커 없이는 대시보드가 비어 있습니다. 이는 원시 로그와 실행 가능한 인사이트 사이의 다리입니다.

### 4. 대시보드 실행

앱의 활력 상태를 보세요:

```bash
# php vendor/bin/runway apm:dashboard을 실행하세요
php vendor/bin/runway apm:dashboard
```

**이게 무슨 일이죠?**
- `http://localhost:8001/apm/dashboard`에서 PHP 서버를 시작합니다.
- 요청 로그, 느린 라우트, 오류 비율 등을 보여줍니다.

**커스터마이즈하기**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: 모든 IP에서 접근 가능합니다(원격 보기 유용).
- `--port 8080`: 8001이 사용 중이면 다른 포트를 사용하세요.
- `--php-path`: PATH에 PHP가 없으면 지정하세요.

브라우저에서 URL을 열고 탐색하세요!

#### 프로덕션 모드

프로덕션에서 대시보드를 실행하려면 방화벽과 보안 조치로 인해 몇 가지 기술을 시도해야 할 수 있습니다. 몇 가지 옵션:

- **리버스 프록시 사용**: Nginx 또는 Apache를 설정하여 요청을 대시보드로 전달하세요.
- **SSH 터널**: 서버에 SSH로 접속할 수 있으면, `ssh -L 8080:localhost:8001 youruser@yourserver`를 사용하여 로컬 머신으로 터널링하세요.
- **VPN**: 서버가 VPN 뒤에 있으면 연결하고 직접 대시보드에 접근하세요.
- **방화벽 구성**: 지정한 IP나 서버 네트워크에 대해 포트 8001을 열어주세요(또는 설정한 포트).
- **Apache/Nginx 구성**: 애플리케이션 앞에 웹 서버가 있으면 도메인이나 서브도메인으로 구성하세요. 이렇게 하면 문서 루트를 `/path/to/your/project/vendor/flightphp/apm/dashboard`로 설정합니다.

#### 다른 대시보드 원하세요?

원하시면 직접 대시보드를 구축할 수 있습니다! vendor/flightphp/apm/src/apm/presenter 디렉터리를 살펴보고 데이터를 표시하는 아이디어를 얻으세요!

## 대시보드 기능

대시보드는 APM의 본부—여기서 볼 수 있는 내용입니다:

- **요청 로그**: 타임스탬프, URL, 응답 코드, 총 시간과 함께 모든 요청. "상세"를 클릭하면 미들웨어, 쿼리, 오류를 볼 수 있습니다.
- **가장 느린 요청**: 시간 소모가 가장 큰 상위 5개 요청(예: “/api/heavy” at 2.5s).
- **가장 느린 라우트**: 평균 시간 기준 상위 5개 라우트—패턴을 발견하는 데 좋습니다.
- **오류 비율**: 실패한 요청의 비율(예: 2.3% 500s).
- **지연 백분위수**: 95번째(p95)와 99번째(p99) 응답 시간—최악 시나리오를 알 수 있습니다.
- **응답 코드 차트**: 200s, 404s, 500s 등을 시간 경과에 따라 시각화합니다.
- **긴 쿼리/미들웨어**: 상위 5개 느린 데이터베이스 호출과 미들웨어 레이어.
- **캐시 히트/미스**: 캐시가 얼마나 자주 작동하는지.

**추가 기능**:
- "지난 1시간", "지난 1일", 또는 "지난 1주"로 필터링.
- 야간 세션을 위해 다크 모드를 토글.

**예시**:
`/users` 요청은 다음과 같이 보여질 수 있습니다:
- 총 시간: 150ms
- 미들웨어: `AuthMiddleware->handle` (50ms)
- 쿼리: `SELECT * FROM users` (80ms)
- 캐시: `user_list` 히트 (5ms)

## 커스텀 이벤트 추가

API 호출이나 지불 처리와 같은 것을 추적하세요:

```php
# flight\apm\CustomEvent를 사용합니다
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**어디에 표시되나요?**
대시보드의 요청 상세에서 “커스텀 이벤트” 아래—확장 가능하며 예쁜 JSON 형식으로 표시됩니다.

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
이제 그 API가 앱을 느리게 하는지 확인할 수 있습니다!

## 데이터베이스 모니터링

PDO 쿼리를 이렇게 추적하세요:

```php
# flight\database\PdoWrapper를 사용합니다
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**얻을 수 있는 것**:
- 쿼리 텍스트(예: `SELECT * FROM users WHERE id = ?`)
- 실행 시간(예: 0.015s)
- 행 수(예: 42)

**주의사항**:
- **선택적**: DB 추적이 필요하지 않으면 건너뛰세요.
- **PdoWrapper만**: 기본 PDO는 아직 연결되지 않았습니다—기다려 주세요!
- **성능 경고**: DB가 많은 사이트에서 모든 쿼리를 로깅하면 느려질 수 있습니다. 샘플링을 사용하세요(`$Apm = new Apm($ApmLogger, 0.1)`).

**예시 출력**:
- 쿼리: `SELECT name FROM products WHERE price > 100`
- 시간: 0.023s
- 행: 15

## 워커 옵션

워커를 원하는 대로 조정하세요:

- `--timeout 300`: 5분 후 중지—테스트에 좋습니다.
- `--max_messages 500`: 500개의 메트릭스 후 중지—유한하게 유지합니다.
- `--batch_size 200`: 한 번에 200개 처리—속도와 메모리 균형.
- `--daemon`: 영원히 실행—실시간 모니터링에 이상적.

**예시**:
```bash
# php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600을 실행하세요
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
1시간 동안 실행되며, 한 번에 100개의 메트릭스를 처리합니다.

## 앱의 요청 ID

각 요청에는 고유한 요청 ID가 있어 로그와 메트릭스를 추적할 수 있습니다. 예를 들어 오류 페이지에 요청 ID를 추가할 수 있습니다:

```php
Flight::map('error', function($message) {
	# 요청 ID를 응답 헤더 X-Flight-Request-Id에서 가져옵니다
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	# Flight 변수에서 가져올 수도 있습니다
	# 이는 Swoole나 다른 비동기 플랫폼에서 잘 작동하지 않을 수 있습니다.
	# $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## 업그레이드

APM의 새 버전으로 업그레이드할 때 데이터베이스 마이그레이션이 필요할 수 있습니다. 다음 명령으로 실행하세요:

```bash
# php vendor/bin/runway apm:migrate을 실행하세요
php vendor/bin/runway apm:migrate
```
이 명령은 데이터베이스 스키마를 최신 버전으로 업데이트합니다.

**노트:** APM 데이터베이스가 크면 이 마이그레이션에 시간이 걸릴 수 있습니다. 오프-피크 시간에 실행하세요.

## 오래된 데이터 제거

데이터베이스를 정리하려면 오래된 데이터를 제거하세요. 이는 바쁜 앱에서 데이터베이스 크기를 관리하는 데 유용합니다.
다음 명령으로 실행하세요:

```bash
# php vendor/bin/runway apm:purge을 실행하세요
php vendor/bin/runway apm:purge
```
이 명령은 30일 이상 된 모든 데이터를 제거합니다. `--days` 옵션으로 일수를 조정하세요:

```bash
php vendor/bin/runway apm:purge --days 7
```
이 명령은 7일 이상 된 데이터를 제거합니다.

## 문제 해결

문제가 발생하면 다음을 시도하세요:

- **대시보드에 데이터가 없나요?**
  - 워커가 실행 중인가요? `ps aux | grep apm:worker`로 확인하세요.
  - 구성 경로가 맞나요? `.runway-config.json`의 DSN이 실제 파일을 가리키는지 확인하세요.
  - `php vendor/bin/runway apm:worker`를 수동으로 실행하여 보류 중인 메트릭스를 처리하세요.

- **워커 오류?**
  - SQLite 파일을 확인하세요(예: `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - PHP 로그를 확인하여 스택 트레이스를 보세요.

- **대시보드가 시작되지 않나요?**
  - 포트 8001이 사용 중인가요? `--port 8080`을 사용하세요.
  - PHP가 없나요? `--php-path /usr/bin/php`를 사용하세요.
  - 방화벽이 막나요? 포트를 열거나 `--host localhost`를 사용하세요.

- **너무 느리나요?**
  - 샘플 속도를 낮추세요: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - 배치 크기를 줄이세요: `--batch_size 20`.