# 배우기

이 페이지는 Flight 학습을 위한 가이드입니다. 프레임워크의 기본 사항과 사용 방법을 다룹니다.

## <a name="routing"></a> 라우팅

Flight의 라우팅은 URL 패턴을 콜백 함수와 일치시킴으로써 이루어집니다.

``` php
Flight::route('/', function(){
    echo '안녕하세요, 세계!';
});
```

콜백은 호출할 수 있는 모든 객체일 수 있습니다. 따라서 일반 함수를 사용할 수 있습니다:

``` php
function hello(){
    echo '안녕하세요, 세계!';
}

Flight::route('/', 'hello');
```

또는 클래스 메서드를 사용할 수 있습니다:

``` php
class Greeting {
    public static function hello() {
        echo '안녕하세요, 세계!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

또는 객체 메서드를 사용할 수 있습니다:

``` php
class Greeting
{
    public function __construct() {
        $this->name = '존 도';
    }

    public function hello() {
        echo "안녕하세요, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

라우트는 정의된 순서대로 일치합니다. 요청을 일치시키는 첫 번째 라우트가 호출됩니다.

### 메서드 라우팅

기본적으로 라우트 패턴은 모든 요청 메서드와 일치합니다. URL 앞에 식별자를 배치함으로써 특정 메서드에 응답할 수 있습니다.

``` php
Flight::route('GET /', function(){
    echo 'GET 요청을 받았습니다.';
});

Flight::route('POST /', function(){
    echo 'POST 요청을 받았습니다.';
});
```

여러 메서드를 단일 콜백에 매핑하려면 `|` 구분 기호를 사용할 수 있습니다:

``` php
Flight::route('GET|POST /', function(){
    echo 'GET 또는 POST 요청을 받았습니다.';
});
```

### 정규 표현식

라우트에서 정규 표현식을 사용할 수 있습니다:

``` php
Flight::route('/user/[0-9]+', function(){
    // 이것은 /user/1234와 일치합니다
});
```

### 명명된 매개변수

라우트에서 명명된 매개변수를 지정하여 콜백 함수로 전달할 수 있습니다.

``` php
Flight::route('/@name/@id', function($name, $id){
    echo "안녕하세요, $name ($id)!";
});
```

`:` 구분 기호를 사용하여 명명된 매개변수와 정규 표현식을 함께 사용할 수도 있습니다:

``` php
Flight::route('/@name/@id:[0-9]{3}', function($name, $id){
    // 이것은 /bob/123과 일치합니다
    // 그러나 /bob/12345와는 일치하지 않습니다
});
```

### 선택적 매개변수

세그먼트를 괄호로 감싸 선택적으로 매칭될 수 있는 명명된 매개변수를 지정할 수 있습니다.

``` php
Flight::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // 다음 URL과 일치합니다:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

일치하지 않는 선택적 매개변수는 NULL로 전달됩니다.

### 와일드카드

일치하는 것은 개별 URL 세그먼트에서만 수행됩니다. 여러 세그먼트를 일치시켜야 하는 경우 `*` 와일드카드를 사용할 수 있습니다.

``` php
Flight::route('/blog/*', function(){
    // 이것은 /blog/2000/02/01과 일치합니다
});
```

모든 요청을 단일 콜백으로 라우팅하려면 다음과 같이 할 수 있습니다:

``` php
Flight::route('*', function(){
    // 무언가를 처리합니다
});
```

### 전달

콜백 함수에서 `true`를 반환하여 다음 일치하는 라우트로 실행을 전달할 수 있습니다.

``` php
Flight::route('/user/@name', function($name){
    // 어떤 조건을 검사합니다
    if ($name != "밥") {
        // 다음 라우트로 계속 진행
        return true;
    }
});

Flight::route('/user/*', function(){
    // 이것이 호출됩니다
});
```

### 라우트 정보

일치하는 라우트 정보를 검사하려면 라우트 메서드에서 세 번째 매개변수로 `true`를 전달하여 라우트 객체가 콜백으로 전달되도록 요청할 수 있습니다. 라우트 객체는 항상 콜백 함수에 전달되는 마지막 매개변수입니다.

``` php
Flight::route('/', function($route){
    // 일치한 HTTP 메서드 배열
    $route->methods;

    // 명명된 매개변수 배열
    $route->params;

    // 일치하는 정규 표현식
    $route->regex;

    // URL 패턴에서 사용된 '*'의 내용을 포함합니다
    $route->splat;
}, true);
```

### 라우트 그룹화

관련 라우트를 함께 그룹화해야 하는 경우가 있을 수 있습니다(예: `/api/v1`). `group` 메서드를 사용하여 이를 수행할 수 있습니다:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users와 일치합니다
  });

  Flight::route('/posts', function () {
	// /api/v1/posts와 일치합니다
  });
});
```

그룹을 중첩할 수도 있습니다:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()는 변수를 가져오고 라우트를 설정하지 않습니다! 아래 객체 컨텍스트를 참조하세요
	Flight::route('GET /users', function () {
	  // GET /api/v1/users와 일치합니다
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/posts와 일치합니다
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/posts와 일치합니다
	});
  });
  Flight::group('/v2', function () {

	// Flight::get()는 변수를 가져오고 라우트를 설정하지 않습니다! 아래 객체 컨텍스트를 참조하세요
	Flight::route('GET /users', function () {
	  // GET /api/v2/users와 일치합니다
	});
  });
});
```

#### 객체 컨텍스트로 그룹화

다음과 같이 `Engine` 객체와 함께 라우트 그룹화를 여전히 사용할 수 있습니다:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// GET /api/v1/users와 일치합니다
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts와 일치합니다
  });
});
```

