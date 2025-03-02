# Flight란 무엇입니까?

Flight는 PHP를 위한 빠르고 간단하며 확장 가능한 프레임워크입니다.  
Flight는 RESTful 웹 애플리케이션을 빠르고 쉽게 구축할 수 있게 해줍니다.

``` php
require 'flight/Flight.php';

// 라우트를 정의합니다.
Flight::route('/', function(){
  echo 'hello world!';
});

// Flight를 시작합니다.
Flight::start();
```

[더 알아보기](learn)

# 요구 사항

Flight는 PHP 7.4 이상이 필요합니다.

# 라이센스

Flight는 [MIT](https://github.com/mikecao/flight/blob/master/LICENSE) 라이센스에 따라 배포됩니다.

# 커뮤니티

우리는 매트릭스에 있습니다! [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)에서 저희와 채팅하세요.

# 기여하기

이 웹사이트는 [Github](https://github.com/mikecao/flightphp.com)에서 호스팅됩니다.  
업데이트 및 언어 번역이 환영합니다.