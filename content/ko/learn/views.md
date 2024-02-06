# 뷰(Views)

Flight는 기본적인 템플릿 기능을 제공합니다. 뷰
템플릿을 표시하려면 템플릿 파일의 이름과 선택적인
템플릿 데이터를 사용하여 `render` 메소드를 호출하십시오:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

전달한 템플릿 데이터는 자동으로 템플릿에 주입되며
로컬 변수처럼 참조할 수 있습니다. 템플릿 파일은 단순히 PHP 파일입니다. `hello.php` 템플릿 파일의 내용이 다음과 같다면:

```php
Hello, <?= $name ?>!
```

출력은 다음과 같을 것입니다:

```
Hello, Bob!
```

뷰 변수를 수동으로 설정할 수도 있습니다.

```php
Flight::view()->set('name', 'Bob');
```

변수 `name`은 이제 모든 뷰에서 사용할 수 있습니다. 따라서 다음과 같이 할 수 있습니다:

```php
Flight::render('hello');
```

`render` 메소드에서 템플릿 이름을 지정할 때 `.php` 확장자를 생략할 수 있음을 유의하십시오.

Flight는 기본적으로 템플릿 파일을 위해 `views` 디렉토리를 찾습니다. 다음 구성을 설정하여 템플릿의 대체 경로를 설정할 수 있습니다:

```php
Flight::set('flight.views.path', '/path/to/views');
```

## 레이아웃(Layouts)

웹 사이트에 단일 레이아웃 템플릿 파일이 있는 것이 일반적입니다. 레이아웃에 사용할 콘텐츠를 렌더링하려면 `render` 메소드에 선택적 매개변수를 전달할 수 있습니다.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

그럼 뷰는 `headerContent`와 `bodyContent` 라는 변수로 저장되어 있을 것입니다. 그럼 다음과 같이 레이아웃을 렌더링할 수 있습니다:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

다음과 같은 템플릿 파일이 있다면:

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

## 사용자 정의 뷰(Custom Views)

Flight를 통해 기본 뷰 엔진을 간단히 교체할 수 있습니다. 여기서는 [Smarty](http://www.smarty.net/) 템플릿 엔진을 뷰로 사용하는 방법을 보여드리겠습니다:

```php
// Smarty 라이브러리 로드
require './Smarty/libs/Smarty.class.php';

// 뷰 클래스로 Smarty 등록
// 또한 로드 시 Smarty를 구성하는 콜백 함수를 전달합니다
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

완전성을 위해 Flight의 기본 `render` 메소드를 재정의해야 합니다:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```