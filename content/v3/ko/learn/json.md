# JSON Wrapper

## 개요

Flight의 `Json` 클래스는 애플리케이션에서 JSON 데이터를 인코딩하고 디코딩하는 간단하고 일관된 방법을 제공합니다. PHP의 기본 JSON 함수를 더 나은 오류 처리와 유용한 기본값으로 감싸서 JSON 작업을 더 쉽고 안전하게 만듭니다.

## 이해하기

현대 PHP 앱에서 JSON 작업은 API 구축이나 AJAX 요청 처리 시 매우 일반적입니다. `Json` 클래스는 모든 JSON 인코딩과 디코딩을 중앙화하여 PHP의 내장 함수에서 발생하는 이상한 에지 케이스나 난해한 오류를 걱정할 필요가 없습니다.

주요 기능:
- 일관된 오류 처리 (실패 시 예외 발생)
- 인코딩/디코딩을 위한 기본 옵션 (예: 이스케이프되지 않은 슬래시)
- 예쁜 출력 및 유효성 검사 유틸리티 메서드

## 기본 사용법

### 데이터를 JSON으로 인코딩하기

PHP 데이터를 JSON 문자열로 변환하려면 `Json::encode()`를 사용하세요:

```php
use flight\util\Json;

$data = [
  'framework' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$json = Json::encode($data);
echo $json;
// 출력: {"framework":"Flight","version":3,"features":["routing","views","extending"]}
```

인코딩이 실패하면 유용한 오류 메시지가 포함된 예외를 받게 됩니다.

### 예쁜 출력

JSON을 사람이 읽기 쉽게 하려면 `prettyPrint()`를 사용하세요:

```php
echo Json::prettyPrint($data);
/*
{
  "framework": "Flight",
  "version": 3,
  "features": [
    "routing",
    "views",
    "extending"
  ]
}
*/
```

### JSON 문자열 디코딩

JSON 문자열을 PHP 데이터로 다시 변환하려면 `Json::decode()`를 사용하세요:

```php
$json = '{"framework":"Flight","version":3}';
$data = Json::decode($json);
echo $data->framework; // 출력: Flight
```

객체 대신 연관 배열을 원하면 두 번째 인수로 `true`를 전달하세요:

```php
$data = Json::decode($json, true);
echo $data['framework']; // 출력: Flight
```

디코딩이 실패하면 명확한 오류 메시지가 포함된 예외를 받게 됩니다.

### JSON 유효성 검사

문자열이 유효한 JSON인지 확인하세요:

```php
if (Json::isValid($json)) {
  // 유효합니다!
} else {
  // 유효하지 않은 JSON
}
```

### 마지막 오류 가져오기

네이티브 PHP 함수에서 발생한 마지막 JSON 오류 메시지를 확인하려면:

```php
$error = Json::getLastError();
if ($error !== '') {
  echo "마지막 JSON 오류: $error";
}
```

## 고급 사용법

더 많은 제어가 필요하다면 인코딩 및 디코딩 옵션을 사용자 지정할 수 있습니다 ( [PHP의 json_encode 옵션](https://www.php.net/manual/en/json.constants.php) 참조):

```php
// HEX_TAG 옵션으로 인코딩
$json = Json::encode($data, JSON_HEX_TAG);

// 사용자 지정 깊이로 디코딩
$data = Json::decode($json, false, 1024);
```

## 관련 항목

- [Collections](/learn/collections) - JSON으로 쉽게 변환할 수 있는 구조화된 데이터 작업.
- [Configuration](/learn/configuration) - Flight 앱 구성 방법.
- [Extending](/learn/extending) - 사용자 지정 유틸리티 추가 또는 코어 클래스 재정의 방법.

## 문제 해결

- 인코딩 또는 디코딩이 실패하면 예외가 발생합니다—오류를 우아하게 처리하려면 호출을 try/catch로 감싸세요.
- 예상치 못한 결과가 나오면 데이터에서 순환 참조나 비-UTF8 문자를 확인하세요.
- 디코딩 전에 `Json::isValid()`를 사용하여 문자열이 유효한 JSON인지 확인하세요.

## 변경 로그

- v3.16.0 - JSON 래퍼 유틸리티 클래스 추가.