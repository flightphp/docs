# 뷰

Flight는 기본적인 템플릿 기능을 제공합니다.

더 복잡한 템플릿이 필요하다면 [사용자 정의 뷰](#custom-views) 섹션에서 Smarty와 Latte 예제를 참조하세요.

뷰 템플릿을 표시하려면 템플릿 파일과 선택적 템플릿 데이터를 사용하여 `render` 메소드를 호출하십시오.

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

전달하는 템플릿 데이터는 자동으로 템플릿에 주입되며 로컬 변수처럼 참조할 수 있습니다. 템플릿 파일은 간단히 PHP 파일입니다. 만약 `hello.php` 템플릿 파일의 내용이 다음과 같다면:

```php
Hello, <?= $name ?>!
```

출력은 다음과 같을 것입니다:

```
Hello, Bob!
```

`set` 메소드를 사용하여 뷰 변수를 수동으로 설정할 수도 있습니다.

```php
Flight::view()->set('name', 'Bob');
```

이제 `name` 변수는 모든 뷰에서 사용할 수 있습니다. 따라서 다음과 같이 할 수 있습니다:

```php
Flight::render('hello');
```

`render` 메소드에서 템플릿의 이름을 지정할 때 `.php` 확장자를 생략할 수 있습니다.

Flight는 기본적으로 템플릿 파일을 위해 `views` 디렉토리를 찾습니다. 다음 설정을 설정하여 템플릿의 대체 경로를 설정할 수 있습니다:

```php
Flight::set('flight.views.path', '/경로/에서/뷰');
```

## 레이아웃

웹 사이트가 서로 교체되는 콘텐츠를 포함한 단일 레이아웃 템플릿 파일을 가지고 있는 경우가 많습니다. 레이아웃에 사용할 콘텐츠를 렌더링하려면 `render` 메소드에 선택적 매개변수를 전달할 수 있습니다.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

그러면 뷰는 `headerContent`와 `bodyContent`라는 저장된 변수를 가지게 됩니다. 이후 레이아웃을 렌더링하려면 다음을 수행할 수 있습니다:

```php
Flight::render('layout', ['title' => '홈 페이지']);
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

출력은 다음과 같을 것입니다:
```html
<html>
	<head>
		<title>홈 페이지</title>
	</head>
	<body>
		<h1>Hello</h1>
		<div>World</div>
	</body>
</html>
```

## 사용자 정의 뷰

Flight를 통해 기본 뷰 엔진을 교체할 수 있습니다. 사용자 정의 뷰 클래스를 등록함으로써 가능합니다.

### 스마티(Smarty)

뷰로 [Smarty](http://www.smarty.net/) 템플릿 엔진을 사용하는 방법은 다음과 같습니다:

```php
// Smarty 라이브러리 로드
require './Smarty/libs/Smarty.class.php';

// 뷰 클래스로 Smarty 등록
// 또한 Smarty를 로드할 때 구성하기 위한 콜백 함수를 전달합니다.
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
});

// 템플릿 데이터 할당
Flight::view()->assign('name', 'Bob');

// 템플릿 표시
Flight::view()->display('hello.tpl');
```

완성을 위해 Flight의 기본 render 메소드를 재정의해야 합니다:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### 라테(Latte)

뷰로 [Latte](https://latte.nette.org/) 템플릿 엔진을 사용하는 방법은 다음과 같습니다:

```php

// 뷰 클래스로 Latte 등록
// Latte를 로드할 때 구성하기 위한 콜백 함수를 전달합니다.
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // 이곳은 Latte가 템플릿을 캐시하는 위치입니다.
	// Latte의 멋진 점 중 하나는 템플릿을 수정할 때 자동으로 캐시를 새로고침한다는 것입니다!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Latte에게 뷰의 루트 디렉토리를 알려줍니다.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Flight::render()를 올바르게 사용할 수 있도록 묶어줍니다.
Flight::map('render', function(string $template, array $data): void {
  // 이는 $latte_engine->render($template, $data); 와 같습니다.
  echo Flight::view()->render($template, $data);
});
```