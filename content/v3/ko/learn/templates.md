# HTML 뷰와 템플릿

## 개요

Flight는 기본적으로 간단한 HTML 템플릿 기능이 제공됩니다. 템플릿은 애플리케이션 로직을 프레젠테이션 계층에서 분리하는 매우 효과적인 방법입니다.

## 이해하기

애플리케이션을 구축할 때, 최종 사용자에게 전달할 HTML을 만들게 될 것입니다. PHP 자체는 템플릿 언어이지만, 데이터베이스 호출, API 호출 등의 비즈니스 로직을 HTML 파일에 쉽게 포함시켜 테스트와 분리를 매우 어렵게 만들 수 있습니다. 데이터를 템플릿에 전달하고 템플릿이 자체적으로 렌더링하도록 하면 코드를 분리하고 단위 테스트하기가 훨씬 쉬워집니다. 템플릿을 사용하면 나중에 감사하게 될 것입니다!

## 기본 사용법

Flight는 기본 뷰 엔진을 자신의 뷰 클래스를 등록함으로써 간단히 교체할 수 있습니다. Smarty, Latte, Blade 등의 사용 예시는 아래를 스크롤하여 확인하세요!

### Latte

<span class="badge bg-info">권장</span>

뷰에 [Latte](https://latte.nette.org/) 템플릿 엔진을 사용하는 방법은 다음과 같습니다.

#### 설치

```bash
composer require latte/latte
```

#### 기본 구성

주요 아이디어는 기본 PHP 렌더러 대신 Latte를 사용하도록 `render` 메서드를 덮어쓰는 것입니다.

```php
// 기본 PHP 렌더러 대신 latte를 사용하도록 render 메서드 덮어쓰기
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// latte가 캐시를 저장하는 위치
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Flight에서 Latte 사용하기

이제 Latte로 렌더링할 수 있으므로 다음과 같이 할 수 있습니다:

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

브라우저에서 `/Bob`을 방문하면 출력은 다음과 같습니다:

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### 추가 읽기

Latte를 레이아웃과 함께 사용하는 더 복잡한 예시는 이 문서의 [멋진 플러그인](/awesome-plugins/latte) 섹션에 나와 있습니다.

번역 및 언어 기능 포함 Latte의 전체 기능을 더 알아보려면 [공식 문서](https://latte.nette.org/en/)를 읽어보세요.

### 내장 뷰 엔진

<span class="badge bg-warning">더 이상 사용되지 않음</span>

> **참고:** 여전히 기본 기능이며 기술적으로 작동합니다.

뷰 템플릿을 표시하려면 `render` 메서드를 템플릿 파일 이름과 선택적 템플릿 데이터로 호출하세요:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

전달된 템플릿 데이터는 자동으로 템플릿에 주입되며 로컬 변수처럼 참조할 수 있습니다. 템플릿 파일은 단순한 PHP 파일입니다. `hello.php` 템플릿 파일의 내용이 다음과 같다면:

```php
Hello, <?= $name ?>!
```

출력은 다음과 같습니다:

```text
Hello, Bob!
```

`set` 메서드를 사용하여 뷰 변수를 수동으로 설정할 수도 있습니다:

```php
Flight::view()->set('name', 'Bob');
```

이제 `name` 변수는 모든 뷰에서 사용할 수 있습니다. 따라서 간단히 다음과 같이 할 수 있습니다:

```php
Flight::render('hello');
```

`render` 메서드에서 템플릿 이름을 지정할 때 `.php` 확장자를 생략할 수 있습니다.

기본적으로 Flight는 템플릿 파일을 `views` 디렉토리에서 찾습니다. 템플릿에 대한 대체 경로를 설정하려면 다음 구성을 설정하세요:

```php
Flight::set('flight.views.path', '/path/to/views');
```

#### 레이아웃

웹사이트는 교환 가능한 콘텐츠와 함께 단일 레이아웃 템플릿 파일을 가지는 것이 일반적입니다. 레이아웃에 사용할 콘텐츠를 렌더링하려면 `render` 메서드에 선택적 매개변수를 전달할 수 있습니다.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

뷰에는 `headerContent`와 `bodyContent`라는 저장된 변수가 있습니다. 레이아웃을 렌더링하려면 다음과 같이 하세요:

```php
Flight::render('layout', ['title' => 'Home Page']);
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
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

### Smarty

뷰에 [Smarty](http://www.smarty.net/) 템플릿 엔진을 사용하는 방법은 다음과 같습니다:

```php
// Smarty 라이브러리 로드
require './Smarty/libs/Smarty.class.php';

// Smarty를 뷰 클래스로 등록
// 로드 시 Smarty를 구성하는 콜백 함수도 전달
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

완전성을 위해 Flight의 기본 render 메서드도 덮어써야 합니다:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

뷰에 [Blade](https://laravel.com/docs/8.x/blade) 템플릿 엔진을 사용하는 방법은 다음과 같습니다:

먼저 Composer를 통해 BladeOne 라이브러리를 설치해야 합니다:

```bash
composer require eftec/bladeone
```

그런 다음 Flight에서 BladeOne을 뷰 클래스로 구성할 수 있습니다:

```php
<?php
// BladeOne 라이브러리 로드
use eftec\bladeone\BladeOne;

// BladeOne을 뷰 클래스로 등록
// 로드 시 BladeOne을 구성하는 콜백 함수도 전달
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

완전성을 위해 Flight의 기본 render 메서드도 덮어써야 합니다:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

이 예에서 hello.blade.php 템플릿 파일은 다음과 같을 수 있습니다:

```php
<?php
Hello, {{ $name }}!
```

출력은 다음과 같습니다:

```
Hello, Bob!
```

## 관련 항목
- [확장](/learn/extending) - 다른 템플릿 엔진을 사용하도록 `render` 메서드를 덮어쓰는 방법.
- [라우팅](/learn/routing) - 라우트를 컨트롤러에 매핑하고 뷰를 렌더링하는 방법.
- [응답](/learn/responses) - HTTP 응답을 사용자 지정하는 방법.
- [프레임워크란?](/learn/why-frameworks) - 템플릿이 전체 그림에 어떻게 맞는지.

## 문제 해결
- 미들웨어에 리디렉션이 있지만 앱이 리디렉션되지 않는 것 같다면, 미들웨어에 `exit;` 문을 추가했는지 확인하세요.

## 변경 로그
- v2.0 - 초기 릴리스.