### 라우트 별칭

라우트에 별칭을 할당하여 URL을 나중에 코드에서 동적으로 생성할 수 있습니다(예: 템플릿의 경우).

```php
Flight::route('/users/@id', function($id) { echo '사용자:'.$id; }, false, 'user_view');

// 코드의 다른 부분에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

이는 URL이 변경된 경우에 특히 유용합니다. 위 예제에서 사용자가 `/admin/users/@id`로 이동되었다고 가정해 보겠습니다. 별칭을 설정하면 별칭을 참조하는 모든 곳에서 변경할 필요 없이 별칭이 이제 `/admin/users/5`를 반환합니다.

라우트 별칭은 그룹에서도 여전히 작동합니다:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo '사용자:'.$id; }, false, 'user_view');
});

// 코드의 다른 부분에서
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'를 반환합니다
```

## <a name="extending"></a> 확장

Flight는 확장 가능한 프레임워크로 설계되었습니다. 프레임워크는 기본 메서드와 구성 요소 세트를 제공하지만, 사용자가 자신의 메서드를 매핑하고, 자신의 클래스를 등록하거나, 기존 클래스 및 메서드를 재정의할 수 있습니다.

### 메서드 매핑

사용자 정의 메서드를 매핑하려면 `map` 함수를 사용합니다:

``` php
// 메서드를 매핑합니다
Flight::map('hello', function($name){
    echo "안녕하세요 $name!";
});

// 사용자 정의 메서드를 호출합니다
Flight::hello('밥');
```

### 클래스 등록

자신의 클래스를 등록하려면 `register` 함수를 사용합니다:

``` php
// 클래스를 등록합니다
Flight::register('user', 'User');

// 클래스 인스턴스를 가져옵니다
$user = Flight::user();
```

등록 메서드는 클래스 생성자에 매개변수를 전달할 수도 있습니다. 따라서 사용자 정의 클래스를 로드할 때 자동으로 초기화됩니다. 생성자 매개변수는 추가 배열을 전달하여 정의할 수 있습니다. 데이터베이스 연결을 로드하는 예는 다음과 같습니다:

``` php
// 생성자 매개변수와 함께 클래스를 등록합니다
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// 클래스 인스턴스를 가져옵니다
// 이것은 정의된 매개변수로 객체를 생성합니다
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

추가 콜백 매개변수를 전달하면 클래스 생성 직후에 실행됩니다. 이를 통해 새 객체에 대한 모든 설정 절차를 수행할 수 있습니다. 콜백 함수는 새 객체의 인스턴스를 매개변수로 받습니다.

``` php
// 콜백은 생성된 객체를 받습니다
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'),
  function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

기본적으로 클래스를 로드할 때마다 공유 인스턴스를 받게 됩니다. 클래스의 새 인스턴스를 얻으려면 `false`를 매개변수로 전달하면 됩니다:

``` php
// 클래스의 공유 인스턴스
$shared = Flight::db();

// 클래스의 새 인스턴스
$new = Flight::db(false);
```

매핑된 메서드는 등록된 클래스보다 우선합니다. 동일한 이름으로 둘 다 선언하면 매핑된 메서드만 호출됩니다.

## <a name="overriding"></a> 재정의

