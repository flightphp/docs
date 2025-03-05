# Créer un blog simple avec Flight PHP

Ce guide vous accompagne pour créer un blog de base en utilisant le framework Flight PHP. Vous allez configurer un projet, définir des routes, gérer des publications avec JSON et les afficher avec le moteur de templates Latte, tout en mettant en avant la simplicité et la flexibilité de Flight. À la fin, vous aurez un blog fonctionnel avec une page d'accueil, des pages de publication individuelles et un formulaire de création.

## Prérequis
- **PHP 7.4+** : Installé sur votre système.
- **Composer** : Pour la gestion des dépendances.
- **Éditeur de texte** : Tout éditeur comme VS Code ou PHPStorm.
- Connaissances de base en PHP et développement web.

## Étape 1 : Configurer votre projet

Commencez par créer un nouveau répertoire de projet et installez Flight via Composer.

1. **Créer un répertoire** :
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Installer Flight** :
   ```bash
   composer require flightphp/core
   ```

3. **Créer un répertoire public** :
   Flight utilise un point d'entrée unique (`index.php`). Créez un dossier `public/` pour cela :
   ```bash
   mkdir public
   ```

4. **Base `index.php`** :
   Créez `public/index.php` avec une route simple « hello world » :
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo 'Bonjour, Flight !';
   });

   Flight::start();
   ```

5. **Exécuter le serveur intégré** :
   Testez votre configuration avec le serveur de développement de PHP :
   ```bash
   php -S localhost:8000 -t public/
   ```
   Visitez `http://localhost:8000` pour voir « Bonjour, Flight ! ».

## Étape 2 : Organiser la structure de votre projet

Pour une configuration propre, structurez votre projet comme ceci :

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

- `app/config/` : Fichiers de configuration (ex. : événements, routes).
- `app/views/` : Templates pour le rendu des pages.
- `data/` : Fichier JSON pour stocker les articles du blog.
- `public/` : Racine web avec `index.php`.

## Étape 3 : Installer et configurer Latte

Latte est un moteur de templates léger qui s'intègre bien avec Flight.

1. **Installer Latte** :
   ```bash
   composer require latte/latte
   ```

2. **Configurer Latte dans Flight** :
   Mettez à jour `public/index.php` pour enregistrer Latte comme moteur de vues :
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Mon Blog']);
   });

   Flight::start();
   ```

3. **Créer un template de mise en page : 
Dans `app/views/layout.latte`** :
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>Mon Blog</h1>
        <nav>
            <a href="/">Accueil</a> | 
            <a href="/create">Créer un Article</a>
        </nav>
    </header>
    <main>
        {block content}{/block}
    </main>
    <footer>
        <p>&copy; {date('Y')} Blog Flight</p>
    </footer>
</body>
</html>
```

4. **Créer un template d'accueil** :
   Dans `app/views/home.latte` :
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
   Redémarrez le serveur si vous en êtes sorti et visitez `http://localhost:8000` pour voir la page rendue.

5. **Créer un fichier de données** :

   Utilisez un fichier JSON pour simuler une base de données pour plus de simplicité.

   Dans `data/posts.json` :
   ```json
   [
       {
           "slug": "premier-article",
           "title": "Mon Premier Article",
           "content": "Ceci est mon tout premier article de blog avec Flight PHP !"
       }
   ]
   ```

## Étape 4 : Définir les routes

Séparez vos routes dans un fichier de configuration pour une meilleure organisation.

1. **Créer `routes.php`** :
   Dans `app/config/routes.php` :
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Mon Blog']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => 'Article : ' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => 'Créer un Article']);
   });
   ```

2. **Mettre à jour `index.php`** :
   Incluez le fichier des routes :
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

## Étape 5 : Stocker et récupérer des articles de blog

Ajoutez les méthodes pour charger et sauvegarder des articles.

1. **Ajouter une méthode Posts** :
   Dans `index.php`, ajoutez une méthode pour charger des articles :
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **Mettre à jour les routes** :
   Modifiez `app/config/routes.php` pour utiliser les articles :
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => 'Mon Blog',
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
       Flight::view()->render('create.latte', ['title' => 'Créer un Article']);
   });
   ```

## Étape 6 : Créer des templates

Mettez à jour vos templates pour afficher des articles.

1. **Page de l'article (`app/views/post.latte`)** :
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## Étape 7 : Ajouter la création d'articles

Gérez la soumission du formulaire pour ajouter de nouveaux articles.

1. **Formulaire de création (`app/views/create.latte`)** :
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">Titre :</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">Contenu :</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">Sauvegarder l'Article</button>
		</form>
	{/block}
   ```

2. **Ajouter une route POST** :
   Dans `app/config/routes.php` :
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

3. **Testez-le** :
   - Visitez `http://localhost:8000/create`.
   - Soumettez un nouvel article (par exemple, « Deuxième Article » avec un peu de contenu).
   - Vérifiez la page d'accueil pour voir qu'il est répertorié.

## Étape 8 : Améliorer avec la gestion des erreurs

Surchargez la méthode `notFound` pour une meilleure expérience 404.

Dans `index.php` :
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => 'Page Non Trouvée']);
});
```

Créez `app/views/404.latte` :
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>Désolé, cette page n'existe pas !</p>
{/block}
```

## Prochaines étapes
- **Ajouter du style** : Utilisez le CSS dans vos templates pour un meilleur rendu.
- **Base de données** : Remplacez `posts.json` par une base de données comme SQLite en utilisant `PdoWrapper`.
- **Validation** : Ajoutez des vérifications pour les slugs en double ou les entrées vides.
- **Middleware** : Implémentez l'authentification pour la création d'articles.

## Conclusion

Vous avez construit un blog simple avec Flight PHP ! Ce guide démontre des fonctionnalités clés comme le routage, le rendu de templates avec Latte et la gestion des soumissions de formulaires, le tout en gardant les choses légères. Explorez la documentation de Flight pour des fonctionnalités plus avancées afin de faire progresser votre blog !