# Erstellen eines einfachen Blogs mit Flight PHP

Diese Anleitung führt Sie durch die Erstellung eines einfachen Blogs mit dem Flight PHP-Framework. Sie richten ein Projekt ein, definieren Routen, verwalten Beiträge mit JSON und rendern sie mit der Latte-Template-Engine – alles zeigt die Einfachheit und Flexibilität von Flight. Am Ende haben Sie einen funktionalen Blog mit einer Startseite, individuellen Beitragsseiten und einem Erstellungsformular.

## Voraussetzungen
- **PHP 7.4+**: Auf Ihrem System installiert.
- **Composer**: Für das Abhängigkeitsmanagement.
- **Texteditor**: Jeder Editor wie VS Code oder PHPStorm.
- Grundkenntnisse in PHP und Webentwicklung.

## Schritt 1: Richten Sie Ihr Projekt ein

Beginnen Sie damit, ein neues Projektverzeichnis zu erstellen und Flight über Composer zu installieren.

1. **Verzeichnis erstellen**:
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Flight installieren**:
   ```bash
   composer require flightphp/core
   ```

3. **Ein öffentliches Verzeichnis erstellen**:
   Flight verwendet einen einzigen Einstiegspunkt (`index.php`). Erstellen Sie einen `public/`-Ordner dafür:
   ```bash
   mkdir public
   ```

4. **Basis `index.php`**:
   Erstellen Sie `public/index.php` mit einer einfachen „Hallo Welt“-Route:
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo 'Hallo, Flight!';
   });

   Flight::start();
   ```

5. **Den integrierten Server ausführen**:
   Testen Sie Ihre Einrichtung mit dem Entwicklungsserver von PHP:
   ```bash
   php -S localhost:8000 -t public/
   ```
   Besuchen Sie `http://localhost:8000`, um „Hallo, Flight!“ zu sehen.

## Schritt 2: Organisieren Sie Ihre Projektstruktur

Für eine saubere Einrichtung strukturieren Sie Ihr Projekt wie folgt:

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

- `app/config/`: Konfigurationsdateien (z. B. Ereignisse, Routen).
- `app/views/`: Vorlagen zum Rendern von Seiten.
- `data/`: JSON-Datei zum Speichern von Blog-Beiträgen.
- `public/`: Web-Stamm mit `index.php`.

## Schritt 3: Installieren und Konfigurieren von Latte

Latte ist eine leichtgewichtige Template-Engine, die gut mit Flight integriert.

1. **Latte installieren**:
   ```bash
   composer require latte/latte
   ```

2. **Latte in Flight konfigurieren**:
   Aktualisieren Sie `public/index.php`, um Latte als Ansicht-Engine zu registrieren:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Mein Blog']);
   });

   Flight::start();
   ```

3. **Eine Layout-Vorlage erstellen**:
In `app/views/layout.latte`:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>Mein Blog</h1>
        <nav>
            <a href="/">Startseite</a> | 
            <a href="/create">Beitrag erstellen</a>
        </nav>
    </header>
    <main>
        {block content}{/block}
    </main>
    <footer>
        <p>&copy; {date('Y')} Flight Blog</p>
    </footer>
</body>
</html>
```

4. **Eine Startvorlage erstellen**:
In `app/views/home.latte`:
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
Starten Sie den Server neu, wenn Sie ihn verlassen haben und besuchen Sie `http://localhost:8000`, um die gerenderte Seite zu sehen.

5. **Eine Datendatei erstellen**:

Verwenden Sie eine JSON-Datei, um eine Datenbank zur Vereinfachung zu simulieren.

In `data/posts.json`:
```json
[
    {
        "slug": "first-post",
        "title": "Mein erster Beitrag",
        "content": "Dies ist mein allererster Blogbeitrag mit Flight PHP!"
    }
]
```

## Schritt 4: Routen definieren

Trennen Sie Ihre Routen in eine Konfigurationsdatei für eine bessere Organisation.

1. **Erstellen Sie `routes.php`**:
In `app/config/routes.php`:
```php
<?php
Flight::route('/', function () {
    Flight::view()->render('home.latte', ['title' => 'Mein Blog']);
});

Flight::route('/post/@slug', function ($slug) {
    Flight::view()->render('post.latte', ['title' => 'Beitrag: ' . $slug, 'slug' => $slug]);
});

Flight::route('GET /create', function () {
    Flight::view()->render('create.latte', ['title' => 'Beitrag erstellen']);
});
```

2. **Aktualisieren Sie `index.php`**:
Integrieren Sie die Routen-Datei:
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

## Schritt 5: Blog-Beiträge speichern und abrufen

Fügen Sie die Methoden zum Laden und Speichern von Beiträgen hinzu.

1. **Eine Posts-Methode hinzufügen**:
In `index.php`, fügen Sie eine Methode hinzu, um Beiträge zu laden:
```php
Flight::map('posts', function () {
    $file = __DIR__ . '/../data/posts.json';
    return json_decode(file_get_contents($file), true);
});
```

2. **Routen aktualisieren**:
Ändern Sie `app/config/routes.php`, um Beiträge zu verwenden:
```php
<?php
Flight::route('/', function () {
    $posts = Flight::posts();
    Flight::view()->render('home.latte', [
        'title' => 'Mein Blog',
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
    Flight::view()->render('create.latte', ['title' => 'Beitrag erstellen']);
});
```

## Schritt 6: Vorlagen erstellen

Aktualisieren Sie Ihre Vorlagen, um Beiträge anzuzeigen.

1. **Beitragsseite (`app/views/post.latte`)**:
```html
{extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
```

## Schritt 7: Beitragserstellung hinzufügen

Behandeln Sie die Formularübermittlung zum Hinzufügen neuer Beiträge.

1. **Erstellungsformular (`app/views/create.latte`)**:
```html
{extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">Titel:</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">Inhalt:</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">Beitrag speichern</button>
		</form>
	{/block}
```

2. **POST-Route hinzufügen**:
In `app/config/routes.php`:
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

3. **Testen Sie es**:
   - Besuchen Sie `http://localhost:8000/create`.
   - Reichen Sie einen neuen Beitrag ein (z. B. „Zweiter Beitrag“ mit etwas Inhalt).
   - Überprüfen Sie die Startseite, um zu sehen, dass er aufgeführt ist.

## Schritt 8: Mit Fehlerbehandlung verbessern

Überschreiben Sie die `notFound`-Methode für ein besseres 404-Erlebnis.

In `index.php`:
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => 'Seite nicht gefunden']);
});
```

Erstellen Sie `app/views/404.latte`:
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>Entschuldigung, diese Seite existiert nicht!</p>
{/block}
```

## Nächste Schritte
- **Styling hinzufügen**: Verwenden Sie CSS in Ihren Vorlagen für ein besseres Aussehen.
- **Datenbank**: Ersetzen Sie `posts.json` durch eine Datenbank wie SQLite mit `PdoWrapper`.
- **Validierung**: Fügen Sie Prüfungen auf doppelte Slugs oder leere Eingaben hinzu.
- **Middleware**: Implementieren Sie die Authentifizierung für die Erstellung von Beiträgen.

## Fazit

Sie haben einen einfachen Blog mit Flight PHP erstellt! Diese Anleitung zeigt die grundlegenden Funktionen wie Routing, das Rendern von Vorlagen mit Latte und die Handhabung von Formularübermittlungen – und das alles, während es leichtgewichtig bleibt. Erkunden Sie die Dokumentation von Flight für weitere fortgeschrittene Funktionen, um Ihren Blog weiter auszubauen!