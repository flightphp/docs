# Construyendo un Blog Simple con Flight PHP

Esta guía te guía a través de la creación de un blog básico utilizando el framework Flight PHP. Configurarás un proyecto, definirás rutas, administrarás publicaciones con JSON y las renderizarás con el motor de plantillas Latte, todo mostrando la simplicidad y flexibilidad de Flight. Al final, tendrás un blog funcional con una página de inicio, páginas de publicaciones individuales y un formulario de creación.

## Requisitos Previos
- **PHP 7.4+**: Instalado en tu sistema.
- **Composer**: Para la gestión de dependencias.
- **Editor de Texto**: Cualquier editor como VS Code o PHPStorm.
- Conocimientos básicos de PHP y desarrollo web.

## Paso 1: Configura Tu Proyecto

Comienza creando un nuevo directorio de proyecto e instalando Flight a través de Composer.

1. **Crear un Directorio**:
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Instalar Flight**:
   ```bash
   composer require flightphp/core
   ```

3. **Crear un Directorio Público**:
   Flight utiliza un único punto de entrada (`index.php`). Crea una carpeta `public/` para ello:
   ```bash
   mkdir public
   ```

4. **Básico `index.php`**:
   Crea `public/index.php` con una ruta simple de “hola mundo”:
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo '¡Hola, Flight!';
   });

   Flight::start();
   ```

5. **Ejecutar el Servidor Integrado**:
   Prueba tu configuración con el servidor de desarrollo de PHP:
   ```bash
   php -S localhost:8000 -t public/
   ```
   Visita `http://localhost:8000` para ver “¡Hola, Flight!”.

## Paso 2: Organizar la Estructura de Tu Proyecto

Para una configuración limpia, estructura tu proyecto así:

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

- `app/config/`: Archivos de configuración (por ejemplo, eventos, rutas).
- `app/views/`: Plantillas para renderizar páginas.
- `data/`: Archivo JSON para almacenar publicaciones de blog.
- `public/`: Raíz web con `index.php`.

## Paso 3: Instalar y Configurar Latte

Latte es un motor de plantillas ligero que se integra bien con Flight.

1. **Instalar Latte**:
   ```bash
   composer require latte/latte
   ```

2. **Configurar Latte en Flight**:
   Actualiza `public/index.php` para registrar Latte como el motor de vista:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Mi Blog']);
   });

   Flight::start();
   ```

3. **Crear una Plantilla de Diseño: 
En `app/views/layout.latte`**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>Mi Blog</h1>
        <nav>
            <a href="/">Inicio</a> | 
            <a href="/create">Crear una Publicación</a>
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

4. **Crear una Plantilla de Inicio**:
   En `app/views/home.latte`:
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
   Reinicia el servidor si te has salido de él y visita `http://localhost:8000` para ver la página renderizada.

5. **Crear un Archivo de Datos**:

   Utiliza un archivo JSON para simular una base de datos por simplicidad.

   En `data/posts.json`:
   ```json
   [
       {
           "slug": "primer-post",
           "title": "Mi Primer Post",
           "content": "¡Esta es mi primera publicación de blog con Flight PHP!"
       }
   ]
   ```

## Paso 4: Definir Rutas

Separa tus rutas en un archivo de configuración para mejor organización.

1. **Crear `routes.php`**:
   En `app/config/routes.php`:
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Mi Blog']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => 'Publicación: ' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => 'Crear una Publicación']);
   });
   ```

2. **Actualizar `index.php`**:
   Incluye el archivo de rutas:
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

## Paso 5: Almacenar y Recuperar Publicaciones de Blog

Agrega los métodos para cargar y guardar publicaciones.

1. **Agregar un Método de Publicaciones**:
   En `index.php`, agrega un método para cargar publicaciones:
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **Actualizar Rutas**:
   Modifica `app/config/routes.php` para usar publicaciones:
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => 'Mi Blog',
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
       Flight::view()->render('create.latte', ['title' => 'Crear una Publicación']);
   });
   ```

## Paso 6: Crear Plantillas

Actualiza tus plantillas para mostrar publicaciones.

1. **Página de Publicación (`app/views/post.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## Paso 7: Agregar Creación de Publicación

Manejar la presentación del formulario para agregar nuevas publicaciones.

1. **Crear Formulario (`app/views/create.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">Título:</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">Contenido:</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">Guardar Publicación</button>
		</form>
	{/block}
   ```

2. **Agregar Ruta POST**:
   En `app/config/routes.php`:
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

3. **Prueba**:
   - Visita `http://localhost:8000/create`.
   - Envía una nueva publicación (por ejemplo, “Segundo Post” con algo de contenido).
   - Verifica la página de inicio para verlo listado.

## Paso 8: Mejorar con Manejo de Errores

Sobrescribe el método `notFound` para una mejor experiencia 404.

En `index.php`:
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => 'Página No Encontrada']);
});
```

Crea `app/views/404.latte`:
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>¡Lo siento, esa página no existe!</p>
{/block}
```

## Próximos Pasos
- **Agregar Estilo**: Utiliza CSS en tus plantillas para un mejor aspecto.
- **Base de Datos**: Reemplaza `posts.json` con una base de datos como SQLite utilizando `PdoWrapper`.
- **Validación**: Agrega verificaciones para slugs duplicados o entradas vacías.
- **Middleware**: Implementa autenticación para la creación de publicaciones.

## Conclusión

¡Has construido un blog simple con Flight PHP! Esta guía demuestra características básicas como enrutamiento, templating con Latte y manejo de envíos de formularios, todo mientras mantienes las cosas ligeras. ¡Explora la documentación de Flight para más características avanzadas que lleven tu blog más lejos!