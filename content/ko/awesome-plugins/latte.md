# 라떼

라떼는 매우 쉽게 사용할 수 있고 PHP 구문에 가까운 느낌을 주는 Twig나 Smarty보다 풀 기능을 갖춘 템플릿 엔진입니다. 또한 확장하고 자체 필터 및 함수를 추가하는 것도 매우 쉽습니다.

## 설치

컴포저로 설치하세요.

```bash
composer require latte/latte
```

## 기본 구성

시작하기 위한 몇 가지 기본 구성 옵션이 있습니다. 더 자세한 내용은 [Latte Documentation](https://latte.nette.org/en/guide)에서 확인할 수 있습니다.

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// 여기가 라떼가 템플릿을 캐시하여 속도를 높일 위치입니다
	// 라떼의 멋진 기능 중 하나는 템플릿을 수정할 때 자동으로 캐시를 새로 고쳐준다는 것입니다!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 라떼에게 보기의 루트 디렉토리가 어디에 있는지 알려줍니다.
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## 간단한 레이아웃 예제

여기에 레이아웃 파일의 간단한 예제가 있습니다. 이 파일은 다른 모든 보기를 래핑하는 데 사용될 것입니다.

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
				<!-- 네비게이션 요소를 여기에 추가하세요 -->
			</nav>
		</header>
		<div id="content">
			<!-- 이게 바로 마법입니다 -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; 저작권
		</div>
	</body>
</html>
```

그리고 이제 해당 컨텐츠 블록 내에서 렌더링될 파일이 준비되어 있습니다:

```html
<!-- app/views/home.latte -->
<!-- 이를 통해 라떼에게 이 파일이 layout.latte 파일의 "내부"에 있다는 것을 알려줍니다 -->
{extends layout.latte}

<!-- 이 컨텐츠가 layout의 내부에서 콘텐츠 블록 내에서 렌더링될 내용입니다 -->
{block content}
	<h1>홈 페이지</h1>
	<p>내 앱에 오신 것을 환영합니다!</p>
{/block}
```

그럼 이를 함수 또는 컨트롤러 내에서 렌더링하려면 다음과 같이 수행하세요:

```php
// 간단한 경로
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

[Latte Documentation](https://latte.nette.org/en/guide)에서 라떼를 최대한 활용하는 방법에 대해 자세히 알아보세요!