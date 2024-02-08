# 라떼

라떼는 매우 사용하기 쉬우며 PHP 구문에 더 가까운 느낌을 주는 완벽한 기능을 갖춘 템플릿 엔진입니다. Twig나 Smarty보다 쉽게 확장하여 사용자 정의 필터 및 함수를 추가할 수도 있습니다.

## 설치

컴포저로 설치하십시오.

```bash
composer require latte/latte
```

## 기본 구성

시작하는 데 도움이 되는 몇 가지 기본 구성 옵션이 있습니다. 더 많은 정보는 [Latte 문서](https://latte.nette.org/en/guide)를 참조하십시오.

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// 라떼가 템플릿을 캐시하여 속도를 높일 위치입니다
	// 라떼의 멋진 점 중 하나는 템플릿을 변경할 때 자동으로 캐시를 새로 고쳐줍니다!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 라떼에게 뷰의 루트 디렉토리가 어디에 있는지 알려줍니다.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## 간단한 레이아웃 예제

다음은 레이아웃 파일의 간단한 예제입니다. 이 파일은 다른 모든 뷰를 랩핑하는 데 사용됩니다.

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
				<!-- 여기에 내비게이션 요소 -->
			</nav>
		</header>
		<div id="content">
			<!-- 이것이 바로 마법 -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; 저작권
		</div>
	</body>
</html>
```

그리고 이 콘텐트 블록 안에 렌더링될 파일이 있습니다:

```html
<!-- app/views/home.latte -->
<!-- 이것은 이 파일이 layout.latte 파일 "안에" 있다고 라떼에게 알려줍니다 -->
{extends layout.latte}

<!-- 이 파일이 레이아웃 안의 콘텐트 블록 내부에 렌더링될 콘텐츠입니다 -->
{block content}
	<h1>홈 페이지</h1>
	<p>내 앱에 오신 것을 환영합니다!</p>
{/block}
```

그럼 함수나 컨트롤러에서 이를 렌더링하려면 다음과 같이합니다:

```php
// 간단한 루트
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
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
		Flight::latte()->render('home.latte', [
			'title' => '홈 페이지'
		]);
	}
}
```

[Latte 문서](https://latte.nette.org/en/guide)에서 라떼를 최대한 활용하는 방법에 대해 더 많은 정보를 확인하십시오!