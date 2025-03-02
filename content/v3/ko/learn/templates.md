# HTML 뷰와 템플릿

Flight는 기본적으로 몇 가지 기본 템플레이팅 기능을 제공합니다.

Flight는 직접 만든 뷰 클래스를 등록함으로써 기본 뷰 엔진을 쉽게 바꿀 수 있습니다. Smarty, Latte, Blade 등을 사용하는 방법의 예제를 보려면 아래로 스크롤하세요!

## 기본 제공 뷰 엔진

뷰 템플릿을 표시하려면 템플릿 파일의 이름과 선택적 템플릿 데이터를 사용하여 `render` 메소드를 호출하십시오:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

전달하는 템플릿 데이터는 자동으로 템플릿에 주입되며, 로컬 변수처럼 참조할 수 있습니다. 템플릿 파일은 단순한 PHP 파일입니다. `hello.php` 템플릿 파일의 내용이 다음과 같다면:

```php
안녕하세요, <?= $name ?>!
```

출력은 다음과 같습니다:

```
안녕하세요, Bob!
```

set 메소드를 사용하여 뷰 변수를 수동으로 설정할 수도 있습니다:

```php
Flight::view()->set('name', 'Bob');
```

변수 `name`은 이제 모든 뷰에서 사용할 수 있게 됩니다. 따라서 간단히 다음과 같이 할 수 있습니다:

```php
Flight::render('hello');
```

render 메소드에서 템플릿의 이름을 지정할 때 `.php` 확장자는 생략할 수 있습니다.

기본적으로 Flight는 템플릿 파일을 위한 `views` 디렉토리를 찾습니다. 다음 구성을 설정하여 템플릿의 대체 경로를 설정할 수 있습니다:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### 레이아웃

웹사이트는 일반적으로 교체 가능한 콘텐츠가 있는 단일 레이아웃 템플릿 파일을 갖추고 있습니다. 레이아웃에서 사용할 콘텐츠를 렌더링하려면 `render` 메소드에 선택적 매개변수를 전달할 수 있습니다.

```php
Flight::render('header', ['heading' => '안녕하세요'], 'headerContent');
Flight::render('body', ['body' => '세계'], 'bodyContent');
```

그러면 뷰에 `headerContent` 및 `bodyContent`라는 저장된 변수가 생깁니다. 그런 다음 다음과 같이 레이아웃을 렌더링할 수 있습니다:

```php
Flight::render('layout', ['title' => '홈페이지']);
```

템플릿 파일이 다음과 같다면:

`header.php`:

```php
<h1><?= $heading ?></h1>
```

`body.php`:

```php
<div><?= $body ?></div>
```

`layout.php`:

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

출력은 다음과 같습니다:
```html
<html>
  <head>
    <title>홈페이지</title>
  </head>
  <body>
    <h1>안녕하세요</h1>
    <div>세계</div>
  </body>
</html>
```

## Smarty

다음은 [Smarty](http://www.smarty.net/) 템플릿 엔진을 뷰에서 사용하는 방법입니다:

```php
// Smarty 라이브러리 로드
require './Smarty/libs/Smarty.class.php';

// Smarty를 뷰 클래스으로 등록
// 로드 시 Smarty를 구성하기 위한 콜백 함수를 전달하기도
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// 템플릿 데이터 할당
Flight::view()->assign('name', 'Bob');

// 템플릿 표시
Flight::view()->display('hello.tpl');
```

완전성을 위해 Flight의 기본 render 메소드를 재정의해야 합니다:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

다음은 [Latte](https://latte.nette.org/) 템플릿 엔진을 뷰에서 사용하는 방법입니다:

```php

// Latte를 뷰 클래스으로 등록
// 로드 시 Latte를 구성하기 위한 콜백 함수를 전달하기도
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // 이곳이 Latte가 템플릿을 캐시하여 속도를 높이는 곳입니다
	// Latte의 하나의 장점은 템플릿을 수정할 때 자동으로 캐시를 새로 고친다는 것입니다!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 뷰의 루트 디렉토리가 어디에 있는지 Latte에 알려줍니다.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Flight::render()를 올바르게 사용하기 위해 마무리합니다
Flight::map('render', function(string $template, array $data): void {
  // 이는 $latte_engine->render($template, $data);와 유사합니다
  echo Flight::view()->render($template, $data);
});
```

## Blade

다음은 [Blade](https://laravel.com/docs/8.x/blade) 템플릿 엔진을 뷰에서 사용하는 방법입니다:

먼저, Composer를 통해 BladeOne 라이브러리를 설치해야 합니다:

```bash
composer require eftec/bladeone
```

그런 다음, Flight에서 BladeOne을 뷰 클래스으로 구성할 수 있습니다:

```php
<?php
// BladeOne 라이브러리 로드
use eftec\bladeone\BladeOne;

// BladeOne을 뷰 클래스으로 등록
// 로드 시 BladeOne을 구성하기 위한 콜백 함수를 전달하기도
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// 템플릿 데이터 할당
Flight::view()->share('name', 'Bob');

// 템플릿 표시
echo Flight::view()->run('hello', []);
```

완전성을 위해 Flight의 기본 render 메소드를 재정의해야 합니다:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

이 예제에서 hello.blade.php 템플릿 파일은 다음과 같을 수 있습니다:

```php
<?php
안녕하세요, {{ $name }}!
```

출력은 다음과 같습니다:

```
안녕하세요, Bob!
```

이 단계들을 따르면 Blade 템플릿 엔진을 Flight와 통합하고 이를 사용하여 뷰를 렌더링할 수 있습니다.