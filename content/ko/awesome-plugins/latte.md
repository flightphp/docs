# 라떼

[라떼](https://latte.nette.org/en/guide)는 매우 쉽게 사용할 수 있으며 Twig 또는 Smarty보다 PHP 구문에 가깝게 느껴지는 완전한 기능의 템플릿 엔진입니다. 또한 매우 쉽게 확장하고 자체 필터 및 함수를 추가할 수 있습니다.

## 설치

컴포저로 설치하세요.

```bash
composer require latte/latte
```

## 기본 구성

시작하기 위한 몇 가지 기본 구성 옵션이 있습니다. 자세한 내용은 [라떼 문서](https://latte.nette.org/en/guide)에서 확인할 수 있습니다.

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// 라떼가 템플릿을 캐시하여 속도를 높일 위치입니다
	// 라떼의 멋진 기능 중 하나는 템플릿을 변경할 때 자동으로 캐시를 새로 고친다는 것입니다!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 라떼에게 보기의 루트 디렉터리가 어디에 있는지 알려줍니다.
	// $app->get('flight.views.path')는 config.php 파일에서 설정됩니다
	//   그냥 `__DIR__ . '/../views/'`와 같이 할 수도 있습니다
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## 간단한 레이아웃 예제

다른 모든 보기를 래핑하는 데 사용될 파일의 간단한 예제입니다.

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
				<!-- 여기에 당신의 nav 요소 -->
			</nav>
		</header>
		<div id="content">
			<!-- 여기에 마법이 있습니다 -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; 저작권
		</div>
	</body>
</html>
```

이제 해당 콘텐츠 블록 내부에 렌더링될 파일이 있습니다.

```html
<!-- app/views/home.latte -->
<!-- 라떼에게이 파일이 layout.latte 파일 "내부"에 있다고 알려줍니다 -->
{extends layout.latte}

<!-- 레이아웃 내부에서 렌더링될 콘텐츠입니다 -->
{block content}
	<h1>홈 페이지</h1>
	<p>내 앱에 오신 것을 환영합니다!</p>
{/block}
```

그럼 함수나 컨트롤러 내에서 이를 렌더링할 때 다음과 같이 수행할 수 있습니다.

```php
// 간단한 루트
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => '홈 페이지'
	]);
});

// 또는 컨트롤러를 사용하는 경우
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => '홈 페이지'
		]);
	}
}
```

[라떼 문서](https://latte.nette.org/en/guide)에서 라떼를 최대한 활용하는 방법에 대해 자세히 알아보세요!