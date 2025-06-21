# FlightPHP Session - Manipulador de Sessão Leve Baseado em Arquivos

Isto é um plugin leve e baseado em arquivos para manipular sessões no [Flight PHP Framework](https://docs.flightphp.com/). Ele oferece uma solução simples e poderosa para gerenciar sessões, com recursos como leituras de sessão não bloqueantes, criptografia opcional, funcionalidade de auto-commit e um modo de teste para desenvolvimento. Os dados da sessão são armazenados em arquivos, tornando-o ideal para aplicações que não requerem um banco de dados.

Se você quiser usar um banco de dados, verifique o plugin [ghostff/session](/awesome-plugins/ghost-session) que possui muitos desses mesmos recursos, mas com backend de banco de dados.

Visite o [repositório no Github](https://github.com/flightphp/session) para o código-fonte completo e detalhes.

## Instalação

Instale o plugin via Composer:

```bash
composer require flightphp/session
```

## Uso Básico

Aqui está um exemplo simples de como usar o plugin `flightphp/session` na sua aplicação Flight:

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// Registra o serviço de sessão
$app->register('session', Session::class);

// Exemplo de rota com uso de sessão
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // Saída: johndoe
    echo $session->get('preferences', 'default_theme'); // Saída: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => 'Usuário está logado!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Limpa todos os dados da sessão
    Flight::json(['message' => 'Deslogado com sucesso']);
});

Flight::start();
```

### Pontos Chave
- **Não Bloqueante**: Usa `read_and_close` para iniciar a sessão por padrão, impedindo problemas de bloqueio de sessão.
- **Auto-Commit**: Ativado por padrão, então as alterações são salvas automaticamente no desligamento, a menos que desativado.
- **Armazenamento em Arquivos**: As sessões são armazenadas no diretório temporário do sistema sob `/flight_sessions` por padrão.

## Configuração

Você pode personalizar o manipulador de sessão passando um array de opções ao registrar:

```php
// Sim, é um array duplo :)
$app->register('session', Session::class, [ [
    'save_path' => '/custom/path/to/sessions',         // Diretório para os arquivos de sessão
	'prefix' => 'myapp_',                              // Prefixo para os arquivos de sessão
    'encryption_key' => 'a-secure-32-byte-key-here',   // Ativa criptografia (32 bytes recomendados para AES-256-CBC)
    'auto_commit' => false,                            // Desativa auto-commit para controle manual
    'start_session' => true,                           // Inicia a sessão automaticamente (padrão: true)
    'test_mode' => false,                              // Ativa modo de teste para desenvolvimento
    'serialization' => 'json',                         // Método de serialização: 'json' (padrão) ou 'php' (legado)
] ]);
```

### Opções de Configuração
| Option            | Description                                      | Default Value                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | Diretório onde os arquivos de sessão são armazenados | `sys_get_temp_dir() . '/flight_sessions'` |
| `prefix`          | Prefixo para o arquivo de sessão salvo           | `sess_`                           |
| `encryption_key`  | Chave para criptografia AES-256-CBC (opcional)   | `null` (sem criptografia)        |
| `auto_commit`     | Auto-salvar dados da sessão no desligamento      | `true`                            |
| `start_session`   | Iniciar a sessão automaticamente                 | `true`                            |
| `test_mode`       | Executar no modo de teste sem afetar sessões PHP | `false`                           |
| `test_session_id` | ID de sessão personalizado para modo de teste (opcional) | Gerado aleatoriamente se não definido |
| `serialization`   | Método de serialização: 'json' (padrão, seguro) ou 'php' (legado, permite objetos) | `'json'` |

## Modos de Serialização

Por padrão, esta biblioteca usa **serialização JSON** para os dados da sessão, o que é seguro e previne vulnerabilidades de injeção de objetos PHP. Se você precisar armazenar objetos PHP na sessão (não recomendado para a maioria dos apps), você pode optar pela serialização PHP legada:

- `'serialization' => 'json'` (padrão):
  - Apenas arrays e primitivos são permitidos nos dados da sessão.
  - Mais seguro: imune a injeção de objetos PHP.
  - Arquivos são prefixados com `J` (JSON simples) ou `F` (JSON criptografado).
- `'serialization' => 'php'`:
  - Permite armazenar objetos PHP (use com cautela).
  - Arquivos são prefixados com `P` (serialização PHP simples) ou `E` (serialização PHP criptografada).

**Nota:** Se você usar serialização JSON, tentar armazenar um objeto lançará uma exceção.

## Uso Avançado

### Commit Manual
Se você desativar o auto-commit, você deve commitar as alterações manualmente:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Salva explicitamente as alterações
});
```

