# Métodos da API do Framework

Flight foi projetado para ser fácil de usar e entender. O seguinte é o conjunto completo
de métodos para o framework. Ele consiste em métodos principais, que são métodos 
estáticos regulares, e métodos extensíveis, que são métodos mapeados que podem ser 
filtrados ou substituídos.

## Métodos Principais

Esses métodos são essenciais para o framework e não podem ser substituídos.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Cria um método personalizado do framework.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra uma classe a um método do framework.
Flight::unregister(string $name) // Cancela o registro de uma classe a um método do framework.
Flight::before(string $name, callable $callback) // Adiciona um filtro antes de um método do framework.
Flight::after(string $name, callable $callback) // Adiciona um filtro após um método do framework.
Flight::path(string $path) // Adiciona um caminho para autoloading de classes.
Flight::get(string $key) // Obtém uma variável definida por Flight::set().
Flight::set(string $key, mixed $value) // Define uma variável dentro do mecanismo Flight.
Flight::has(string $key) // Verifica se uma variável está definida.
Flight::clear(array|string $key = []) // Limpa uma variável.
Flight::init() // Inicializa o framework com as configurações padrão.
Flight::app() // Obtém a instância do objeto da aplicação.
Flight::request() // Obtém a instância do objeto de requisição.
Flight::response() // Obtém a instância do objeto de resposta.
Flight::router() // Obtém a instância do objeto de roteador.
Flight::view() // Obtém a instância do objeto de visualização.
```

## Métodos Extensíveis

```php
Flight::start() // Inicia o framework.
Flight::stop() // Para o framework e envia uma resposta.
Flight::halt(int $code = 200, string $message = '') // Para o framework com um código de status e mensagem opcionais.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL a um callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de requisição POST a um callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de requisição PUT a um callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de requisição PATCH a um callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de requisição DELETE a um callback.
Flight::group(string $pattern, callable $callback) // Cria agrupamentos para URLs, o padrão deve ser uma string.
Flight::getUrl(string $name, array $params = []) // Gera uma URL com base em um alias de rota.
Flight::redirect(string $url, int $code) // Redireciona para outra URL.
Flight::download(string $filePath) // Faz o download de um arquivo.
Flight::render(string $file, array $data, ?string $key = null) // Renderiza um arquivo de template.
Flight::error(Throwable $error) // Envia uma resposta HTTP 500.
Flight::notFound() // Envia uma resposta HTTP 404.
Flight::etag(string $id, string $type = 'string') // Realiza caching HTTP de ETag.
Flight::lastModified(int $time) // Realiza caching HTTP do último modificado.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSON e para o framework.
Flight::onEvent(string $event, callable $callback) // Registra um ouvinte de evento.
Flight::triggerEvent(string $event, ...$args) // Dispara um evento.
```

Qualquer método personalizado adicionado com `map` e `register` também pode ser filtrado. Para exemplos de como mapear esses métodos, consulte o guia [Extending Flight](/learn/extending).