안녕하세요! 조금 더 구체적으로 fleshing out 하고 싶은 마음을 충분히 이해합니다—당신의 수정 사항은 확실히 좋고, 더 많은 예시와 설명, 그리고 약간의 추가 풍미를 더하기 위해 그것을 바탕으로 진행하겠습니다. 재미있고 친근하게 유지하겠지만, 사용자들이 자신 있게 뛰어들 수 있도록 유용한 세부 정보를 가득 담도록 하겠습니다. 각 조각이 무엇을 하는지, 왜 유용한지, 그리고 몇 가지 실용적인 예시와 함께 문서 페이지를 확장해봅시다!

여기서 보강된 버전입니다:

---

# FlightPHP APM Documentation

FlightPHP APM에 오신 것을 환영합니다—귀하의 앱의 개인 성능 코치! 이 가이드는 FlightPHP와 함께 애플리케이션 성능 모니터링(APM)을 설정하고, 사용하고, 마스터하기 위한 로드맵입니다. 느린 요청을 추적하든, 단순히 지연 차트에 대해 Geek-ish하게 이야기하든, 저희가 도와드리겠습니다. 앱을 더 빠르게, 사용자를 더 행복하게, 디버깅 세션을 더 수월하게 만들어 봅시다!

## APM이 중요한 이유

상상해 보세요: 귀하의 앱은 바쁜 레스토랑입니다. 주문 소요 시간을 추적하거나 주방의 문제를 파악할 방법이 없으면, 고객이 왜 불만을 품고 떠나는지 추측할 수밖에 없습니다. APM은 당신의 수석 요리사입니다—진입 요청부터 데이터베이스 쿼리까지 모든 단계를 지켜보고, 당신을 느리게 하는 무언가를 플래그합니다. 느린 페이지는 사용자를 잃게 만듭니다(연구에 따르면 사이트가 3초 이상 걸리면 53%가 이탈한다고 합니다!), 그리고 APM은 이러한 문제를 *피해*가기 전에 포착하도록 도와줍니다. 이는 사전 예방적인 안정성입니다—“왜 이게 깨졌지?”라는 순간은 줄어들고, “이렇게 매끄럽게 작동하네!”라는 승리는 더 많아집니다.

## 설치

Composer로 시작하세요:

```bash
composer require flightphp/apm
```

필요한 사항:
- **PHP 7.4+**: 현대 PHP를 지원하면서 LTS 리눅스 배포판과의 호환성을 유지합니다.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: 저희가 강화하고 있는 경량 프레임워크입니다.

## 시작하기

APM의 멋진 세계로 들어가는 단계별 절차입니다:

### 1. APM 등록하기

이 코드를 `index.php` 또는 `services.php` 파일에 추가하여 추적을 시작하세요:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**여기서 무슨 일이 일어나고 있나요?**
- `LoggerFactory::create()`는 구성 파일을 가져오고(곧 자세히 설명할 예정) 로거를 설정합니다—기본적으로 SQLite입니다.
- `Apm`이 주인공입니다—Flight의 이벤트(요청, 경로, 오류 등)를 청취하고 메트릭을 수집합니다.
- `bindEventsToFlightInstance($app)`는 모든 것을 귀하의 Flight 앱에 연결합니다.

