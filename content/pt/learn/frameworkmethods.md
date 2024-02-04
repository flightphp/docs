# Métodos do Framework

Flight foi projetado para ser fácil de usar e entender. A seguir está o conjunto completo de métodos para o framework. Consiste em métodos principais, que são métodos estáticos regulares, e métodos extensíveis, que são métodos mapeados que podem ser filtrados ou substituídos.

## Métodos Principais

```php
Voo::map(string $name, callable $callback, bool $pass_route = false) // Cria um método personalizado do framework.
Voo::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra uma classe em um método do framework.
Voo::before(string $name, callable $callback) // Adiciona um filtro antes de um método do framework.
Voo::after(string $name, callable $callback) // Adiciona um filtro após um método do framework.
Voo::path(string $path) // Adiciona um caminho para carregar classes automaticamente.
Voo::get(string $key) // Obtém uma variável.
Voo::set(string $key, mixed $value) // Define uma variável.
Voo::has(string $key) // Verifica se uma variável está definida.
Voo::clear(array|string $key = []) // Limpa uma variável.
Voo::init() // Inicializa o framework com suas configurações padrão.
Voo::app() // Obtém a instância do objeto de aplicação
```

## Métodos Extensíveis

```php
Voo::start() // Inicia o framework.
Voo::stop() // Para o framework e envia uma resposta.
Voo::halt(int $code = 200, string $message = '') // Para o framework com um código de status e mensagem opcional.
Voo::route(string $pattern, callable $callback, bool $pass_route = false) // Mapeia um padrão de URL para um retorno de chamada.
Voo::group(string $pattern, callable $callback) // Cria agrupamentos para URLs, o padrão deve ser uma string.
Voo::redirect(string $url, int $code) // Redireciona para outra URL.
Voo::render(string $file, array $data, ?string $key = null) // Renderiza um arquivo de modelo.
Voo::error(Throwable $error) // Envia uma resposta HTTP 500.
Voo::notFound() // Envia uma resposta HTTP 404.
Voo::etag(string $id, string $type = 'string') // Realiza o cacheamento HTTP ETag.
Voo::lastModified(int $time) // Realiza o cacheamento HTTP da última modificação.
Voo::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSON.
Voo::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envia uma resposta JSONP.
```

Qualquer método personalizado adicionado com `map` e `register` também pode ser filtrado.