Flight는 코드를 수정할 필요 없이 기본 기능을 재정의할 수 있도록 합니다.

예를 들어, Flight가 URL를 라우트에 일치시킬 수 없을 때 `notFound` 메서드를 호출하여 일반적인 `HTTP 404` 응답을 보냅니다. 이 동작을 재정의하려면 `map` 메서드를 사용합니다:

``` php
Flight::map('notFound', function(){
    // 사용자 정의 404 페이지를 표시합니다
    include 'errors/404.html';
});
```

Flight는 또한 프레임워크의 핵심 구성 요소를 교체할 수 있습니다. 예를 들어 기본 라우터 클래스를 사용자 정의 클래스로 교체할 수 있습니다:

``` php
// 사용자 정의 클래스를 등록합니다
Flight::register('router', 'MyRouter');

// Flight가 Router 인스턴스를 로드할 때, 사용자 클래스를 로드합니다
$myrouter = Flight::router();
```

그러나 `map` 및 `register`와 같은 프레임워크 메서드는 재정의할 수 없습니다. 이를 시도하면 오류가 발생합니다.

## <a name="filtering"></a> 필터링

Flight는 메서드를 호출하기 전후에 필터링할 수 있습니다. 암기할 필요가 있는 정의된 훅이 없습니다. 기본 프레임워크 메서드와 매핑한 사용자 정의 메서드 모두를 필터링할 수 있습니다.

필터 함수는 다음과 같습니다:

``` php
function(&$params, &$output) {
    // 필터 코드
}
```

전달된 변수를 사용하여 입력 매개변수 및/또는 출력을 조작할 수 있습니다.

메서드 전에 필터를 실행하려면:

``` php
Flight::before('start', function(&$params, &$output){
    // 무언가를 처리합니다
});
```

메서드 후에 필터를 실행하려면:

``` php
Flight::after('start', function(&$params, &$output){
    // 무언가를 처리합니다
});
```

원하는 만큼 많은 필터를 메서드에 추가할 수 있습니다. 필터는 선언된 순서대로 호출됩니다.

필터링 프로세스의 예는 다음과 같습니다:

``` php
// 사용자 정의 메서드를 매핑합니다
Flight::map('hello', function($name){
    return "안녕하세요, $name!";
});

// 전에 필터를 추가합니다
Flight::before('hello', function(&$params, &$output){
    // 매개변수를 조작합니다
    $params[0] = '프레드';
});

// 후에 필터를 추가합니다
Flight::after('hello', function(&$params, &$output){
    // 출력을 조작합니다
    $output .= " 좋은 하루 되세요!";
});

// 사용자 정의 메서드를 호출합니다
echo Flight::hello('밥');
```

이 출력은 다음과 같아야 합니다:

``` html
안녕하세요 프레드! 좋은 하루 되세요!
```

여러 필터를 정의한 경우 필터 함수 중 하나에서 `false`를 반환하여 체인을 끊을 수 있습니다:

``` php
Flight::before('start', function(&$params, &$output){
    echo '하나';
});

Flight::before('start', function(&$params, &$output){
    echo '둘';

    // 이것은 체인을 종료합니다
    return false;
});

// 이것은 호출되지 않습니다
Flight::before('start', function(&$params, &$output){
    echo '셋';
});
```

`map` 및 `register`와 같은 핵심 메서드는 직접 호출되므로 필터링할 수 없습니다.

## <a name="variables"></a> 변수

Flight는 변수를 저장하여 애플리케이션 어디에서나 사용할 수 있도록 합니다.

``` php
// 변수를 저장합니다
Flight::set('id', 123);

// 애플리케이션의 다른 부분에서
$id = Flight::get('id');
```

변수가 설정되었는지 확인하려면 다음과 같이 할 수 있습니다:

``` php
if (Flight::has('id')) {
     // 무언가를 처리합니다
}
```

변수를 지우려면:

``` php
// id 변수를 지웁니다
Flight::clear('id');

// 모든 변수를 지웁니다
Flight::clear();
```

Flight는 구성 목적으로도 변수를 사용합니다.

``` php
Flight::set('flight.log_errors', true);
```

## <a name="views"></a> 뷰

Flight는 기본적인 템플릿 기능을 기본으로 제공합니다. 뷰 템플릿을 표시하려면 템플릿 파일의 이름과 선택적 템플릿 데이터를 사용하여 `render` 메서드를 호출합니다:

``` php
Flight::render('hello.php', array('name' => '밥'));
```

