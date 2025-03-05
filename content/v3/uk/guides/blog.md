# Будівництво простого блогу з Flight PHP

Цей посібник проведе вас через створення базового блогу з використанням фреймворку Flight PHP. Ви налаштуєте проект, визначите маршрути, керуватимете дописами з JSON та відображатимете їх за допомогою движка шаблонів Latte, демонструючи простоту та гнучкість Flight. Наприкінці у вас буде функціональний блог з головною сторінкою, індивідуальними сторінками дописів та формою створення.

## Потреби
- **PHP 7.4+**: Встановлений на вашій системі.
- **Composer**: Для управління залежностями.
- **Текстовий редактор**: Будь-який редактор, як-от VS Code або PHPStorm.
- Базові знання PHP та веб-розробки.

## Крок 1: Налаштування вашого проекту

Почніть зі створення нового каталогу проекту та встановлення Flight через Composer.

1. **Створіть каталог**:
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Встановіть Flight**:
   ```bash
   composer require flightphp/core
   ```

3. **Створіть публічний каталог**:
   Flight використовує єдину точку входу (`index.php`). Створіть папку `public/` для цього:
   ```bash
   mkdir public
   ```

4. **Основний `index.php`**:
   Створіть `public/index.php` з простою маршрутом "hello world":
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo 'Привіт, Flight!';
   });

   Flight::start();
   ```

5. **Запустіть вбудований сервер**:
   Перевірте вашу установку за допомогою розробницького сервера PHP:
   ```bash
   php -S localhost:8000 -t public/
   ```
   Відвідайте `http://localhost:8000`, щоб побачити "Привіт, Flight!".

## Крок 2: Організуйте структуру вашого проекту

Для чистого налаштування структуруйте ваш проект ось так:

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

- `app/config/`: Файли конфігурації (наприклад, події, маршрути).
- `app/views/`: Шаблони для відображення сторінок.
- `data/`: JSON-файл для зберігання дописів блогу.
- `public/`: Веб-корінь з `index.php`.

## Крок 3: Встановіть та налаштуйте Latte

Latte - це легкий движок шаблонів, який добре інтегрується з Flight.

1. **Встановіть Latte**:
   ```bash
   composer require latte/latte
   ```

2. **Налаштуйте Latte в Flight**:
   Оновіть `public/index.php`, щоб зареєструвати Latte як двигун відображення:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Мій блог']);
   });

   Flight::start();
   ```

3. **Створіть шаблон макету:
У `app/views/layout.latte`**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>Мій блог</h1>
        <nav>
            <a href="/">Головна</a> | 
            <a href="/create">Створити допис</a>
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

4. **Створіть домашній шаблон**:
   У `app/views/home.latte`:
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
   Перезапустіть сервер, якщо ви вийшли з нього, та відвідайте `http://localhost:8000`, щоб побачити відображену сторінку.

5. **Створіть файл даних**:

   Використовуйте JSON-файл, щоб імітувати базу даних для спрощення.

   У `data/posts.json`:
   ```json
   [
       {
           "slug": "first-post",
           "title": "Мій перший допис",
           "content": "Це мій перший допис у блозі з Flight PHP!"
       }
   ]
   ```

## Крок 4: Визначте маршрути

Розділіть ваші маршрути на файл конфігурації для кращої організації.

1. **Створіть `routes.php`**:
   У `app/config/routes.php`:
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Мій блог']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => 'Допис: ' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => 'Створити допис']);
   });
   ```

2. **Оновіть `index.php`**:
   Додайте файл маршрутів:
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

## Крок 5: Зберігайте та отримуйте дописи блогу

Додайте методи для завантаження і збереження дописів.

1. **Додайте методи дописів**:
   У `index.php` додайте метод для завантаження дописів:
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **Оновіть маршрути**:
   Змініть `app/config/routes.php`, щоб використовувати дописи:
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => 'Мій блог',
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
       Flight::view()->render('create.latte', ['title' => 'Створити допис']);
   });
   ```

## Крок 6: Створіть шаблони

Оновіть ваші шаблони, щоб відображати дописи.

1. **Сторінка допису (`app/views/post.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## Крок 7: Додайте створення дописів

Обробіть подання форми для додавання нових дописів.

1. **Створіть форму (`app/views/create.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">Назва:</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">Зміст:</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">Зберегти допис</button>
		</form>
	{/block}
   ```

2. **Додайте POST-маршрут**:
   У `app/config/routes.php`:
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

3. **Перевірте це**:
   - Відвідайте `http://localhost:8000/create`.
   - Надішліть новий допис (наприклад, "Другий допис" з деяким змістом).
   - Перевірте головну сторінку, щоб побачити його в списку.

## Крок 8: Поліпшіть обробку помилок

Перевизначте метод `notFound` для покращеного досвіду 404.

У `index.php`:
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => 'Сторінка не знайдена']);
});
```

Створіть `app/views/404.latte`:
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>Вибачте, така сторінка не існує!</p>
{/block}
```

## Наступні кроки
- **Додайте стилі**: Використовуйте CSS у ваших шаблонах для кращого вигляду.
- **База даних**: Замість `posts.json` використовуйте базу даних, наприклад, SQLite за допомогою `PdoWrapper`.
- **Валідність**: Додайте перевірки на наявність дублікатів слугів або порожніх полів.
- **Проміжне програмне забезпечення**: Реалізуйте автентифікацію для створення дописів.

## Висновок

Ви побудували простий блог з Flight PHP! Цей посібник демонструє основні функції, такі як маршрутизація, шаблонізація з Latte та обробка подань форм, зберігаючи все легким. Досліджуйте документацію Flight для вивчення більш розвинених функцій, щоб підвищити ваш блог!