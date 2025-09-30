# Collections

## Overview

Flight의 `Collection` 클래스는 데이터 세트를 관리하기 위한 유용한 유틸리티입니다. 배열과 객체 표기법을 모두 사용하여 데이터에 접근하고 조작할 수 있어 코드가 더 깔끔하고 유연해집니다.

## Understanding

`Collection`은 기본적으로 배열 주위의 래퍼이지만, 추가 기능이 있습니다. 배열처럼 사용할 수 있으며, 반복할 수 있고, 항목 수를 세고, 객체 속성처럼 항목에 접근할 수 있습니다. 이는 앱에서 구조화된 데이터를 전달하거나 코드를 더 읽기 쉽게 만들 때 특히 유용합니다.

Collections는 여러 PHP 인터페이스를 구현합니다:
- `ArrayAccess` (배열 구문을 사용할 수 있음)
- `Iterator` ( `foreach`로 반복할 수 있음)
- `Countable` ( `count()`를 사용할 수 있음)
- `JsonSerializable` (JSON으로 쉽게 변환할 수 있음)

## Basic Usage

### Creating a Collection

배열을 생성자에 전달하여 컬렉션을 생성할 수 있습니다:

```php
use flight\util\Collection;

$data = [
  'name' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$collection = new Collection($data);
```

### Accessing Items

배열 또는 객체 표기법을 사용하여 항목에 접근할 수 있습니다:

```php
// Array notation
echo $collection['name']; // Output: FlightPHP

// Object notation
echo $collection->version; // Output: 3
```

존재하지 않는 키에 접근하려고 하면 오류 대신 `null`을 반환합니다.

### Setting Items

두 표기법을 사용하여 항목을 설정할 수도 있습니다:

```php
// Array notation
$collection['author'] = 'Mike Cao';

// Object notation
$collection->license = 'MIT';
```

### Checking and Removing Items

항목이 존재하는지 확인:

```php
if (isset($collection['name'])) {
  // Do something
}

if (isset($collection->version)) {
  // Do something
}
```

항목 제거:

```php
unset($collection['author']);
unset($collection->license);
```

### Iterating Over a Collection

Collections는 반복 가능하므로 `foreach` 루프에서 사용할 수 있습니다:

```php
foreach ($collection as $key => $value) {
  echo "$key: $value\n";
}
```

### Counting Items

컬렉션의 항목 수를 세는 방법:

```php
echo count($collection); // Output: 4
```

### Getting All Keys or Data

모든 키 가져오기:

```php
$keys = $collection->keys(); // ['name', 'version', 'features', 'license']
```

배열로 모든 데이터 가져오기:

```php
$data = $collection->getData();
```

### Clearing the Collection

모든 항목 제거:

```php
$collection->clear();
```

### JSON Serialization

Collections를 JSON으로 쉽게 변환할 수 있습니다:

```php
echo json_encode($collection);
// Output: {"name":"FlightPHP","version":3,"features":["routing","views","extending"],"license":"MIT"}
```

## Advanced Usage

필요에 따라 내부 데이터 배열을 완전히 교체할 수 있습니다:

```php
$collection->setData(['foo' => 'bar']);
```

Collections는 컴포넌트 간에 구조화된 데이터를 전달하거나 배열 데이터에 더 객체 지향적인 인터페이스를 제공할 때 특히 유용합니다.

## See Also

- [Requests](/learn/requests) - HTTP 요청을 처리하는 방법과 컬렉션을 사용하여 요청 데이터를 관리하는 방법을 배우세요.
- [PDO Wrapper](/learn/pdo-wrapper) - Flight에서 PDO 래퍼를 사용하는 방법과 컬렉션을 사용하여 데이터베이스 결과를 관리하는 방법을 배우세요.

## Troubleshooting

- 존재하지 않는 키에 접근하려고 하면 오류 대신 `null`을 반환합니다.
- 컬렉션은 재귀적이지 않습니다: 중첩된 배열은 자동으로 컬렉션으로 변환되지 않습니다.
- 컬렉션을 재설정해야 하면 `$collection->clear()` 또는 `$collection->setData([])`를 사용하세요.

## Changelog

- v3.0 - 타입 힌트 개선 및 PHP 8+ 지원.
- v1.0 - Collection 클래스 초기 릴리스.