제공된 템플릿 데이터는 템플릿에 자동으로 주입되며, 로컬 변수처럼 참조할 수 있습니다. 템플릿 파일은 단순히 PHP 파일입니다. `hello.php` 템플릿 파일의 내용이:

``` php
안녕하세요, '<?php echo $name; ?>'!
```

출력은 다음과 같습니다:

``` html
안녕하세요, 밥!
```

또한 `set` 메서드를 사용하여 수동으로 뷰 변수를 설정할 수 있습니다:

``` php
Flight::view()->set('name', '밥');
```

`name` 변수가 이제 모든 뷰에서 사용할 수 있습니다. 따라서 다음과 같이 간단하게 할 수 있습니다:

``` php
Flight::render('hello');
```

`render` 메서드에서 템플릿의 이름을 지정할 때 `.php` 확장자를 생략할 수 있습니다.

기본적으로 Flight는 템플릿 파일을 위해 `views` 디렉터리를 찾습니다. 다음 구성을 설정하여 템플릿에 대한 대체 경로를 설정할 수 있습니다:

``` php
Flight::set('flight.views.path', '/path/to/views');
```

### 레이아웃

웹사이트는 일반적으로 바뀌는 콘텐츠가 있는 단일 레이아웃 템플릿 파일을 가지고 있는 경우가 많습니다. 레이아웃에 사용될 콘텐츠를 렌더링하려면 `render` 메서드에 선택적 매개변수를 전달할 수 있습니다.

``` php
Flight::render('header', array('heading' => '안녕하세요'), 'header_content');
Flight::render('body', array('body' => '세상'), 'body_content');
```

그런 다음 뷰는 `header_content` 및 `body_content`라는 저장된 변수를 가집니다. 레이아웃을 렌더링하려면 다음과 같이 할 수 있습니다:

``` php
Flight::render('layout', array('title' => '홈 페이지'));
```

템플릿 파일이 다음과 같이 생겼다면:

`header.php`:

``` php
<h1><?php echo $heading; ?></h1>
```

`body.php`:

``` php
<div><?php echo $body; ?></div>
```

`layout.php`:

``` php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

출력은 다음과 같습니다:

``` html
<html>
<head>
<title>홈 페이지</title>
</head>
<body>
<h1>안녕하세요</h1>
<div>세상</div>
</body>
</html>
```

### 사용자 정의 뷰

Flight는 기본 뷰 엔진을 교체할 수 있도록 사용자가 자신의 뷰 클래스를 등록할 수 있습니다. 다음은 [Smarty](http://www.smarty.net/) 템플릿 엔진을 뷰에 사용하는 방법입니다:

``` php
// Smarty 라이브러리 로드
require './Smarty/libs/Smarty.class.php';

// Smarty를 뷰 클래스로 등록
// 로드 시 Smarty를 구성하기 위해 콜백 함수도 전달합니다
Flight::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// 템플릿 데이터 할당
Flight::view()->assign('name', '밥');

