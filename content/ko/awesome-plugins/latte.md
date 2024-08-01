# 라떼

라떼는 매우 사용하기 쉽고 PHP 구문에 가깝게 느껴지는 템플릿 엔진입니다. 트윅이나 스마티보다는 훨씬 가까운 느낌을 줍니다. 또한 매우 쉽게 확장하고 자체 필터 및 함수를 추가할 수 있습니다.

## 설치

Composer로 설치합니다.

```bash
composer require latte/latte
```

## 기본 구성

시작하기 위한 몇 가지 기본 구성 옵션이 있습니다. 더 자세한 내용은 [라떼 문서](https://latte.nette.org/en/guide)에서 확인할 수 있습니다.

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// 여기에 라떼가 템플릿을 캐시하여 속도를 높이는 위치입니다
	// 라떼의 멋진 점 중 하나는 템플릿을 변경할 때 자동으로 캐시를 새로 고칩니다!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 라떼에게 your views를 위한 루트 디렉토리가 어디에 있는지 알려줍니다.
	// $app->get('flight.views.path')는 config.php 파일에 설정되어 있습니다
	//   `__DIR__ . '/../views/'`와 같은 것을 할 수도 있습니다
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## 간단한 레이아웃 예제

여기에 레이아웃 파일의 간단한 예제가 있습니다. 이 파일은 다른 모든 뷰를 래핑하는 데 사용됩니다.

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
				<!-- 여기에 nav 요소를 넣으세요 -->
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

그리고 이제 해당 내용 블록 내부에 렌더링될 파일이 있습니다:

```html
<!-- app/views/home.latte -->
<!-- 이것은 이 파일이 layout.latte 파일 "안에" 있다는 것을 라떼에게 알려줍니다 -->
{extends layout.latte}

<!-- 레이아웃 안에서 내용이 렌더링될 내용을 작성합니다 -->
{block content}
	<h1>홈페이지</h1>
	<p>어플리케이션에 오신 것을 환영합니다!</p>
{/block}
```

그럼 함수 또는 컨트롤러 내부에서 이것을 렌더링할 때 다음과 같이 수행합니다:

```php
// 간단한 루트
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => '홈페이지'
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
			'title' => '홈페이지'
		]);
	}
}
```

라떼를 최대한 활용하는 방법에 대한 자세한 내용은 [라떼 문서](https://latte.nette.org/en/guide)를 참조하세요!