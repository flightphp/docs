# Flight PHP로 간단한 블로그 만들기

이 가이드는 Flight PHP 프레임워크를 사용하여 기본 블로그를 만드는 과정을 안내합니다. 프로젝트를 설정하고, 경로를 정의하고, JSON으로 게시물을 관리하고, Latte 템플릿 엔진으로 렌더링합니다. 모든 과정에서 Flight의 간단함과 유연성을 보여줍니다. 마지막에는 홈페이지와 개별 게시물 페이지, 생성 양식이 포함된 기능적인 블로그를 만들게 됩니다.

## 필수 조건
- **PHP 7.4+**: 시스템에 설치되어 있어야 합니다.
- **Composer**: 의존성 관리를 위해 필요합니다.
- **텍스트 편집기**: VS Code나 PHPStorm과 같은 편집기.
- PHP 및 웹 개발에 대한 기본 지식.

## 1단계: 프로젝트 설정

새 프로젝트 디렉토리를 생성하고 Composer를 통해 Flight를 설치합니다.

1. **디렉토리 생성**:
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Flight 설치**:
   ```bash
   composer require flightphp/core
   ```

3. **공용 디렉토리 생성**:
   Flight는 단일 진입점(`index.php`)을 사용합니다. 이를 위해 `public/` 폴더를 생성합니다:
   ```bash
   mkdir public
   ```

4. **기본 `index.php`**:
   간단한 "hello world" 경로로 `public/index.php`를 생성합니다:
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo '안녕하세요, Flight!';
   });

   Flight::start();
   ```

5. **내장 서버 실행**:
   PHP의 개발 서버로 설정을 테스트합니다:
   ```bash
   php -S localhost:8000 -t public/
   ```
   `http://localhost:8000`을 방문하여 "안녕하세요, Flight!"를 확인합니다.

## 2단계: 프로젝트 구조 정리

정리된 설정을 위해 프로젝트를 다음과 같이 구조화합니다:

```text
flight-blog/
├── app/
│   ├── config/
│   └── views/
├── data/
├── public/
│   └── index.php
├── vendor/
└── composer.json
```

- `app/config/`: 구성 파일 (예: 이벤트, 경로).
- `app/views/`: 페이지 렌더링을 위한 템플릿.
- `data/`: 블로그 게시물을 저장할 JSON 파일.
- `public/`: `index.php`가 있는 웹 루트.

## 3단계: Latte 설치 및 구성

Latte는 Flight와 잘 통합되는 경량 템플릿 엔진입니다.

1. **Latte 설치**:
   ```bash
   composer require latte/latte
   ```

2. **Flight에서 Latte 구성**:
   `public/index.php`를 업데이트하여 Latte를 뷰 엔진으로 등록합니다:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => '내 블로그']);
   });

   Flight::start();
   ```

3. **레이아웃 템플릿 생성**:
   `app/views/layout.latte`에 다음을 추가합니다:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>내 블로그</h1>
        <nav>
            <a href="/">홈</a> | 
            <a href="/create">게시물 작성</a>
        </nav>
    </header>
    <main>
        {block content}{/block}
    </main>
    <footer>
        <p>&copy; {date('Y')} Flight 블로그</p>
    </footer>
</body>
</html>
```

4. **홈 템플릿 만들기**:
   `app/views/home.latte`에:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<ul>
		{foreach $posts as $post}
			<li><a href="/post/{$post['slug']}">{$post['title']}</a></li>
		{/foreach}
		</ul>
	{/block}
   ```
   서버를 재시작하고 `http://localhost:8000`을 방문하여 렌더링된 페이지를 확인하세요.

5. **데이터 파일 생성**:

   간단하게 데이터베이스를 시뮬레이션하기 위해 JSON 파일을 사용합니다.

   `data/posts.json`에:
   ```json
   [
       {
           "slug": "first-post",
           "title": "내 첫 번째 게시물",
           "content": "이것은 Flight PHP로 작성한 첫 번째 블로그 게시물입니다!"
       }
   ]
   ```

## 4단계: 경로 정의

보다 나은 구성을 위해 경로를 구성 파일에 분리합니다.

1. **`routes.php` 생성**:
   `app/config/routes.php`에:
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => '내 블로그']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => '게시물: ' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => '게시물 작성']);
   });
   ```

2. **`index.php` 업데이트**:
   경로 파일을 포함합니다:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   require '../app/config/routes.php';

   Flight::start();
   ```

## 5단계: 블로그 게시물 저장 및 검색

게시물을 불러오고 저장하는 메서드를 추가합니다.

1. **게시물 메서드 추가**:
   `index.php`에 게시물을 불러오는 메서드를 추가합니다:
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **경로 업데이트**:
   `app/config/routes.php`를 수정하여 게시물을 사용합니다:
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => '내 블로그',
           'posts' => $posts
       ]);
   });

   Flight::route('/post/@slug', function ($slug) {
       $posts = Flight::posts();
       $post = array_filter($posts, fn($p) => $p['slug'] === $slug);
       $post = reset($post) ?: null;
       if (!$post) {
           Flight::notFound();
           return;
       }
       Flight::view()->render('post.latte', [
           'title' => $post['title'],
           'post' => $post
       ]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => '게시물 작성']);
   });
   ```

## 6단계: 템플릿 생성

게시물을 표시하도록 템플릿을 업데이트합니다.

1. **게시물 페이지 (`app/views/post.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## 7단계: 게시물 작성 추가

새 게시물을 추가하기 위해 양식 제출을 처리합니다.

1. **양식 생성 (`app/views/create.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">제목:</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">내용:</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">게시물 저장</button>
		</form>
	{/block}
   ```

2. **POST 경로 추가**:
   `app/config/routes.php`에:
   ```php
   Flight::route('POST /create', function () {
       $request = Flight::request();
       $title = $request->data['title'];
       $content = $request->data['content'];
       $slug = strtolower(str_replace(' ', '-', $title));

       $posts = Flight::posts();
       $posts[] = ['slug' => $slug, 'title' => $title, 'content' => $content];
       file_put_contents(__DIR__ . '/../../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));

       Flight::redirect('/');
   });
   ```

3. **테스트**:
   - `http://localhost:8000/create`를 방문합니다.
   - 새 게시물(예: "두 번째 게시물" 및 내용)을 제출합니다.
   - 홈페이지를 확인하여 목록에 표시되는지 확인합니다.

## 8단계: 오류 처리 향상

더 나은 404 경험을 위해 `notFound` 메서드를 오버라이드합니다.

`index.php`에:
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => '페이지를 찾을 수 없습니다']);
});
```

`app/views/404.latte`를 생성합니다:
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>죄송합니다, 그 페이지는 존재하지 않습니다!</p>
{/block}
```

## 다음 단계
- **스타일 추가**: 템플릿에 CSS를 사용하여 더 나은 모양을 만듭니다.
- **데이터베이스**: `posts.json`을 SQLite와 같은 데이터베이스로 교체합니다.
- **유효성 검사**: 중복 슬러그 또는 빈 입력에 대한 검사를 추가합니다.
- **미들웨어**: 게시물 작성을 위해 인증을 구현합니다.

## 결론

Flight PHP로 간단한 블로그를 만들었습니다! 이 가이드는 경로 설정, Latte로 템플릿화, 양식 제출 처리 등 핵심 기능을 보여줍니다. Flight의 문서를 살펴보고 블로그를 더 발전시켜 보세요!