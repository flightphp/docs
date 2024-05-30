# 문제 해결

이 페이지는 Flight를 사용할 때 만날 수 있는 일반적인 문제를 해결하는 데 도움이 될 것입니다.

## 일반적인 문제

### 404 Not Found 또는 예기치 않은 라우트 동작

404 Not Found 오류를 보고 있다면 (그러나 그것이 실제로 거기에 있고 철자가 틀린 게 아니라고 당신 목숨으로 맹세한다면) 이것은 실제로 해당 경로 끝점에서 값을 반환하는 대신 그냥 에코하는 문제가 될 수 있습니다. 이것은 의도적인 것이지만 몇몇 개발자들에게 숨겨진 상태일 수 있습니다.

```php

Flight::route('/hello', function(){
	// This might cause a 404 Not Found error
	return 'Hello World';
});

// What you probably want
Flight::route('/hello', function(){
	echo 'Hello World';
});

``` 

이것은 "다음 경로로 이동"으로 출력을 처리하는 라우터에 내장된 특별한 메커니즘으로 인한 것입니다. [Routing](/learn/routing#passing) 섹션에서 이 동작을 문서화된 것을 볼 수 있습니다.