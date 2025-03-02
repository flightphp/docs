# Métodos da API do Framework

Flight foi projetado para ser fácil de usar e entender. A seguir está o conjunto completo de métodos para o framework. Ele consiste em métodos principais, que são métodos estáticos regulares, e métodos extensíveis, que são métodos mapeados que podem ser filtrados ou anulados.

## Métodos Principais

Estes métodos são essenciais para o framework e não podem ser anulados.

```php
Flight::map(string $nome, callable $retorno, bool $pass_route = false) // Cria um método personalizado para o framework.
Flight::register(string $nome, string $classe, array $params = [], ?callable $retorno = null) // Registra uma classe para um método do framework.
Flight::unregister(string $nome) // Anula uma classe de um método do framework.
Flight::before(string $nome, callable $retorno) // Adiciona um filtro antes de um método do framework.
Flight::after(string $nome, callable $retorno) // Adiciona um filtro após um método do framework.
Flight::path(string $caminho) // Adiciona um caminho para o carregamento automático de classes.
Flight::get(string $chave) // Obtém uma variável definida por Flight::set().
Flight::set(string $chave, mixed $valor) // Define uma variável dentro do mecanismo do Flight.
Flight::has(string $chave) // Verifica se uma variável está definida.
Flight::clear(array|string $chave = []) // Limpa uma variável.
Flight::init() // Inicializa o framework com as configurações padrão.
Flight::app() // Obtém a instância do objeto de aplicativo
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
Flight::route(string $padrão, callable $retorno, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL para um retorno.
Flight::post(string $padrão, callable $retorno, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de solicitação POST para um retorno.
Flight::put(string $padrão, callable $retorno, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de solicitação PUT para um retorno.
Flight::patch(string $padrão, callable $retorno, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de solicitação PATCH para um retorno.
Flight::delete(string $padrão, callable $retorno, bool $pass_route = false, string $alias = '') // Mapeia um padrão de URL de solicitação DELETE para um retorno.
Flight::group(string $padrão, callable $retorno) // Cria agrupamento para URLs, o padrão deve ser uma string.
Flight::getUrl(string $nome, array $params = []) // Gera uma URL com base em um alias de rota.
Flight::redirect(string $url, int $code) // Redireciona para outra URL.
Flight::download(string $caminhoArquivo) // Faz o download de um arquivo.
Flight::render(string $arquivo, array $dados, ?string $chave = null) // Renderiza um arquivo de modelo.
Flight::error(Throwable $erro) // Envia uma resposta HTTP 500.
Flight::notFound() // Envia uma resposta HTTP 404.
Flight::etag(string $id, string $tipo = 'string') // Executa o cacheamento HTTP ETag.
Flight::lastModified(int $tempo) // Executa o cacheamento HTTP da última modificação.
Flight::json(mixed $dados, int $code = 200, bool $encode = true, string $charset = 'utf8', int $opção) // Envia uma resposta JSON.
Flight::jsonp(mixed $dados, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $opção) // Envia uma resposta JSONP.
Flight::jsonHalt(mixed $dados, int $code = 200, bool $encode = true, string $charset = 'utf8', int $opção) // Envia uma resposta JSON e interrompe o framework.
```

Quaisquer métodos personalizados adicionados com `map` e `register` também podem ser filtrados. Para exemplos de como mapear esses métodos, consulte o guia [Estendendo o Flight](/learn/extending).