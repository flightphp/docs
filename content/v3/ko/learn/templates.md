# HTML 뷰와 템플릿

## 개요

Flight는 기본적으로 몇 가지 기본 HTML 템플릿 기능을 제공합니다. 템플릿은 애플리케이션 로직을 프레젠테이션 계층에서 분리하는 매우 효과적인 방법입니다. 전용 엔진(Twig, Latte 등)은 [AI 코딩 도구](/learn/ai)에 익숙하고 제한된 구문을 제공하여 비즈니스 로직을 HTML에 덤프할 가능성을 줄여줍니다.

## 이해

애플리케이션을 구축할 때, 최종 사용자에게 다시 전달하려는 HTML이 있을 것입니다. PHP 자체는 템플릿 언어이지만, 데이터베이스 호출, API 호출 등과 같은 비즈니스 로직을 HTML 파일에 포함시키고 테스트 및 분리를 매우 어렵게 만드는 것은 _매우_ 쉽습니다. 데이터를 템플릿에 푸시하고 템플릿이 스스로 렌더링하도록 하면 코드를 분리하고 단위 테스트하기가 훨씬 쉬워집니다. 템플릿을 사용하면 우리에게 감사할 것입니다!

## 기본 사용법

Flight는 `render`를 매핑(또는 뷰 클래스 등록)하여 기본 뷰 엔진을 간단히 교체할 수 있습니다. Twig, Latte, Smarty, Blade 등에 대해서는 아래로 스크롤하세요.

