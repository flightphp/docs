# HTML Views and Templates

Flight은 기본적인 템플릿 기능을 제공합니다.

보다 복잡한 템플릿이 필요한 경우 [사용자 정의 뷰](#custom-views) 섹션에서 Smarty와 Latte 예제를 참조하십시오.

## 기본 View 엔진

뷰 템플릿을 표시하려면 `render` 메서드를 호출하고 템플릿 파일의 이름과 선택적인 템플릿 데이터를 전달하십시오:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

전달하는 템플릿 데이터는 자동으로 템플릿에 주입되며 로컬 변수처럼 참조할 수 있습니다. 템플릿 파일은 단순히 PHP 파일입니다. `hello.php` 템플릿 파일의 내용이 다음과 같다면:

```php
Hello, <?= $name ?>!
```

출력은 다음과 같을 것입니다:

```
Hello, Bob!
```

또한 `set` 메서드를 사용하여 뷰 변수를 수동으로 설정할 수도 있습니다:

```php
Flight::view()->set('name', 'Bob');
```

이제 `name` 변수는 모든 뷰에서 사용할 수 있습니다. 따라서 다음과 같이 간단히 할 수 있습니다:

```php
Flight::render('hello');
```

`render` 메서드에서 템플릿의 이름을 지정할 때 `.php` 확장자를 생략할 수 있다는 점에 유의하십시오.

Flight는 기본적으로 템플릿 파일을 위해 `views` 디렉토리를 찾습니다. 다음 구성을 설정하여 템플릿의 대체 경로를 설정할 수 있습니다:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### 레이아웃

웹 사이트에서 교체되는 콘텐츠가 포함된 단일 레이아웃 템플릿 파일을 가지는 것이 일반적입니다. 레이아웃에 렌더링할 콘텐츠를 렌더링하려면 `render` 메서드에 선택적 매개변수를 전달할 수 있습니다.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

그러면 뷰에 `headerContent` 및 `bodyContent`로 저장된 변수가 있을 것입니다. 그런 다음 다음을 수행하여 레이아웃을 렌더링할 수 있습니다:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

템플릿 파일이 다음과 같은 경우:

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
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

## 사용자 정의 View 엔진

Flight를 통해 기본 view 엔진을 간단히 교체할 수 있습니다. 단순히 사용자 정의 view 클래스를 등록하면 됩니다.

### Smarty

뷰로 [Smarty](http://www.smarty.net/) 템플릿 엔진을 사용하는 방법은 다음과 같습니다:

```php
// Smarty 라이브러리 로드
require './Smarty/libs/Smarty.class.php';

// 뷰 클래스로 Smarty 등록
// 또한 Smarty를 로드하도록 콜백 함수 전달
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

완벽성을 위해 Flight의 기본 렌더링 메서드도 재정의해야 합니다:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

뷰로 [Latte](https://latte.nette.org/) 템플릿 엔진을 사용하는 방법은 다음과 같습니다:

```php

// 뷰 클래스로 Latte 등록
// Latte를 로드하도록 콜백 함수 전달
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // Latte가 템플릿을 캐시하는 위치입니다.
  // Latte의 멋진 점 중 하나는 템플릿을 수정할 때 캐시를 자동으로 새로 고칠 수 있다는 것입니다!
  $latte->setTempDirectory(__DIR__ . '/../cache/');

  // Latte에게 뷰의 루트 디렉토리 경로를 알려줍니다.
  $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Flight::render()를 올바르게 사용할 수 있도록 묶어 줍니다
Flight::map('render', function(string $template, array $data): void {
  // 이는 $latte_engine->render($template, $data); 와 비슷합니다.
  echo Flight::view()->render($template, $data);
});
```