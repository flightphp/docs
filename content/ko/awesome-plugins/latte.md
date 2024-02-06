# 라떼

라떼는 매우 쉽게 사용할 수 있고 Twig 또는 Smarty보다 PHP 구문에 더 가까운 완전한 기능의 템플릿 엔진입니다. 또한 매우 쉽게 확장하고 자체 필터 및 기능을 추가할 수 있습니다.

## 설치

컴포저로 설치하세요.

```bash
composer require latte/latte
```

## 기본 구성

시작하기 위한 일부 기본 구성 옵션이 있습니다. [라떼 문서](https://latte.nette.org/en/guide)에서 자세히 알아볼 수 있습니다.

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// 라떼가 템플릿을 더 빠르게 하기 위해 캐시할 위치입니다
	// 라떼의 멋진 기능 중 하나는 템플릿을 변경할 때 캐시를 자동으로 새로 고침한다는 것입니다!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 뷰의 루트 디렉토리가 될 위치를 라떼에게 알려주세요.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
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
				<!-- nav 요소들이 여기에 있습니다 -->
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

그리고 이제 해당 콘텐츠 블록 내에서 렌더링될 파일이 있습니다:

```html
<!-- app/views/home.latte -->
<!-- 이것은 라떼에게 이 파일이 layout.latte 파일 "안에" 있다고 알려줍니다 -->
{extends layout.latte}

<!-- 이 콘텐츠는 레이아웃 내부의 content 블록 안에 렌더링됩니다 -->
{block content}
	<h1>홈 페이지</h1>
	<p>내 앱에 오신 것을 환영합니다!</p>
{/block}
```

그런 다음이 함수 또는 컨트롤러 내에서 이를 렌더링할 때 다음과 같이 수행할 수 있습니다:

```php
// 간단한 라우트
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

라떼를 최대한 활용하는 방법에 대한 자세한 정보는 [라떼 문서](https://latte.nette.org/en/guide)를 참조하십시오!