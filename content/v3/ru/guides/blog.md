# Создание простого блога с Flight PHP

Этот гид проведет вас через создание базового блога с использованием фреймворка Flight PHP. Вы настроите проект, определите маршруты, управляйте постами с помощью JSON и отображайте их с помощью шаблонизатора Latte — все это демонстрирует простоту и гибкость Flight. В конце у вас будет функциональный блог с домашней страницей, страницами отдельных постов и формой для создания.

## Предварительные требования
- **PHP 7.4+**: Установлен на вашей системе.
- **Composer**: Для управления зависимостями.
- **Текстовый редактор**: Любой редактор, например, VS Code или PHPStorm.
- Базовые знания PHP и веб-разработки.

## Шаг 1: Настройте свой проект

Начните с создания новой директории проекта и установки Flight через Composer.

1. **Создайте директорию**:
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Установите Flight**:
   ```bash
   composer require flightphp/core
   ```

3. **Создайте публичную директорию**:
   Flight использует одну точку входа (`index.php`). Создайте папку `public/` для этого:
   ```bash
   mkdir public
   ```

4. **Базовый `index.php`**:
   Создайте `public/index.php` с простым маршрутом "hello world":
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo 'Привет, Flight!';
   });

   Flight::start();
   ```

5. **Запустите встроенный сервер**:
   Проверьте вашу настройку с помощью веб-сервера разработки PHP:
   ```bash
   php -S localhost:8000 -t public/
   ```
   Посетите `http://localhost:8000`, чтобы увидеть "Привет, Flight!".

## Шаг 2: Организуйте структуру вашего проекта

Для чистой настройки структурируйте ваш проект следующим образом:

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

- `app/config/`: Файлы конфигурации (например, события, маршруты).
- `app/views/`: Шаблоны для отображения страниц.
- `data/`: JSON-файл для хранения постов блога.
- `public/`: Веб-корень с `index.php`.

## Шаг 3: Установите и настройте Latte

Latte — это легкий шаблонизатор, который хорошо интегрируется с Flight.

1. **Установите Latte**:
   ```bash
   composer require latte/latte
   ```

2. **Настройте Latte в Flight**:
   Обновите `public/index.php`, чтобы зарегистрировать Latte как движок представлений:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Мой блог']);
   });

   Flight::start();
   ```

3. **Создайте шаблон разметки: 
В `app/views/layout.latte`**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>Мой блог</h1>
        <nav>
            <a href="/">Главная</a> | 
            <a href="/create">Создать пост</a>
        </nav>
    </header>
    <main>
        {block content}{/block}
    </main>
    <footer>
        <p>&copy; {date('Y')} Блог Flight</p>
    </footer>
</body>
</html>
```

4. **Создайте шаблон для главной страницы**:
   В `app/views/home.latte`:
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
   Перезапустите сервер, если вы вышли из него, и посетите `http://localhost:8000`, чтобы увидеть отрендеренную страницу.

5. **Создайте файл данных**:

   Используйте JSON-файл, чтобы смоделировать базу данных для простоты.

   В `data/posts.json`:
   ```json
   [
       {
           "slug": "first-post",
           "title": "Мой первый пост",
           "content": "Это мой самый первый пост в блоге с Flight PHP!"
       }
   ]
   ```

## Шаг 4: Определите маршруты

Отделите ваши маршруты в файл конфигурации для лучшей организации.

1. **Создание `routes.php`**:
   В `app/config/routes.php`:
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Мой блог']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => 'Пост: ' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => 'Создать пост']);
   });
   ```

2. **Обновите `index.php`**:
   Включите файл маршрутов:
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

## Шаг 5: Хранение и получение постов блога

Добавьте методы для загрузки и сохранения постов.

1. **Добавьте метод для постов**:
   В `index.php` добавьте метод для загрузки постов:
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **Обновите маршруты**:
   Измените `app/config/routes.php`, чтобы использовать посты:
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => 'Мой блог',
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
       Flight::view()->render('create.latte', ['title' => 'Создать пост']);
   });
   ```

## Шаг 6: Создание шаблонов

Обновите ваши шаблоны для отображения постов.

1. **Страница поста (`app/views/post.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## Шаг 7: Добавление создания постов

Обработайте отправку формы для добавления новых постов.

1. **Создайте форму (`app/views/create.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">Заголовок:</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">Содержимое:</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">Сохранить пост</button>
		</form>
	{/block}
   ```

2. **Добавьте маршрут POST**:
   В `app/config/routes.php`:
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

3. **Проверьте это**:
   - Посетите `http://localhost:8000/create`.
   - Отправьте новый пост (например, "Второй пост" с некоторым содержимым).
   - Проверьте главную страницу, чтобы увидеть его в списке.

## Шаг 8: Улучшите обработку ошибок

Переопределите метод `notFound` для лучшего опыта 404.

В `index.php`:
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => 'Страница не найдена']);
});
```

Создайте `app/views/404.latte`:
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>Извините, этой страницы не существует!</p>
{/block}
```

## Следующие шаги
- **Добавить стили**: Используйте CSS в ваших шаблонах для лучшего внешнего вида.
- **База данных**: Замените `posts.json` на базу данных, такую как SQLite, используя `PdoWrapper`.
- **Валидация**: Добавьте проверки на дублирующиеся слаги или пустые вводы.
- **Промежуточное ПО**: Реализуйте аутентификацию для создания постов.

## Заключение

Вы создали простой блог с Flight PHP! Этот гид демонстрирует основные функции, такие как маршрутизация, шаблонизация с помощью Latte и обработка отправок форм — при этом все оставаясь легковесным. Изучите документацию Flight для более сложных функций, чтобы развить ваш блог дальше!