### Segurança de Sessão com Criptografia
Ative a criptografia para dados sensíveis:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // Criptografado automaticamente
    echo $session->get('credit_card'); // Descriptografado na recuperação
});
```

### Regeneração de Sessão
Regenere o ID da sessão por segurança (ex.: após o login):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // Novo ID, mantém os dados
    // OU
    $session->regenerate(true); // Novo ID, deleta os dados antigos
});
```

### Exemplo de Middleware
Proteja rotas com autenticação baseada em sessão:

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Bem-vindo ao painel de administração']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Acesso negado');
    }
});
```

Isto é apenas um exemplo simples de como usar isso em middleware. Para um exemplo mais detalhado, veja a documentação de [middleware](/learn/middleware).

## Métodos

A classe `Session` fornece esses métodos:

- `set(string $key, $value)`: Armazena um valor na sessão.
- `get(string $key, $default = null)`: Recupera um valor, com um padrão opcional se a chave não existir.
- `delete(string $key)`: Remove uma chave específica da sessão.
- `clear()`: Deleta todos os dados da sessão, mas mantém o mesmo nome de arquivo para a sessão.
- `commit()`: Salva os dados atuais da sessão no sistema de arquivos.
- `id()`: Retorna o ID da sessão atual.
- `regenerate(bool $deleteOldFile = false)`: Regenera o ID da sessão, incluindo a criação de um novo arquivo de sessão, mantendo todos os dados antigos e o arquivo antigo permanece no sistema. Se `$deleteOldFile` for `true`, o arquivo de sessão antigo é deletado.
- `destroy(string $id)`: Destrói uma sessão pelo ID e deleta o arquivo de sessão do sistema. Isso faz parte da `SessionHandlerInterface` e `$id` é obrigatório. Uso típico seria `$session->destroy($session->id())`.
- `getAll()` : Retorna todos os dados da sessão atual.

Todos os métodos, exceto `get()` e `id()`, retornam a instância `Session` para encadeamento.

## Por Que Usar Este Plugin?

- **Leve**: Sem dependências externas – apenas arquivos.
- **Não Bloqueante**: Evita bloqueio de sessão com `read_and_close` por padrão.
- **Seguro**: Suporta criptografia AES-256-CBC para dados sensíveis.
- **Flexível**: Opções de auto-commit, modo de teste e controle manual.
- **Nativo do Flight**: Construído especificamente para o framework Flight.

## Detalhes Técnicos

- **Formato de Armazenamento**: Arquivos de sessão são prefixados com `sess_` e armazenados no `save_path` configurado. Prefixos de conteúdo do arquivo:
  - `J`: JSON simples (padrão, sem criptografia)
  - `F`: JSON criptografado (padrão com criptografia)
  - `P`: Serialização PHP simples (legado, sem criptografia)
  - `E`: Serialização PHP criptografada (legado com criptografia)
- **Criptografia**: Usa AES-256-CBC com IV aleatório por gravação de sessão quando uma `encryption_key` é fornecida. A criptografia funciona para ambos os modos de serialização JSON e PHP.
- **Serialização**: JSON é o método padrão e mais seguro. Serialização PHP está disponível para uso legado/avançado, mas é menos segura.
- **Coleta de Lixo**: Implementa `SessionHandlerInterface::gc()` do PHP para limpar sessões expiradas.

## Contribuindo

Contribuições são bem-vindas! Faça fork no [repositório](https://github.com/flightphp/session), faça suas alterações e envie um pull request. Relate bugs ou sugira recursos via o rastreador de issues do Github.

## Licença

Este plugin é licenciado sob a Licença MIT. Veja o [repositório no Github](https://github.com/flightphp/session) para detalhes.