# FlightPHP Sessão - Manipulador de Sessão Leve Baseado em Arquivo

Este é um plugin de manipulador de sessão leve e baseado em arquivo para o [Flight PHP Framework](https://docs.flightphp.com/). Ele fornece uma solução simples, mas poderosa para gerenciar sessões, com recursos como leituras de sessão não bloqueantes, criptografia opcional, funcionalidade de auto-confirmação e um modo de teste para desenvolvimento. Os dados da sessão são armazenados em arquivos, tornando-o ideal para aplicativos que não exigem um banco de dados.

Se você quiser usar um banco de dados, confira o plugin [ghostff/session](/awesome-plugins/ghost-session) com muitas dessas mesmas características, mas com um backend de banco de dados.

Visite o [repositório do Github](https://github.com/flightphp/session) para o código-fonte completo e detalhes.

## Instalação

Instale o plugin via Composer:

```bash
composer require flightphp/session
```

## Uso Básico

Aqui está um exemplo simples de como usar o plugin `flightphp/session` em sua aplicação Flight:

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
    Flight::json(['message' => 'Desconectado com sucesso']);
});

Flight::start();
```

### Pontos-Chave
- **Não-Bloqueante**: Usa `read_and_close` para iniciar a sessão por padrão, evitando problemas de bloqueio de sessão.
- **Auto-Confirmação**: Habilitado por padrão, então as alterações são salvas automaticamente no desligamento, a menos que desativado.
- **Armazenamento em Arquivo**: Sessões são armazenadas no diretório temporário do sistema sob `/flight_sessions` por padrão.

## Configuração

Você pode personalizar o manipulador de sessão passando um array de opções ao registrá-lo:

```php
$app->register('session', Session::class, [
    'save_path' => '/custom/path/to/sessions',         // Diretório para os arquivos de sessão
    'encryption_key' => 'a-secure-32-byte-key-here',   // Habilitar criptografia (32 bytes recomendado para AES-256-CBC)
    'auto_commit' => false,                            // Desativar auto-confirmação para controle manual
    'start_session' => true,                           // Iniciar sessão automaticamente (padrão: true)
    'test_mode' => false                               // Habilitar modo de teste para desenvolvimento
]);
```

### Opções de Configuração
| Opção             | Descrição                                        | Valor Padrão                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | Diretório onde os arquivos de sessão são armazenados | `sys_get_temp_dir() . '/flight_sessions'` |
| `encryption_key`  | Chave para criptografia AES-256-CBC (opcional)  | `null` (sem criptografia)        |
| `auto_commit`     | Salvar dados da sessão automaticamente ao desligar | `true`                            |
| `start_session`   | Iniciar a sessão automaticamente                  | `true`                            |
| `test_mode`       | Executar em modo de teste sem afetar as sessões do PHP | `false`                           |
| `test_session_id` | ID de sessão personalizado para o modo de teste (opcional) | Gerado aleatoriamente se não definido |

## Uso Avançado

### Confirmação Manual
Se você desativar a auto-confirmação, deverá confirmar manualmente as alterações:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Salva explicitamente as alterações
});
```

### Segurança da Sessão com Criptografia
Habilite a criptografia para dados sensíveis:

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

### Regeneração da Sessão
Regere a ID da sessão para segurança (por exemplo, após login):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // Nova ID, mantém os dados
    // OU
    $session->regenerate(true); // Nova ID, exclui dados antigos
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

Este é apenas um exemplo simples de como usar isso em middleware. Para um exemplo mais detalhado, consulte a [documentação de middleware](/learn/middleware).

## Métodos

A classe `Session` fornece estes métodos:

- `set(string $key, $value)`: Armazena um valor na sessão.
- `get(string $key, $default = null)`: Recupera um valor, com um padrão opcional se a chave não existir.
- `delete(string $key)`: Remove uma chave específica da sessão.
- `clear()`: Exclui todos os dados da sessão.
- `commit()`: Salva os dados atuais da sessão no sistema de arquivos.
- `id()`: Retorna a ID da sessão atual.
- `regenerate(bool $deleteOld = false)`: Regenera a ID da sessão, excluindo opcionalmente os dados antigos.

Todos os métodos, exceto `get()` e `id()`, retornam a instância `Session` para encadeamento.

## Por Que Usar Este Plugin?

- **Leve**: Sem dependências externas—apenas arquivos.
- **Não-Bloqueante**: Evita bloqueio de sessão com `read_and_close` por padrão.
- **Seguro**: Suporta criptografia AES-256-CBC para dados sensíveis.
- **Flexível**: Opções de auto-confirmação, modo de teste e controle manual.
- **Nativo ao Flight**: Construído especificamente para o framework Flight.

## Detalhes Técnicos

- **Formato de Armazenamento**: Os arquivos de sessão são prefixados com `sess_` e armazenados no `save_path` configurado. Dados criptografados usam um prefixo `E`, texto não criptografado usa `P`.
- **Criptografia**: Usa AES-256-CBC com um IV aleatório por gravação de sessão quando uma `encryption_key` é fornecida.
- **Coleta de Lixo**: Implementa o `SessionHandlerInterface::gc()` do PHP para limpar sessões expiradas.

## Contribuindo

Contribuições são bem-vindas! Fork o [repositório](https://github.com/flightphp/session), faça suas alterações e envie um pull request. Relate bugs ou sugira recursos pelo rastreador de problemas do Github.

## Licença

Este plugin é licenciado sob a Licença MIT. Consulte o [repositório do Github](https://github.com/flightphp/session) para detalhes.