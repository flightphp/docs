# 변수

Flight은 애플리케이션 내의 어디에서나 사용할 수 있도록 변수를 저장할 수 있게 해줍니다.

```php
// 변수 저장하기
Flight::set('id', 123);

// 애플리케이션의 다른 곳에서
$id = Flight::get('id');
```

변수가 설정되었는지 확인하려면 다음을 수행할 수 있습니다:

```php
if (Flight::has('id')) {
  // 무언가를 수행
}
```

다음을 수행하여 변수를 지울 수 있습니다:

```php
// id 변수 지우기
Flight::clear('id');

// 모든 변수 지우기
Flight::clear();
```

Flight은 구성 목적으로도 변수를 사용합니다.

```php
Flight::set('flight.log_errors', true);
```