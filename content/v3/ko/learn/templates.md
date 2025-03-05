# HTML 뷰 및 템플릿

Flight는 기본적으로 일부 기본 템플릿 기능을 제공합니다.

Flight는 기본 뷰 엔진을 자신의 뷰 클래스를 등록하여 간단하게 교체할 수 있게 해줍니다. Smarty, Latte, Blade 등을 사용하는 방법에 대한 예제를 보려면 아래로 스크롤하세요!

## 내장 뷰 엔진

뷰 템플릿을 표시하려면 템플릿 파일의 이름과 선택적 템플릿 데이터를 `render` 메서드로 호출합니다:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

전달하는 템플릿 데이터는 자동으로 템플릿에 주입되며 지역 변수처럼 참조할 수 있습니다. 템플릿 파일은 단순히 PHP 파일입니다. `hello.php` 템플릿 파일의 내용이 다음과 같다면:

```php
Hello, <?= $name ?>!
```

출력은 다음과 같습니다:

```text
Hello, Bob!
```

set 메서드를 사용하여 뷰 변수를 수동으로 설정할 수도 있습니다:

```php
Flight::view()->set('name', 'Bob');
```

`name` 변수는 이제 모든 뷰에서 사용할 수 있습니다. 따라서 간단히 다음과 같이 하면 됩니다:

```php
Flight::render('hello');
```

render 메서드에서 템플릿의 이름을 지정할 때 `.php` 확장자를 생략할 수 있습니다.

기본적으로 Flight는 템플릿 파일을 위한 `views` 디렉터리를 찾습니다. 다음 설정을 통해 템플릿에 대한 대체 경로를 설정할 수 있습니다:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### 레이아웃

웹사이트에는 일반적으로 교환 가능한 내용을 가진 단일 레이아웃 템플릿 파일이 있습니다. 레이아웃에서 사용할 내용을 렌더링하려면 `render` 메서드에 선택적 매개변수를 전달할 수 있습니다.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

이제 뷰에는 `headerContent` 및 `bodyContent`라는 변수들이 저장됩니다. 그런 다음 다음과 같이 레이아웃을 렌더링할 수 있습니다:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

템플릿 파일이 다음과 같이 보인다면:

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

## Smarty

다음은 [Smarty](http://www.smarty.net/) 템플릿 엔진을 뷰에서 사용하는 방법입니다:

```php
// Smarty 라이브러리 로드
require './Smarty/libs/Smarty.class.php';

// Smarty를 뷰 클래스로 등록
// Smarty를 로드할 때 구성하는 콜백 함수도 전달
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

완전성을 위해 Flight의 기본 render 메서드를 오버라이드하는 것도 잊지 마세요:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

다음은 [Latte](https://latte.nette.org/) 템플릿 엔진을 뷰에서 사용하는 방법입니다:

```php
// Latte를 뷰 클래스로 등록
// Latte를 로드할 때 구성하는 콜백 함수도 전달
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // 여기서 Latte는 템플릿 캐싱을 통해 성능을 높입니다
	// Latte의 한 가지 멋진 점은 템플릿을 변경할 때 자동으로 캐시를 새로 고친다는 것입니다!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 뷰의 루트 디렉터리가 어디에 있을지를 Latte에 알려줍니다.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Flight::render()를 올바르게 사용하기 위해 포장합니다
Flight::map('render', function(string $template, array $data): void {
  // 이는 $latte_engine->render($template, $data)와 같습니다.
  echo Flight::view()->render($template, $data);
});
```

## Blade

다음은 [Blade](https://laravel.com/docs/8.x/blade) 템플릿 엔진을 뷰에서 사용하는 방법입니다:

먼저, Composer를 통해 BladeOne 라이브러리를 설치해야 합니다:

```bash
composer require eftec/bladeone
```

그런 다음, Flight에서 BladeOne을 뷰 클래스로 구성할 수 있습니다:

```php
<?php
// BladeOne 라이브러리 로드
use eftec\bladeone\BladeOne;

// BladeOne을 뷰 클래스로 등록
// BladeOne을 로드할 때 구성하는 콜백 함수를 전달
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

완전성을 위해 Flight의 기본 render 메서드를 오버라이드하는 것도 잊지 마세요:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

이 예제에서 hello.blade.php 템플릿 파일은 다음과 같이 보일 수 있습니다:

```php
<?php
Hello, {{ $name }}!
```

출력은 다음과 같습니다:

```
Hello, Bob!
```

이 단계를 따르면 Blade 템플릿 엔진을 Flight와 통합하고 이를 사용하여 뷰를 렌더링할 수 있습니다.