// 템플릿 표시
Flight::view()->display('hello.tpl');
```

완전성을 위해 Flight의 기본 렌더 메서드를 재정의하는 것도 좋습니다:

``` php
Flight::map('render', function($template, $data){
    Flight::view()->assign($data);
    Flight::view()->display($template);
});
```

## <a name="errorhandling"></a> 오류 처리

### 오류 및 예외

모든 오류와 예외는 Flight에 의해 포착되어 `error` 메서드에 전달됩니다. 기본 동작은 일반 `HTTP 500 내부 서버 오류` 응답을 보내고 오류 정보를 제공합니다.

이 동작을 자신의 필요에 맞게 재정의할 수 있습니다:

``` php
Flight::map('error', function(Exception $ex){
    // 오류 처리
    echo $ex->getTraceAsString();
});
```

기본적으로 오류는 웹 서버에 기록되지 않습니다. 구성을 변경하여 이를 활성화할 수 있습니다:

``` php
Flight::set('flight.log_errors', true);
```

### 찾을 수 없음

URL을 찾을 수 없을 때 Flight는 `notFound` 메서드를 호출합니다. 기본 동작은 간단한 메시지와 함께 `HTTP 404 찾을 수 없음` 응답을 보내는 것입니다.

이 동작을 자신의 필요에 맞게 재정의할 수 있습니다:

``` php
Flight::map('notFound', function(){
    // 찾을 수 없음 처리
});
```

## <a name="redirects"></a> 리디렉션

현재 요청을 `redirect` 메서드를 사용하여 새로운 URL로 리디렉션할 수 있습니다:

``` php
Flight::redirect('/new/location');
```

기본적으로 Flight는 HTTP 303 상태 코드를 보냅니다. 선택적으로 사용자 지정 코드를 설정할 수 있습니다:

``` php
Flight::redirect('/new/location', 401);
```

## <a name="requests"></a> 요청

Flight는 HTTP 요청을 단일 객체로 캡슐화하며, 다음과 같이 접근할 수 있습니다:

``` php
$request = Flight::request();
```

요청 객체는 다음과 같은 속성을 제공합니다:

``` html
url - 요청된 URL
base - URL의 상위 하위 디렉터리
method - 요청 메서드 (GET, POST, PUT, DELETE)
referrer - 리퍼러 URL
ip - 클라이언트의 IP 주소
ajax - 요청이 AJAX 요청인지 여부
scheme - 서버 프로토콜 (http, https)
user_agent - 브라우저 정보
type - 콘텐츠 유형
length - 콘텐츠 길이
query - 쿼리 문자열 매개변수
data - POST 데이터 또는 JSON 데이터
cookies - 쿠키 데이터
files - 업로드된 파일
secure - 연결이 보안인지 여부
accept - HTTP 수락 매개변수
proxy_ip - 클라이언트의 프록시 IP 주소
```

`query`, `data`, `cookies`, 및 `files` 속성에 배열 또는 객체로 접근할 수 있습니다.

따라서 쿼리 문자열 매개변수를 얻으려면 다음과 같이 할 수 있습니다:

``` php
$id = Flight::request()->query['id'];
```

또는 다음과 같이 할 수 있습니다:

``` php
$id = Flight::request()->query->id;
```

### RAW 요청 본문

예를 들어 PUT 요청을 처리할 때 원시 HTTP 요청 본문을 얻으려면 다음과 같이 할 수 있습니다:

``` php
$body = Flight::request()->getBody();
```

### JSON 입력

`application/json` 유형과 데이터 `{"id": 123}`로 요청을 보내면 `data` 속성에서 사용할 수 있습니다:

``` php
$id = Flight::request()->data->id;
```

## <a name="stopping"></a> 중지

`halt` 메서드를 호출하여 언제든지 프레임워크를 중지할 수 있습니다:

``` php
Flight::halt();
```

선택적 `HTTP` 상태 코드와 메시지를 지정할 수도 있습니다:

``` php
Flight::halt(200, '잠시만 기다려 주세요...');
```

`halt`를 호출하면 그 시점까지의 모든 응답 콘텐츠가 버려집니다. 프레임워크를 중지하고 현재 응답을 출력하려면 `stop` 메서드를 사용합니다:

``` php
Flight::stop();
```

## <a name="httpcaching"></a> HTTP 캐싱

Flight는 HTTP 수준 캐싱을 기본적으로 지원합니다. 캐싱 조건이 충족되면 Flight는 HTTP `304 수정되지 않음` 응답을 반환합니다. 클라이언트가 동일한 리소스를 다음에 요청할 때는 로컬 캐시된 버전을 사용하도록 요청받게 됩니다.

### 마지막 수정

`lastModified` 메서드를 사용하여 UNIX 타임스탬프를 전달하여 페이지가 마지막으로 수정된 날짜와 시간을 설정할 수 있습니다. 클라이언트는 마지막 수정 값이 변경될 때까지 캐시를 계속 사용합니다.

``` php
Flight::route('/news', function(){
    Flight::lastModified(1234567890);
    echo '이 콘텐츠는 캐시됩니다.';
});
```

### ETag

`ETag` 캐싱은 `Last-Modified`와 유사하지만 리소스에 원하는 아무 ID를 지정할 수 있습니다:

``` php
Flight::route('/news', function(){
    Flight::etag('my-unique-id');
    echo '이 콘텐츠는 캐시됩니다.';
});
```

`lastModified` 또는 `etag`를 호출하면 캐시 값을 설정하고 확인합니다. 요청 간에 캐시 값이 동일하면 Flight는 즉시 `HTTP 304` 응답을 보내고 처리를 중지합니다.

## <a name="json"></a> JSON

Flight는 JSON 및 JSONP 응답을 보내는 것을 지원합니다. JSON 응답을 보내려면 JSON 인코딩할 데이터를 전달합니다:

``` php
Flight::json(array('id' => 123));
```

JSONP 요청의 경우, 콜백 함수를 정의하는 데 사용하는 쿼리 매개변수 이름을 선택적으로 전달할 수 있습니다:

``` php
Flight::jsonp(array('id' => 123), 'q');
```

따라서 `?q=my_func`를 사용하여 GET 요청을 할 때 다음과 같은 출력을 받아야 합니다:

``` json
my_func({"id":123});
```

쿼리 매개변수 이름을 전달하지 않으면 기본적으로 `jsonp`가 사용됩니다.

## <a name="configuration"></a> 구성

구성 값을 `set` 메서드를 통해 설정하여 Flight의 특정 동작을 사용자 정의할 수 있습니다.

``` php
Flight::set('flight.log_errors', true);
```

다음은 사용 가능한 모든 구성 설정 목록입니다:

``` html 
flight.base_url - 요청의 기본 URL을 재정의합니다. (기본값: null)
flight.case_sensitive - URL에 대한 대소문자 구분 일치를 설정합니다. (기본값: false)
flight.handle_errors - Flight가 모든 오류를 내부적으로 처리하도록 허용합니다. (기본값: true)
flight.log_errors - 오류를 웹 서버의 오류 로그 파일에 기록합니다. (기본값: false)
flight.views.path - 뷰 템플릿 파일이 포함된 디렉토리입니다. (기본값: ./views)
flight.views.extension - 뷰 템플릿 파일 확장자입니다. (기본값: .php)
```

## <a name="frameworkmethods"></a> 프레임워크 메서드

Flight는 사용하기 쉽고 이해하기 쉽도록 설계되었습니다. 다음은 프레임워크의 전체 메서드 세트입니다. 이는 정적 메서드인 핵심 메서드와 필터링하거나 재정의할 수 있는 매핑된 메서드인 확장 가능한 메서드로 구성됩니다.

### 핵심 메서드

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // 사용자 정의 프레임워크 메서드를 생성합니다.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // 프레임워크 메서드에 클래스를 등록합니다.
Flight::before(string $name, callable $callback) // 프레임워크 메서드 전에 필터를 추가합니다.
Flight::after(string $name, callable $callback) // 프레임워크 메서드 후에 필터를 추가합니다.
Flight::path(string $path) // 클래스를 자동 로드하기 위한 경로를 추가합니다.
Flight::get(string $key) // 변수를 가져옵니다.
Flight::set(string $key, mixed $value) // 변수를 설정합니다.
Flight::has(string $key) // 변수가 설정되어 있는지 확인합니다.
Flight::clear(array|string $key = []) // 변수를 지웁니다.
Flight::init() // 프레임워크를 기본 설정으로 초기화합니다.
Flight::app() // 애플리케이션 객체 인스턴스를 가져옵니다
```