**전문 팁: 샘플링**
앱이 바쁘면 모든 요청을 기록하면 과부하가 걸릴 수 있습니다. 샘플 비율을 사용하세요(0.0에서 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // 요청의 10%를 기록합니다
```

이렇게 하면 성능은 날카롭게 유지하면서도 확실한 데이터를 제공받을 수 있습니다.

### 2. 설정하기

`.runway-config.json`을 생성하려면 이 명령을 실행하세요:

```bash
php vendor/bin/runway apm:init
```

**이 명령의 역할은?**
- 원시 메트릭이 어디에서 오는지(소스)와 처리된 데이터가 어디로 가는지(대상)를 묻는 마법사를 실행합니다.
- 기본값은 SQLite입니다—예를 들어, 소스는 `sqlite:/tmp/apm_metrics.sqlite`, 그리고 대상은 다른 것입니다.
- 최종적으로 다음과 같은 구성이 생성됩니다:
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

**왜 두 개의 위치일까요?**
원시 메트릭은 빠르게 쌓입니다(필터링되지 않은 로그를 생각해 보세요). 작업자는 이를 대시보드용으로 구조화된 대상으로 처리합니다. 모든 것을 깔끔하게 유지합니다!

### 3. 작업자를 통해 메트릭 처리하기

작업자는 원시 메트릭을 대시보드 준비가 된 데이터로 변환합니다. 한 번 실행해 보세요:

```bash
php vendor/bin/runway apm:worker
```

**이 작업이 무엇을 하고 있나요?**
- 소스(예: `apm_metrics.sqlite`)에서 읽습니다.
- 기본 배치 크기인 100개의 메트릭을 대상으로 처리합니다.
- 완료되면 멈추거나 남아 있는 메트릭이 없으면 멈춥니다.

**계속 실행하기**
실시간 앱의 경우 지속적인 처리가 필요합니다. 다음과 같은 옵션이 있습니다:

- **데몬 모드**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  들어오는 대로 메트릭을 처리하며 영원히 실행됩니다. 개발이나 작은 설정에 적합합니다.

- **크론탭**:
  크론탭에 이 코드를 추가하세요(`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  매 분마다 실행됩니다—프로덕션에 완벽합니다.

- **Tmux/Screen**:
  분리 가능한 세션을 시작하세요:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B 다음 D를 눌러 분리; `tmux attach -t apm-worker`로 재연결
  ```
  로그아웃해도 계속 실행됩니다.

- **사용자 정의 조정**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: 한 번에 50개의 메트릭을 처리합니다.
  - `--max_messages 1000`: 1000개의 메트릭 후 멈춥니다.
  - `--timeout 300`: 5분 후 종료합니다.

**왜 신경 써야 할까요?**
작업자가 없으면 대시보드는 비어 있습니다. 이는 원시 로그와 실행 가능한 통찰력 사이의 다리입니다.

### 4. 대시보드 시작하기

귀하의 앱의 주요 성능 지표를 확인하십시오:

```bash
php vendor/bin/runway apm:dashboard
```

**이 명령의 역할은?**
- `http://localhost:8001/apm/dashboard`에서 PHP 서버를 구동합니다.
- 요청 로그, 느린 경로, 오류 비율 등을 보여줍니다.

**사용자 정의하기**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: 모든 IP에서 접근 가능(원격 보기 용도로 유용).
- `--port 8080`: 8001 포트가 사용 중일 경우 다른 포트를 사용합니다.
- `--php-path`: PHP가 PATH에 없으면 경로를 지정합니다.

브라우저에서 URL을 입력하고 탐색해 보세요!

#### 프로덕션 모드

프로덕션의 경우, 대시보드를 실행하기 위해 몇 가지 기법을 시도해야 할 수도 있습니다. 방화벽 및 기타 보안 조치가 있을 가능성이 높기 때문입니다. 다음 몇 가지 옵션이 있습니다:

- **리버스 프록시 사용**: Nginx 또는 Apache를 설정하여 대시보드에 요청을 포워딩합니다.
- **SSH 터널**: 서버에 SSH가 가능하면, `ssh -L 8080:localhost:8001 youruser@yourserver`를 사용하여 대시보드를 로컬 머신으로 터널링합니다.
- **VPN**: 서버가 VPN 뒤에 있는 경우 연결한 후 대시보드에 직접 접근합니다.
- **방화벽 구성**: 귀하의 IP나 서버의 네트워크에 대해 8001 포트를 엽니다. (또는 설정한 포트로).
- **Apache/Nginx 구성**: 애플리케이션 앞에 웹 서버가 있는 경우, 도메인이나 서브도메인으로 구성할 수 있습니다. 이 경우, 문서 루트를 `/path/to/your/project/vendor/flightphp/apm/dashboard`로 설정합니다.

#### 다른 대시보드가 필요하신가요?

원하는 경우, 직접 대시보드를 구축할 수 있습니다! 데이터를 자신의 대시보드에 표시하는 방법에 대한 아이디어는 `vendor/flightphp/apm/src/apm/presenter` 디렉토리를 확인해보세요!

## 대시보드 기능

대시보드는 귀하의 APM HQ입니다—다음 내용을 볼 수 있습니다:

- **요청 로그**: 타임스탬프, URL, 응답 코드 및 총 시간을 가진 모든 요청. 세부 정보를 클릭하면 미들웨어, 쿼리 및 오류를 확인할 수 있습니다.
- **느린 요청**: 시간을 잡아먹는 상위 5개의 요청(예: “/api/heavy”는 2.5초).
- **느린 경로**: 평균 시간에 따른 상위 5개 경로—패턴을 파악하는 데 유용합니다.
- **오류 비율**: 실패하는 요청의 비율(예: 2.3% 500 오류).
- **지연 백분위수**: 95번째(p95) 및 99번째(p99) 응답 시간—최악의 경우를 미리 알 수 있습니다.
- **응답 코드 차트**: 시간에 따른 200, 404, 500 오류를 시각화합니다.
- **긴 쿼리/미들웨어**: 상위 5개의 느린 데이터베이스 호출 및 미들웨어 레이어.
- **캐시 적중/실패**: 캐시가 얼마나 자주 도움을 주는지.

**추가 기능**:
- “지난 1시간”, “지난 1일”, 또는 “지난 1주”로 필터링.
- 늦은 밤 세션을 위해 다크 모드 전환.

**예시**:
`/users`에 대한 요청은 다음과 같이 표시될 수 있습니다:
- 총 시간: 150ms
- 미들웨어: `AuthMiddleware->handle` (50ms)
- 쿼리: `SELECT * FROM users` (80ms)
- 캐시: `user_list`에서 적중 (5ms)

## 사용자 지정 이벤트 추가하기

API 호출이나 결제 프로세스와 같은 어떤 것이든 추적하세요:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->emit('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**어디에 표시되나요?**
대시보드의 요청 세부정보 아래 “사용자 지정 이벤트”에서 확인할 수 있습니다—예쁘게 JSON 형식으로 확장 가능합니다.

**사용 사례**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->emit('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
이제 그 API가 귀하의 앱을 느리게 만드는지 알 수 있습니다!

## 데이터베이스 모니터링

다음과 같이 PDO 쿼리를 추적하세요:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**얻는 것**:
- 쿼리 텍스트 (예: `SELECT * FROM users WHERE id = ?`)
- 실행 시간 (예: 0.015초)
- 행 수 (예: 42)

**알림**:
- **선택 사항**: DB 추적이 필요하지 않으면 건너뛰세요.
- **PdoWrapper 전용**: 코어 PDO는 아직 연결되지 않았습니다—조만간 업데이트 예정입니다!
- **성능 경고**: DB가 많은 사이트에서 모든 쿼리를 기록하기는 느려질 수 있습니다. 샘플링(`$Apm = new Apm($ApmLogger, 0.1)`)을 사용하여 부하를 줄이세요.

**예시 출력**:
- 쿼리: `SELECT name FROM products WHERE price > 100`
- 시간: 0.023초
- 행: 15

## 작업자 옵션

원하는 대로 작업자를 조정하세요:

- `--timeout 300`: 5분 후 멈춥니다—테스트에 적합합니다.
- `--max_messages 500`: 500개의 메트릭으로 제한합니다—한정성을 유지합니다.
- `--batch_size 200`: 한 번에 200개를 처리합니다—속도와 메모리를 균형있게 유지합니다.
- `--daemon`: 중단 없이 실행됩니다—실시간 모니터링에 이상적입니다.

**예시**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
1시간 동안 100개의 메트릭을 처리합니다.

## 문제 해결

막히셨나요? 다음을 시도해 보세요:

- **대시보드 데이터가 없나요?**
  - 작업자가 실행되고 있나요? `ps aux | grep apm:worker`로 확인하세요.
  - 구성 경로가 일치하나요? `.runway-config.json`의 DSN이 실제 파일을 가리키는지 확인하세요.
  - 보류 중인 메트릭을 처리하려면 `php vendor/bin/runway apm:worker`를 수동으로 실행하세요.

- **작업자 오류가 발생하나요?**
  - SQLite 파일을 확인하세요(예: `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - 스택 추적을 위해 PHP 로그를 확인하세요.

- **대시보드 시작이 안 되나요?**
  - 8001 포트가 사용 중인가요? `--port 8080`을 사용하세요.
  - PHP를 찾지 못하나요? `--php-path /usr/bin/php`를 사용하세요.
  - 방화벽이 차단하나요? 포트를 열거나 `--host localhost`를 사용하세요.

- **너무 느린가요?**
  - 샘플 비율을 낮추세요: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - 배치 크기를 줄이세요: `--batch_size 20`.