> **스켈레톤 기본값:** 공식 [flightphp/skeleton](https://github.com/flightphp/skeleton)은 `app/views/`(`*.twig`) 아래에서 **Twig만** 사용합니다. 컨트롤러는 `$this->app->render('welcome', $data)`를 호출합니다(확장자 선택 사항). 이는 새 프로젝트를 위한 애플리케이션 선택 사항이며 Flight 코어의 요구 사항이 아닙니다. Latte 및 기타 엔진은 완전히 지원됩니다.

### Twig

<span class="badge bg-info">스켈레톤 기본값</span>

[Twig](https://twig.symfony.com/)는 Symfony 및 기타 많은 PHP 프로젝트에서 사용되는 유연하고 빠르며 안전한 템플릿 엔진입니다. AI 코딩 도구는 특히 Twig를 잘 알고 있으며, 기본적으로 출력을 자동 이스케이프하여 XSS로부터 보호하는 데 도움이 됩니다.

#### 설치

```bash
composer require twig/twig
```

(`composer create-project flightphp/skeleton` 시 이미 포함되어 있습니다.)

#### 기본 구성

기본 PHP 렌더러 대신 Twig를 사용하려면 `render` 메서드를 재정의하세요:

```php
// 기본 PHP 렌더러 대신 Twig를 사용하도록 render 메서드를 재정의합니다
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Twig가 컴파일된 템플릿을 저장하는 위치
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// "welcome" 또는 "welcome.twig" 허용
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

스켈레톤에서 이 연결은 `app/config/services.php`에 있습니다(공유 Twig 환경, 캐시 경로, `base_url` / CSP nonce와 같은 전역 변수). 컨트롤러에서 `Engine`을 주입하고 `$app->render()`를 호출하여 코드가 [AI 및 테스트 친화적](/learn/ai)으로 유지되도록 하는 것이 좋습니다.

#### Flight에서 Twig 사용하기

이제 Twig로 렌더링할 수 있으므로 다음과 같이 할 수 있습니다:

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {{ name }}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
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

레이아웃과 함께 Twig를 사용하는 더 완전한 예제는 이 문서의 [멋진 플러그인](/awesome-plugins/twig) 섹션에 나와 있습니다. Tracy 바의 렌더 시간 메트릭은 [Tracy 확장의 Twig 패널](/awesome-plugins/tracy-extensions#twig-panel-optional)을 참조하세요.

[공식 문서](https://twig.symfony.com/doc/3.x/)를 읽어 Twig의 전체 기능에 대해 자세히 알아볼 수 있습니다.

### Latte

<span class="badge bg-secondary">훌륭한 대안</span>

[Latte](https://latte.nette.org/)는 PHP와 유사한 구문을 가진 모든 기능을 갖춘 엔진입니다. 여전히 Flight 앱에 탁월한 선택입니다. 스켈레톤은 단순히 하나의 공유 기본값을 위해 Twig로 표준화합니다(특히 AI 도구가 템플릿을 생성할 때 유용합니다).

#### 설치

```bash
composer require latte/latte
```

#### 기본 구성

주요 아이디어는 기본 PHP 렌더러 대신 Latte를 사용하도록 `render` 메서드를 재정의하는 것입니다.

```php
// 기본 PHP 렌더러 대신 Latte를 사용하도록 render 메서드를 재정의합니다
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Latte가 캐시를 저장하는 위치
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

레이아웃과 함께 Latte를 사용하는 더 복잡한 예제는 이 문서의 [멋진 플러그인](/awesome-plugins/latte) 섹션에 나와 있습니다.

번역 및 언어 기능을 포함한 Latte의 전체 기능에 대해 자세히 알아보려면 [공식 문서](https://latte.nette.org/en/)를 읽어보세요.

### 내장 뷰 엔진

<span class="badge bg-warning">더 이상 사용되지 않음</span>

> **참고:** 이것은 여전히 기본 기능이며 기술적으로는 여전히 작동합니다.

뷰 템플릿을 표시하려면 템플릿 파일의 이름과 선택적 템플릿 데이터를 사용하여 `render` 메서드를 호출하세요:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

전달한 템플릿 데이터는 자동으로 템플릿에 주입되며 지역 변수처럼 참조할 수 있습니다. 템플릿 파일은 단순히 PHP 파일입니다. `hello.php` 템플릿 파일의 내용이 다음과 같다면:

```php
Hello, <?= $name ?>!
```

출력은 다음과 같습니다:

```text
Hello, Bob!
```

set 메서드를 사용하여 수동으로 뷰 변수를 설정할 수도 있습니다:

```php
Flight::view()->set('name', 'Bob');
```

이제 `name` 변수를 모든 뷰에서 사용할 수 있습니다. 따라서 간단히 다음과 같이 할 수 있습니다:

```php
Flight::render('hello');
```

render 메서드에서 템플릿 이름을 지정할 때 `.php` 확장자를 생략할 수 있습니다.

기본적으로 Flight는 템플릿 파일에 대해 `views` 디렉토리를 찾습니다. 다음 구성을 설정하여 템플릿에 대한 대체 경로를 설정할 수 있습니다:

```php
Flight::set('flight.views.path', '/path/to/views');
```

기본적으로 Flight의 내장 `View`는 절대 템플릿 경로나 해당 디렉토리를 벗어나는 이름도 허용합니다. 대부분의 앱에서는 이를 잠가야 합니다:

```php
Flight::set('flight.views.restrict_to_path', true);
```

이렇게 하면 `render()`, `fetch()`, `exists()`가 `flight.views.path` 내부에 유지됩니다. 이전 버전과의 호환성을 위해 기본적으로 꺼져 있습니다. [보안](/learn/security#flightviewsrestrict_to_path)을 참조하세요.

#### 레이아웃

웹사이트에서 콘텐츠를 교체하는 단일 레이아웃 템플릿 파일을 갖는 것은 일반적입니다. 레이아웃에 사용할 콘텐츠를 렌더링하려면 `render` 메서드에 선택적 매개변수를 전달할 수 있습니다.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

그러면 뷰에 `headerContent`와 `bodyContent`라는 저장된 변수가 생깁니다. 그런 다음 다음을 수행하여 레이아웃을 렌더링할 수 있습니다:

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
// 로드 시 Smarty를 구성하기 위해 콜백 함수도 전달
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

완전성을 위해 Flight의 기본 렌더 메서드도 재정의해야 합니다:

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
// 로드 시 BladeOne을 구성하기 위해 콜백 함수도 전달
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

완전성을 위해 Flight의 기본 렌더 메서드도 재정의해야 합니다:

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

## 참고 항목
- [설치](/install) - 새 프로젝트를 위한 스켈레톤 레이아웃(`app/views/*.twig`).
- [확장](/learn/extending) - 다른 템플릿 엔진을 사용하도록 `render` 메서드를 재정의하는 방법.
- [라우팅](/learn/routing) - 경로를 컨트롤러에 매핑하고 뷰를 렌더링하는 방법.
- [응답](/learn/responses) - HTTP 응답을 사용자 정의하는 방법.
- [보안](/learn/security) - 자동 이스케이프, XSS 및 `flight.views.restrict_to_path`.
- [AI 및 개발자 경험](/learn/ai) - 하나의 뷰 엔진 기본값이 코딩 에이전트에 도움이 되는 이유.
- [왜 프레임워크인가?](/learn/why-frameworks) - 템플릿이 전체 그림에 어떻게 맞는지.

## 문제 해결
- 미들웨어에 리디렉션이 있지만 앱이 리디렉션되지 않는 것 같다면 미들웨어에 `exit;` 문을 추가했는지 확인하세요.
- Twig가 템플릿을 찾을 수 없으면 `flight.views.path`와 해당 경로 아래에 예상 확장자로 파일이 있는지 확인하세요(스켈레톤: `app/views/`).

## 변경 로그
- 문서 – 네이티브 PHP 뷰에 대한 `flight.views.restrict_to_path` 문서화.
- 문서 – Twig가 공식 스켈레톤 기본값으로 문서화됨. Latte는 일류 대안으로 남아 있음.
- v2.0 - 초기 릴리스.