### 확장 가능한 메서드

```php
Flight::start() // 프레임워크를 시작합니다.
Flight::stop() // 프레임워크를 중지하고 응답을 보냅니다.
Flight::halt(int $code = 200, string $message = '') // 선택적 상태 코드와 메시지로 프레임워크를 중지합니다.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // URL 패턴을 콜백에 매핑합니다.
Flight::group(string $pattern, callable $callback) // URL을 그룹화합니다. 패턴은 문자열이어야 합니다.
Flight::redirect(string $url, int $code) // 다른 URL로 리디렉션합니다.
Flight::render(string $file, array $data, ?string $key = null) // 템플릿 파일을 렌더링합니다.
Flight::error(Throwable $error) // HTTP 500 응답을 보냅니다.
Flight::notFound() // HTTP 404 응답을 보냅니다.
Flight::etag(string $id, string $type = 'string') // ETag HTTP 캐싱을 수행합니다.
Flight::lastModified(int $time) // 마지막 수정 HTTP 캐싱을 수행합니다.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSON 응답을 보냅니다.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONP 응답을 보냅니다.
```

`map` 및 `register`를 사용하여 추가된 모든 사용자 정의 메서드는 필터링할 수도 있습니다.

## <a name="frameworkinstance"></a> 프레임워크 인스턴스

Flight를 전역 정적 클래스로 실행하는 대신 객체 인스턴스로 실행할 수 있습니다.

``` php
require 'flight/autoload.php';

use flight\Engine;

$app = new Engine();

$app->route('/', function(){
    echo '안녕하세요, 세계!';
});

$app->start();
```

따라서 정적 메서드를 호출하는 대신 Engine 객체에서 동일한 이름의 인스턴스 메서드를 호출합니다.
