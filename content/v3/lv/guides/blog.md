# Vienkārša emuāra izveide ar Flight PHP

Šis ceļvedis nodrošina soļus, kā izveidot pamata emuāru, izmantojot Flight PHP ietvaru. Jūs izveidosiet projektu, definēsiet maršrutus, pārvaldīsiet ierakstus, izmantojot JSON, un attēlosiet tos ar Latte veidņu dzinēju, tādējādi demonstrējot Flight vienkāršību un elastību. Ceļojuma beigās jums būs funkcionāls emuārs ar sākumlapu, individuālām ierakstu lapām un izveides formu.

## Prasības
- **PHP 7.4+**: Instalēts jūsu sistēmā.
- **Composer**: Atkarību pārvaldībai.
- **Teksta redaktors**: Jebkurš redaktors, piemēram, VS Code vai PHPStorm.
- Pamata zināšanas par PHP un tīmekļa izstrādi.

## 1. solis: Iestatiet savu projektu

Sāciet, izveidojot jaunu projekta direktoriju un instalējot Flight, izmantojot Composer.

1. **Izveidojiet direktoriju**:
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Instalējiet Flight**:
   ```bash
   composer require flightphp/core
   ```

3. **Izveidojiet publisko direktoriju**:
   Flight izmanto vienu ieejas punktu (`index.php`). Izveidojiet `public/` mapi tam:
   ```bash
   mkdir public
   ```

4. **Pamata `index.php`**:
   Izveidojiet `public/index.php` ar vienkāršu “hello world” maršrutu:
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo 'Sveiki, Flight!';
   });

   Flight::start();
   ```

5. **Palaidiet iebūvēto serveri**:
   Pārbaudiet savu iestatījumu, izmantojot PHP izstrādes serveri:
   ```bash
   php -S localhost:8000 -t public/
   ```
   Apmeklējiet `http://localhost:8000`, lai redzētu “Sveiki, Flight!”.

## 2. solis: Organizējiet sava projekta struktūru

Lai nodrošinātu tīru iestatījumu, strukturējiet savu projektu šādi:

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

- `app/config/`: Konfigurācijas faili (piemēram, notikumi, maršruti).
- `app/views/`: Veidnes lapu attēlošanai.
- `data/`: JSON fails emuāru ierakstu glabāšanai.
- `public/`: Tīmekļa sakne ar `index.php`.

## 3. solis: Instalējiet un konfigurējiet Latte

Latte ir viegla veidņu dzinēja, kas labi integrējas ar Flight.

1. **Instalējiet Latte**:
   ```bash
   composer require latte/latte
   ```

2. **Konfigurējiet Latte Flight**:
   Atjauniniet `public/index.php`, lai reģistrētu Latte kā skata dzinēju:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Mans emuārs']);
   });

   Flight::start();
   ```

3. **Izveidojiet izkārtojuma veidni: 
   `app/views/layout.latte`**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>Mans emuārs</h1>
        <nav>
            <a href="/">Sākums</a> | 
            <a href="/create">Izveidot ierakstu</a>
        </nav>
    </header>
    <main>
        {block content}{/block}
    </main>
    <footer>
        <p>&copy; {date('Y')} Flight emuārs</p>
    </footer>
</body>
</html>
```

4. **Izveidojiet sākumlapa veidni**:
   `app/views/home.latte`:
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
   Restartējiet serveri, ja esat to izslēguši, un apmeklējiet `http://localhost:8000`, lai redzētu attēloto lapu.

5. **Izveidojiet datu failu**:

   Izmantojiet JSON failu, lai simulētu datu bāzi vienkāršības labad.

   `data/posts.json`:
   ```json
   [
       {
           "slug": "pirmais-ieraksts",
           "title": "Mans pirmais ieraksts",
           "content": "Šis ir mans pirmais emuāra ieraksts ar Flight PHP!"
       }
   ]
   ```

## 4. solis: Definējiet maršrutus

Atsevišķiet savus maršrutus konfigurācijas failā labākai organizācijai.

1. **Izveidojiet `routes.php`**:
   `app/config/routes.php`:
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Mans emuārs']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => 'Ieraksts: ' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => 'Izveidot ierakstu']);
   });
   ```

2. **Atjauniniet `index.php`**:
   Iekļaujiet maršrutu failu:
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

## 5. solis: Glabājiet un iegūstiet emuāru ierakstus

Pievienojiet metodes, lai ielādētu un saglabātu ierakstus.

1. **Pievienojiet ierakstu metodi**:
   `index.php` pievienojiet metodi ierakstu ielādei:
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **Atjauniniet maršrutus**:
   Izmainiet `app/config/routes.php`, lai izmantotu ierakstus:
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => 'Mans emuārs',
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
       Flight::view()->render('create.latte', ['title' => 'Izveidot ierakstu']);
   });
   ```

## 6. solis: Izveidojiet veidnes

Atjauniniet savas veidnes, lai attēlotu ierakstus.

1. **Ieraksta lapa (`app/views/post.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## 7. solis: Pievienojiet ierakstu izveidi

Rīkojieties ar formas iesniegšanu, lai pievienotu jaunus ierakstus.

1. **Izveidojiet formu (`app/views/create.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">Nosaukums:</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">Saturs:</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">Saglabāt ierakstu</button>
		</form>
	{/block}
   ```

2. **Pievienojiet POST maršrutu**:
   `app/config/routes.php`:
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

3. **Pārbaudiet to**:
   - Apmeklējiet `http://localhost:8000/create`.
   - Iesniedziet jaunu ierakstu (piemēram, “Otrais ieraksts” ar kādu saturu).
   - Pārbaudiet sākumlapu, lai redzētu to sarakstā.

## 8. solis: Uzlabojiet ar kļūdu apstrādi

Pārdefinējiet `notFound` metodi, lai uzlabotu 404 pieredzi.

`index.php`:
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => 'Lapa nav atrasta']);
});
```

Izveidojiet `app/views/404.latte`:
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>Atvainojiet, šī lapa neeksistē!</p>
{/block}
```

## Nākamie soļi
- **Pievienojiet stilu**: Izmantojiet CSS savās veidnēs labākam izskatam.
- **Datu bāze**: Aizvietojiet `posts.json` ar datu bāzi, piemēram, SQLite, izmantojot `PdoWrapper`.
- **Validācija**: Pievienojiet pārbaudes dublēto slogu vai tukšu ievadi.
- **Middleware**: Ieviesiet autentifikāciju ierakstu izveidei.

## Secinājums

Jūs esat izveidojis vienkāršu emuāru ar Flight PHP! Šis ceļvedis demonstrē pamatfunkcijas, piemēram, maršrutēšanu, veidņu veidošanu ar Latte un formu iesniegšanu, vienlaikus saglabājot lietas vieglas. Izpētiet Flight dokumentāciju, lai uzzinātu vairāk par uzlabotām funkcijām, kas palīdzēs attīstīt jūsu emuāru tālāk!