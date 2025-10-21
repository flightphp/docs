# CommentTemplate

[CommentTemplate](https://github.com/KnifeLemon/CommentTemplate)은 자산 컴파일, 템플릿 상속, 변수 처리 기능을 갖춘 강력한 PHP 템플릿 엔진입니다. 내장된 CSS/JS 압축 및 캐싱으로 템플릿을 간단하면서도 유연하게 관리할 수 있습니다.

## 기능

- **템플릿 상속**: 레이아웃 사용 및 다른 템플릿 포함
- **자산 컴파일**: 자동 CSS/JS 압축 및 캐싱
- **변수 처리**: 필터와 명령어를 사용한 템플릿 변수
- **Base64 인코딩**: 데이터 URI로 인라인 자산
- **Flight 프레임워크 통합**: Flight PHP 프레임워크와의 선택적 통합

## 설치

Composer를 사용하여 설치하세요.

```bash
composer require knifelemon/comment-template
```

## 기본 구성

시작하기 위한 기본 구성 옵션이 있습니다. 이에 대해 자세한 내용은 [CommentTemplate Repo](https://github.com/KnifeLemon/CommentTemplate)를 참조하세요.

### 방법 1: 콜백 함수 사용

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

$app->register('view', Engine::class, [], function (Engine $engine) use ($app) {
    // 템플릿 파일이 저장된 위치
    $engine->setTemplatesPath(__DIR__ . '/views');
    
    // 공용 자산이 제공되는 위치
    $engine->setPublicPath(__DIR__ . '/public');
    
    // 컴파일된 자산이 저장되는 위치
    $engine->setAssetPath('assets');
    
    // 템플릿 파일 확장자
    $engine->setFileExtension('.php');
});

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

### 방법 2: 생성자 매개변수 사용

```php
<?php
require_once 'vendor/autoload.php';

use KnifeLemon\CommentTemplate\Engine;

$app = Flight::app();

// __construct(string $publicPath = "", string $skinPath = "", string $assetPath = "", string $fileExtension = "")
$app->register('view', Engine::class, [
    __DIR__ . '/public',    // publicPath - 자산이 제공되는 위치
    __DIR__ . '/views',     // skinPath - 템플릿 파일이 저장된 위치  
    'assets',               // assetPath - 컴파일된 자산이 저장되는 위치
    '.php'                  // fileExtension - 템플릿 파일 확장자
]);

$app->map('render', function(string $template, array $data) use ($app): void {
    echo $app->view()->render($template, $data);
});
```

## 템플릿 지시어

### 레이아웃 상속

공통 구조를 생성하기 위해 레이아웃을 사용하세요:

**layout/global_layout.php**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <!--@contents-->
</body>
</html>
```

**view/page.php**:
```html
<!--@layout(layout/global_layout)-->
<h1>{$title}</h1>
<p>{$content}</p>
```

### 자산 관리

#### CSS 파일
```html
<!--@css(/css/styles.css)-->          <!-- 압축 및 캐싱됨 -->
<!--@cssSingle(/css/critical.css)-->  <!-- 단일 파일, 압축되지 않음 -->
```

#### JavaScript 파일
CommentTemplate은 다양한 JavaScript 로딩 전략을 지원합니다:

```html
<!--@js(/js/script.js)-->             <!-- 압축됨, 하단에 로드 -->
<!--@jsAsync(/js/analytics.js)-->     <!-- 압축됨, 하단에 async로 로드 -->
<!--@jsDefer(/js/utils.js)-->         <!-- 압축됨, 하단에 defer로 로드 -->
<!--@jsTop(/js/critical.js)-->        <!-- 압축됨, head에 로드 -->
<!--@jsTopAsync(/js/tracking.js)-->   <!-- 압축됨, head에 async로 로드 -->
<!--@jsTopDefer(/js/polyfill.js)-->   <!-- 압축됨, head에 defer로 로드 -->
<!--@jsSingle(/js/widget.js)-->       <!-- 단일 파일, 압축되지 않음 -->
<!--@jsSingleAsync(/js/ads.js)-->     <!-- 단일 파일, 압축되지 않음, async -->
<!--@jsSingleDefer(/js/social.js)-->  <!-- 단일 파일, 압축되지 않음, defer -->
```

#### CSS/JS 파일 내 자산 지시어

CommentTemplate은 컴파일 중 CSS 및 JavaScript 파일 내 자산 지시어를 처리합니다:

**CSS 예시:**
```css
/* CSS 파일 내 */
/* @font-face {
    font-family: 'CustomFont';
    src: url('<!--@asset(fonts/custom.woff2)-->') format('woff2');
}

.background-image {
    background: url('<!--@asset(images/bg.jpg)-->');
}

.inline-icon {
    background: url('<!--@base64(icons/star.svg)-->');
}
```

**JavaScript 예시:**
```javascript
/* JS 파일 내 */
const fontUrl = '<!--@asset(fonts/custom.woff2)-->';
const imageData = '<!--@base64(images/icon.png)-->';
```

#### Base64 인코딩
```html
<!--@base64(images/logo.png)-->       <!-- 데이터 URI로 인라인 -->
```
** 예시: **
```html
<!-- 더 빠른 로딩을 위해 작은 이미지를 데이터 URI로 인라인 -->
<img src="<!--@base64(images/logo.png)-->" alt="Logo">
<div style="background-image: url('<!--@base64(icons/star.svg)-->');">
    배경으로 작은 아이콘
</div>
```

#### 자산 복사
```html
<!--@asset(images/photo.jpg)-->       <!-- 단일 자산을 공용 디렉토리로 복사 -->
<!--@assetDir(assets)-->              <!-- 전체 디렉토리를 공용 디렉토리로 복사 -->
```
** 예시: **
```html
<!-- 정적 자산 복사 및 참조 -->
<img src="<!--@asset(images/hero-banner.jpg)-->" alt="Hero Banner">
<a href="<!--@asset(documents/brochure.pdf)-->" download>브로슈어 다운로드</a>

<!-- 전체 디렉토리 복사 (폰트, 아이콘 등) -->
<!--@assetDir(assets/fonts)-->
<!--@assetDir(assets/icons)-->
```

### 템플릿 포함
```html
<!--@import(components/header)-->     <!-- 다른 템플릿 포함 -->
```
** 예시: **
```html
<!-- 재사용 가능한 컴포넌트 포함 -->
<!--@import(components/header)-->

<main>
    <h1>웹사이트에 오신 것을 환영합니다</h1>
    <!--@import(components/sidebar)-->
    
    <div class="content">
        <p>메인 콘텐츠가 여기에...</p>
    </div>
</main>

<!--@import(components/footer)-->
```

### 변수 처리

#### 기본 변수
```html
<h1>{$title}</h1>
<p>{$description}</p>
```

#### 변수 필터
```html
{$title|upper}                       <!-- 대문자로 변환 -->
{$content|lower}                     <!-- 소문자로 변환 -->
{$html|striptag}                     <!-- HTML 태그 제거 -->
{$text|escape}                       <!-- HTML 이스케이프 -->
{$multiline|nl2br}                   <!-- 개행을 <br>로 변환 -->
{$html|br2nl}                        <!-- <br> 태그를 개행으로 변환 -->
{$description|trim}                  <!-- 공백 제거 -->
{$subject|title}                     <!-- 제목 형식으로 변환 -->
```

#### 변수 명령어
```html
{$title|default=Default Title}       <!-- 기본값 설정 -->
{$name|concat= (Admin)}              <!-- 텍스트 연결 -->
```

#### 변수 명령어
```html
{$content|striptag|trim|escape}      <!-- 여러 필터 체인 -->
```

## 예시 프로젝트 구조

```
project/
├── source/
│   ├── layouts/
│   │   └── default.php
│   ├── components/
│   │   ├── header.php
│   │   └── footer.php
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── custom.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.min.js
│   └── homepage.php
├── public/
│   └── assets/           # 생성된 자산
│       ├── css/
│       └── js/
└── vendor/
```