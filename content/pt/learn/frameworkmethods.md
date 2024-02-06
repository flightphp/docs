```pt
# Métodos do Framework

Flight é projetado para ser fácil de usar e entender. O seguinte é o conjunto completo
de métodos para o framework. Consiste em métodos principais, que são métodos estáticos regulares,
e métodos extensíveis, que são métodos mapeados que podem ser filtrados
ou substituídos.

## Métodos Principais

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Cria um método de framework personalizado.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra uma classe a um método de framework.
Flight::before(string $name, callable $callback) // Adiciona um filtro antes de um método de framework.
Flight::after(string $name, callable $callback) // Adiciona um filtro após um método de framework.
Flight::path(string $path) // Adiciona um caminho para carregar classes automaticamente.
Flight::get(string $key) // Obtém uma variável.
Flight::set(string $key, mixed $value) // Define uma variável.
Flight::has(string $key) // Verifica se uma variável está definida.
Flight::clear(array|string $key = []) // Limpa uma variável.
Flight::init() // Inicializa o framework com suas configurações padrão.
Flight::app() // Obtém a instância do objeto de aplicativo
```

## Métodos Extensíveis

```php
Flight::start() // Inicia o framework.
Flight::stop() // Para o framework e envia uma resposta.
Flight::halt(int $code = 200, string $message = '') // Para o framework com um código de status e mensagem opcionais.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Mapeia um padrão de URL para um retorno de chamada.
Flight::group(string $pattern, callable $callback) // Cria agrupamento para urls, o padrão deve ser uma string.
Flight::redirect(string $url, int $code) // Redireciona para outra URL.
Flight::render(string $file, array $data, ?string $key = null) // Renderiza um arquivo de modelo.
Flight::error(Throwable $error) // Envia uma resposta HTTP 500.
Flight::notFound() // Envia uma resposta HTTP 404.
Flight::etag(string $id, string $type = 'string') // Realiza o cache do HTTP ETag.
Flight::lastModified(int $time) // Realiza o cache do HTTP de última modificação.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSONP.
```

Quaisquer métodos personalizados adicionados com `map` e `register` também podem ser filtrados.
```