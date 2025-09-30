# Latte

[Latte](https://latte.nette.org/en/guide)는 매우 사용하기 쉽고 Twig나 Smarty보다 PHP 문법에 더 가까운 느낌을 주는 완전한 기능을 갖춘 템플릿 엔진입니다. 또한 확장하기 쉽고 사용자 정의 필터와 함수를 추가할 수 있습니다.

## 설치

Composer를 사용하여 설치하세요.

```bash
composer require latte/latte
```

## 기본 구성

시작하기 위한 기본 구성 옵션이 있습니다. 이에 대해 더 자세히 읽으려면 [Latte 문서](https://latte.nette.org/en/guide)를 참조하세요.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Latte가 캐시를 저장하는 위치
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## 간단한 레이아웃 예제

다음은 레이아웃 파일의 간단한 예제입니다. 이는 다른 모든 뷰를 감싸는 데 사용될 파일입니다.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}My App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- 여기에 네비게이션 요소를 추가하세요 -->
			</nav>
		</header>
		<div id="content">
			<!-- 여기가 바로 마법입니다 -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

이제 그 콘텐츠 블록 내부에 렌더링될 파일입니다:

```html
<!-- app/views/home.latte -->
<!-- 이는 Latte에게 이 파일이 layout.latte 파일 "내부"에 있음을 알려줍니다 -->
{extends layout.latte}

<!-- 이는 레이아웃 내부 콘텐츠 블록에 렌더링될 콘텐츠입니다 -->
{block content}
	<h1>Home Page</h1>
	<p>Welcome to my app!</p>
{/block}
```

함수나 컨트롤러 내부에서 이를 렌더링할 때 다음과 같이 합니다:

```php
// 간단한 라우트
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => 'Home Page'
	]);
});

// 또는 컨트롤러를 사용하는 경우
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::render('home.latte', [
			'title' => 'Home Page'
		]);
	}
}
```

Latte를 최대한 활용하는 방법에 대한 자세한 정보는 [Latte 문서](https://latte.nette.org/en/guide)를 참조하세요!

## Tracy를 사용한 디버깅

_이 섹션에는 PHP 8.1+가 필요합니다._

[Tracy](https://tracy.nette.org/en/)를 사용하여 Latte 템플릿 파일을 바로 디버깅할 수도 있습니다! 이미 Tracy가 설치되어 있다면 Tracy에 Latte 확장을 추가해야 합니다.

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Latte가 캐시를 저장하는 위치
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// 이는 Tracy 디버그 바로가 활성화된 경우에만 확장을 추가합니다
	if (Debugger::$showBar === true) {
		// 여기에 Latte 패널을 Tracy에 추가합니다
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});
```