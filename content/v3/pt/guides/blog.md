# Construindo um Blog Simples com Flight PHP

Este guia o orienta na criação de um blog básico usando o framework Flight PHP. Você configurará um projeto, definirá rotas, gerenciará postagens com JSON e as renderizará com o motor de templates Latte—todos mostrando a simplicidade e flexibilidade do Flight. Ao final, você terá um blog funcional com uma página inicial, páginas de postagens individuais e um formulário de criação.

## Pré-requisitos
- **PHP 7.4+**: Instalado em seu sistema.
- **Composer**: Para gerenciamento de dependências.
- **Editor de Texto**: Qualquer editor como VS Code ou PHPStorm.
- Conhecimento básico de PHP e desenvolvimento web.

## Passo 1: Configure Seu Projeto

Comece criando um novo diretório de projeto e instalando o Flight via Composer.

1. **Criar um Diretório**:
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Instalar o Flight**:
   ```bash
   composer require flightphp/core
   ```

3. **Criar um Diretório Público**:
   O Flight utiliza um único ponto de entrada (`index.php`). Crie uma pasta `public/` para ele:
   ```bash
   mkdir public
   ```

4. **`index.php` Básico**:
   Crie `public/index.php` com uma rota simples de “hello world”:
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo 'Olá, Flight!';
   });

   Flight::start();
   ```

5. **Executar o Servidor Interno**:
   Teste sua configuração com o servidor de desenvolvimento do PHP:
   ```bash
   php -S localhost:8000 -t public/
   ```
   Acesse `http://localhost:8000` para ver “Olá, Flight!”.

## Passo 2: Organize a Estrutura do Seu Projeto

Para uma configuração limpa, estruture seu projeto da seguinte forma:

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

- `app/config/`: Arquivos de configuração (por exemplo, eventos, rotas).
- `app/views/`: Templates para renderização de páginas.
- `data/`: Arquivo JSON para armazenar postagens do blog.
- `public/`: Raiz da web com `index.php`.

## Passo 3: Instalar e Configurar o Latte

O Latte é um motor de templates leve que se integra bem com o Flight.

1. **Instalar o Latte**:
   ```bash
   composer require latte/latte
   ```

2. **Configurar o Latte no Flight**:
   Atualize `public/index.php` para registrar o Latte como o motor de visualização:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Meu Blog']);
   });

   Flight::start();
   ```

3. **Criar um Template de Layout: 
Em `app/views/layout.latte`**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>Meu Blog</h1>
        <nav>
            <a href="/">Home</a> | 
            <a href="/create">Criar uma Postagem</a>
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

4. **Criar um Template de Página Inicial**:
   Em `app/views/home.latte`:
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
   Reinicie o servidor se você saiu dele e visite `http://localhost:8000` para ver a página renderizada.

5. **Criar um Arquivo de Dados**:

   Use um arquivo JSON para simular um banco de dados para simplicidade.

   Em `data/posts.json`:
   ```json
   [
       {
           "slug": "primeira-postagem",
           "title": "Minha Primeira Postagem",
           "content": "Esta é minha primeira postagem no blog com Flight PHP!"
       }
   ]
   ```

## Passo 4: Definir Rotas

Separe suas rotas em um arquivo de configuração para melhor organização.

1. **Criar `routes.php`**:
   Em `app/config/routes.php`:
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Meu Blog']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => 'Post: ' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => 'Criar uma Postagem']);
   });
   ```

2. **Atualizar `index.php`**:
   Inclua o arquivo de rotas:
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

## Passo 5: Armazenar e Recuperar Postagens do Blog

Adicione os métodos para carregar e salvar postagens.

1. **Adicionar um Método de Postagens**:
   Em `index.php`, adicione um método para carregar postagens:
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **Atualizar Rotas**:
   Modifique `app/config/routes.php` para usar postagens:
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => 'Meu Blog',
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
       Flight::view()->render('create.latte', ['title' => 'Criar uma Postagem']);
   });
   ```

## Passo 6: Criar Templates

Atualize seus templates para exibir postagens.

1. **Página de Postagem (`app/views/post.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## Passo 7: Adicionar Criação de Postagens

Manipule a submissão de formulário para adicionar novas postagens.

1. **Criar Formulário (`app/views/create.latte`)**:
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
				<label for="content">Conteúdo:</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">Salvar Postagem</button>
		</form>
	{/block}
   ```

2. **Adicionar Rota POST**:
   Em `app/config/routes.php`:
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

3. **Teste**:
   - Acesse `http://localhost:8000/create`.
   - Envie uma nova postagem (por exemplo, “Segunda Postagem” com algum conteúdo).
   - Verifique a página inicial para vê-la listada.

## Passo 8: Melhorar com Tratamento de Erros

Sobreponha o método `notFound` para uma melhor experiência de 404.

Em `index.php`:
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => 'Página Não Encontrada']);
});
```

Crie `app/views/404.latte`:
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>Desculpe, essa página não existe!</p>
{/block}
```

## Próximos Passos
- **Adicionar Estilo**: Use CSS em seus templates para uma aparência melhor.
- **Banco de Dados**: Substitua `posts.json` por um banco de dados como SQLite usando o `PdoWrapper`.
- **Validação**: Adicione verificações para slugs duplicados ou entradas vazias.
- **Middleware**: Implemente autenticação para criação de postagens.

## Conclusão

Você construiu um blog simples com o Flight PHP! Este guia demonstra recursos principais como roteamento, templating com Latte e manipulação de envios de formulários—tudo isso mantendo as coisas leves. Explore a documentação do Flight para recursos mais avançados para levar seu blog adiante!