# 필터링

## 개요

Flight는 [매핑된 메서드](/learn/extending)가 호출되기 전과 후에 필터링할 수 있도록 합니다.

## 이해하기
기억해야 할 미리 정의된 훅은 없습니다. 프레임워크의 기본 메서드와 매핑한 사용자 지정 메서드 모두를 필터링할 수 있습니다.

필터 함수는 다음과 같습니다:

```php
/**
 * @param array $params 필터링되는 메서드에 전달된 매개변수.
 * @param string $output (v2 출력 버퍼링 전용) 필터링되는 메서드의 출력.
 * @return bool 체인을 계속하려면 true/void를 반환하거나 반환하지 말고, 체인을 중단하려면 false를 반환합니다.
 */
function (array &$params, string &$output): bool {
  // 필터 코드
}
```

전달된 변수를 사용하여 입력 매개변수와/또는 출력을 조작할 수 있습니다.

메서드 전에 필터를 실행하려면 다음과 같이 합니다:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // 무언가 수행
});
```

메서드 후에 필터를 실행하려면 다음과 같이 합니다:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // 무언가 수행
});
```

원하는 만큼의 필터를 어떤 메서드에도 추가할 수 있습니다. 필터는 선언된 순서대로 호출됩니다.

필터링 프로세스의 예는 다음과 같습니다:

```php
// 사용자 지정 메서드 매핑
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// before 필터 추가
Flight::before('hello', function (array &$params, string &$output): bool {
  // 매개변수 조작
  $params[0] = 'Fred';
  return true;
});

// after 필터 추가
Flight::after('hello', function (array &$params, string &$output): bool {
  // 출력 조작
  $output .= " Have a nice day!";
  return true;
});

// 사용자 지정 메서드 호출
echo Flight::hello('Bob');
```

이것은 다음과 같이 표시되어야 합니다:

```
Hello Fred! Have a nice day!
```

여러 필터를 정의한 경우, 필터 함수 중 하나에서 `false`를 반환하여 체인을 중단할 수 있습니다:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // 이것은 체인을 종료합니다
  return false;
});

// 이것은 호출되지 않습니다
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

> **참고:** `map` 및 `register`와 같은 핵심 메서드는 직접 호출되며 동적으로 호출되지 않기 때문에 필터링할 수 없습니다. 자세한 내용은 [Extending Flight](/learn/extending)를 참조하세요.

## 관련 항목
- [Extending Flight](/learn/extending)

## 문제 해결
- 체인을 중지하려면 필터 함수에서 `false`를 반환하는지 확인하세요. 아무것도 반환하지 않으면 체인이 계속됩니다.

## 변경 로그
- v2.0 - 초기 릴리스.