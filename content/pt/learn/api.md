```pt
# Métodos da API do Framework

O Flight é projetado para ser fácil de usar e entender. O seguinte é o conjunto completo
de métodos para o framework. Ele consiste em métodos principais, que são
métodos estáticos regulares, e métodos extensíveis, que são métodos mapeados que podem ser filtrados
ou substituídos.

## Métodos Principais

Esses métodos são essenciais para o framework e não podem ser substituídos.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Cria um método personalizado no framework.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra uma classe em um método do framework.
Flight::unregister(string $name) // Cancela o registro de uma classe em um método do framework.
Flight::before(string $name, callable $callback) // Adiciona um filtro antes de um método do framework.
Flight::after(string $name, callable $callback) // Adiciona um filtro depois de um método do framework.
Flight::path(string $path) // Adiciona um caminho para carregar automaticamente classes.
Flight::get(string $key) // Obtém uma variável.
Flight::set(string $key, mixed $value) // Define uma variável.
Flight::has(string $key) // Verifica se uma variável está definida.
Flight::clear(array|string $key = []) // Limpa uma variável.
Flight::init() // Inicializa o framework com suas configurações padrão.
Flight::app() // Obtém a instância do objeto de aplicação
Flight::request() // Obtém a instância do objeto de solicitação
Flight::response() // Obtém a instância do objeto de resposta
Flight::router() // Obtém a instância do objeto de roteador
Flight::view() // Obtém a instância do objeto de visualização
```

## Métodos Extensíveis

```php
Flight::start() // Inicia o framework.
Flight::stop() // Interrompe o framework e envia uma resposta.
Flight::halt(int $code = 200, string $message = '') // Interrompe o framework com um código de status e mensagem opcional.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL para uma chamada de retorno.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de solicitação POST para uma chamada de retorno.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de solicitação PUT para uma chamada de retorno.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de solicitação PATCH para uma chamada de retorno.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de solicitação DELETE para uma chamada de retorno.
Flight::group(string $pattern, callable $callback) // Cria agrupamento para URLs, o padrão deve ser uma string.
Flight::getUrl(string $name, array $params = []) // Gera uma URL com base em um alias de rota.
Flight::redirect(string $url, int $code) // Redireciona para outra URL.
Flight::render(string $file, array $data, ?string $key = null) // Renderiza um arquivo de modelo.
Flight::error(Throwable $error) // Envia uma resposta HTTP 500.
Flight::notFound() // Envia uma resposta HTTP 404.
Flight::etag(string $id, string $type = 'string') // Executa o cacheamento HTTP ETag.
Flight::lastModified(int $time) // Executa o cacheamento HTTP modificado por último.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSONP.
```

Quaisquer métodos personalizados adicionados com `map` e `register` também podem ser filtrados.
```