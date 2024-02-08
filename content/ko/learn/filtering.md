# 필터링

Flight은 호출되기 전후에 메서드를 필터링할 수 있도록 합니다. 기억해야 할 미리 정의된 후크가 없습니다. 기본 프레임워크 메서드 및 매핑한 사용자 정의 메서드 중에서 원하는 메서드를 필터링할 수 있습니다.

필터 함수는 다음과 같이 보입니다:

```php
function (array &$params, string &$output): bool {
  // 필터 코드
}
```

전달된 변수를 사용하여 입력 매개변수 및/또는 출력을 조작할 수 있습니다.

메서드가 실행되기 전에 필터를 실행하려면 다음과 같이 합니다:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // 무언가 수행
});
```

메서드가 실행된 후에 필터를 실행하려면 다음과 같이 합니다:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // 무언가 수행
});
```

원하는 메서드에 여러 필터를 추가할 수 있습니다. 선언된 순서대로 호출됩니다.

다음은 필터링 프로세스의 예시입니다:

```php
// 사용자 정의 메서드 매핑
Flight::map('hello', function (string $name) {
  return "안녕, $name!";
});

// 전 필터 추가
Flight::before('hello', function (array &$params, string &$output): bool {
  // 매개변수 조작
  $params[0] = 'Fred';
  return true;
});

// 후 필터 추가
Flight::after('hello', function (array &$params, string &$output): bool {
  // 출력 조작
  $output .= " 좋은 하루 보내세요!";
  return true;
});

// 사용자 정의 메서드 호출
echo Flight::hello('Bob');
```

다음이 표시되어야 합니다:

```
안녕, Fred! 좋은 하루 보내세요!
```

여러 필터를 정의했는데 체인을 중단하려면 필터 함수 중에 `false`를 반환하여 체인을 중단할 수 있습니다:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // 이것은 체인을 종료시킵니다
  return false;
});

// 이 부분은 호출되지 않습니다
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

`map` 및 `register`와 같은 핵심 메서드는 직접 호출되므로 동적으로 호출되지 않기 때문에 필터링할 수 없습니다.