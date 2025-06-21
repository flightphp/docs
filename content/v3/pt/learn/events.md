# Sistema de Eventos no Flight PHP (v3.15.0+)

O Flight PHP introduz um sistema de eventos leve e intuitivo que permite registrar e disparar eventos personalizados em seu aplicativo. Com a adição de `Flight::onEvent()` e `Flight::triggerEvent()`, você pode agora se conectar a momentos chave do ciclo de vida do seu app ou definir seus próprios eventos para tornar seu código mais modular e extensível. Esses métodos fazem parte dos **métodos mapeáveis** do Flight, o que significa que você pode sobrescrever seu comportamento para atender às suas necessidades.

Este guia cobre tudo o que você precisa saber para começar com eventos, incluindo por que eles são valiosos, como usá-los e exemplos práticos para ajudar iniciantes a entenderem seu poder.

## Por que Usar Eventos?

Os eventos permitem separar diferentes partes do seu aplicativo para que elas não dependam excessivamente umas das outras. Essa separação — frequentemente chamada de **desacoplamento** — torna seu código mais fácil de atualizar, estender ou depurar. Em vez de escrever tudo em um grande bloco, você pode dividir sua lógica em peças menores e independentes que respondem a ações específicas (eventos).

Imagine que você está construindo um app de blog:
- Quando um usuário posta um comentário, você pode querer:
  - Salvar o comentário no banco de dados.
  - Enviar um email para o dono do blog.
  - Registrar a ação para segurança.

Sem eventos, você enfiaria tudo isso em uma única função. Com eventos, você pode dividir: uma parte salva o comentário, outra dispara um evento como `'comment.posted'`, e ouvintes separados lidam com o email e o registro. Isso mantém seu código mais limpo e permite adicionar ou remover recursos (como notificações) sem tocar na lógica principal.

### Usos Comuns
- **Registro**: Registre ações como logins ou erros sem entulhar seu código principal.
- **Notificações**: Envie emails ou alertas quando algo acontece.
- **Atualizações**: Atualize caches ou notifique outros sistemas sobre mudanças.

## Registrando Ouvintes de Eventos

Para ouvir um evento, use `Flight::onEvent()`. Esse método permite definir o que deve acontecer quando um evento ocorre.

### Sintaxe
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Um nome para seu evento (ex.: `'user.login'`).
- `$callback`: A função a ser executada quando o evento é disparado.

### Como Funciona
Você "se inscreve" em um evento informando ao Flight o que fazer quando ele acontece. O callback pode aceitar argumentos passados do disparo do evento.

O sistema de eventos do Flight é síncrono, o que significa que cada ouvinte de evento é executado em sequência, um após o outro. Quando você dispara um evento, todos os ouvintes registrados para aquele evento serão executados até o fim antes que seu código continue. Isso é importante de entender, pois difere de sistemas de eventos assíncronos onde os ouvintes podem rodar em paralelo ou em um momento posterior.

### Exemplo Simples
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";
});
```
Aqui, quando o evento `'user.login'` é disparado, ele cumprimenta o usuário pelo nome.

### Pontos Chave
- Você pode adicionar vários ouvintes ao mesmo evento — eles serão executados na ordem em que foram registrados.
- O callback pode ser uma função, uma função anônima ou um método de uma classe.

## Disparando Eventos

Para fazer um evento acontecer, use `Flight::triggerEvent()`. Isso informa ao Flight para executar todos os ouvintes registrados para aquele evento, passando qualquer dado que você fornecer.

### Sintaxe
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: O nome do evento que você está disparando (deve corresponder a um evento registrado).
- `...$args`: Argumentos opcionais para enviar aos ouvintes (pode ser qualquer número de argumentos).

### Exemplo Simples
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Isso dispara o evento `'user.login'` e envia `'alice'` para o ouvinte que definimos anteriormente, o que resultará na saída: `Welcome back, alice!`.

### Pontos Chave
- Se nenhum ouvinte estiver registrado, nada acontece — seu app não quebrará.
- Use o operador de espalhamento (`...`) para passar vários argumentos de forma flexível.

### Registrando Ouvintes de Eventos

...

**Parando Ouvintes Adicionais**:
Se um ouvinte retornar `false`, nenhum ouvinte adicional para aquele evento será executado. Isso permite que você pare a cadeia de eventos com base em condições específicas. Lembre-se, a ordem dos ouvintes importa, pois o primeiro a retornar `false` impedirá o resto de rodar.

**Exemplo**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Para ouvintes subsequentes
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // isso nunca é enviado
});
```

## Sobrescrevendo Métodos de Eventos

`Flight::onEvent()` e `Flight::triggerEvent()` estão disponíveis para serem [estendidos](/learn/extending), o que significa que você pode redefinir como eles funcionam. Isso é ótimo para usuários avançados que desejam personalizar o sistema de eventos, como adicionar registro ou alterar como os eventos são despachados.

### Exemplo: Personalizando `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Registrar cada registro de evento
    error_log("New event listener added for: $event");
    // Chamar o comportamento padrão (assumindo um sistema de eventos interno)
    Flight::_onEvent($event, $callback);
});
```
Agora, toda vez que você registrar um evento, ele será registrado antes de prosseguir.

### Por que Sobrescrever?
- Adicionar depuração ou monitoramento.
- Restringir eventos em certos ambientes (ex.: desativar em testes).
- Integrar com uma biblioteca de eventos diferente.

## Onde Colocar Seus Eventos

Como iniciante, você pode se perguntar: *onde eu registro todos esses eventos no meu app?* A simplicidade do Flight significa que não há regra estrita — você pode colocá-los onde fizer sentido para o seu projeto. No entanto, mantê-los organizados ajuda a manter seu código à medida que seu app cresce. Aqui estão algumas opções práticas e melhores práticas, adaptadas à natureza leve do Flight:

### Opção 1: No Seu Principal `index.php`
Para apps pequenos ou protótipos rápidos, você pode registrar eventos diretamente no seu arquivo `index.php` junto com suas rotas. Isso mantém tudo em um lugar, o que é bom quando a simplicidade é a prioridade.

```php
require 'vendor/autoload.php';

// Registrar eventos
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// Definir rotas
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Prós**: Simples, sem arquivos extras, ótimo para projetos pequenos.
- **Contras**: Pode ficar bagunçado à medida que seu app cresce com mais eventos e rotas.

### Opção 2: Um Arquivo Separado `events.php`
Para um app um pouco maior, considere mover os registros de eventos para um arquivo dedicado como `app/config/events.php`. Inclua esse arquivo no seu `index.php` antes das rotas. Isso imita como as rotas são frequentemente organizadas em `app/config/routes.php` em projetos do Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Prós**: Mantém o `index.php` focado nas rotas, organiza eventos de forma lógica, fácil de encontrar e editar.
- **Contras**: Adiciona um pouco de estrutura, o que pode parecer excessivo para apps muito pequenos.

### Opção 3: Perto de Onde Eles São Disparados
Outra abordagem é registrar eventos perto de onde eles são disparados, como dentro de um controlador ou definição de rota. Isso funciona bem se um evento for específico a uma parte do seu app.

```php
Flight::route('/signup', function () {
    // Registrar evento aqui
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **Prós**: Mantém o código relacionado junto, bom para recursos isolados.
- **Contras**: Espalha os registros de eventos, tornando mais difícil ver todos os eventos de uma vez; risco de registros duplicados se não for cuidadoso.

### Melhor Prática para o Flight
- **Comece Simples**: Para apps pequenos, coloque eventos em `index.php`. É rápido e alinhado com o minimalismo do Flight.
- **Cresça Inteligente**: À medida que seu app se expande (ex.: mais de 5-10 eventos), use um arquivo `app/config/events.php`. É um passo natural, como organizar rotas, e mantém seu código organizado sem adicionar frameworks complexos.
- **Evite Engenharia Excessiva**: Não crie uma classe ou diretório completo de “gerenciador de eventos” a menos que seu app fique enorme — o Flight prospera na simplicidade, então mantenha-o leve.

### Dica: Agrupe por Propósito
Em `events.php`, agrupe eventos relacionados (ex.: todos os eventos de usuário juntos) com comentários para clareza:

```php
// app/config/events.php
// Eventos de Usuário
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// Eventos de Página
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Essa estrutura escala bem e permanece amigável para iniciantes.

## Exemplos para Iniciantes

Vamos percorrer alguns cenários do mundo real para mostrar como os eventos funcionam e por que eles são úteis.

### Exemplo 1: Registrando um Login de Usuário
```php
// Passo 1: Registrar um ouvinte
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Passo 2: Dispará-lo no seu app
Flight::route('/login', function () {
    $username = 'bob'; // Finja que isso vem de um formulário
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Por Que É Útil**: O código de login não precisa saber sobre o registro — ele apenas dispara o evento. Você pode adicionar mais ouvintes (ex.: enviar um email de boas-vindas) mais tarde sem alterar a rota.

### Exemplo 2: Notificando Sobre Novos Usuários
```php
// Ouvinte para novos registros
Flight::onEvent('user.registered', function ($email, $name) {
    // Simule o envio de um email
    echo "Email sent to $email: Welcome, $name!";
});

// Dispará-lo quando alguém se registra
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Por Que É Útil**: A lógica de registro se concentra em criar o usuário, enquanto o evento lida com notificações. Você poderia adicionar mais ouvintes (ex.: registrar o registro) mais tarde.

### Exemplo 3: Limpando um Cache
```php
// Ouvinte para limpar um cache
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Limpa o cache da sessão, se aplicável
    echo "Cache cleared for page $pageId.";
});

// Dispará-lo quando uma página é editada
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Finja que atualizamos a página
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Por Que É Útil**: O código de edição não se preocupa com o caching — ele apenas sinaliza a atualização. Outras partes do app podem reagir conforme necessário.

## Melhores Práticas

- **Nomeie Eventos Claramente**: Use nomes específicos como `'user.login'` ou `'page.updated'` para que fique óbvio o que eles fazem.
- **Mantenha Ouvintes Simples**: Não coloque tarefas lentas ou complexas em ouvintes — mantenha seu app rápido.
- **Teste Seus Eventos**: Dispará-los manualmente para garantir que os ouvintes funcionem como esperado.
- **Use Eventos com Sabedoria**: Eles são ótimos para desacoplamento, mas muitos podem tornar seu código difícil de seguir — use-os quando fizer sentido.

O sistema de eventos no Flight PHP, com `Flight::onEvent()` e `Flight::triggerEvent()`, oferece uma forma simples, mas poderosa, de construir aplicativos flexíveis. Ao permitir que diferentes partes do seu app se comuniquem por meio de eventos, você pode manter seu código organizado, reutilizável e fácil de expandir. Seja registrando ações, enviando notificações ou gerenciando atualizações, os eventos ajudam a fazer isso sem emaranhar sua lógica. Além disso, com a capacidade de sobrescrever esses métodos, você tem a liberdade de adaptar o sistema às suas necessidades. Comece pequeno com um único evento e veja como ele transforma a estrutura do seu app!

## Eventos Integrados

O Flight PHP vem com alguns eventos integrados que você pode usar para se conectar ao ciclo de vida do framework. Esses eventos são disparados em pontos específicos do ciclo de solicitação/resposta, permitindo que você execute lógica personalizada quando certas ações ocorrem.

### Lista de Eventos Integrados
- **flight.request.received**: `function(Request $request)` Disparado quando uma solicitação é recebida, analisada e processada.
- **flight.error**: `function(Throwable $exception)` Disparado quando um erro ocorre durante o ciclo de vida da solicitação.
- **flight.redirect**: `function(string $url, int $status_code)` Disparado quando uma redireção é iniciada.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Disparado quando o cache é verificado para uma chave específica e se houve acerto ou falha no cache.
- **flight.middleware.before**: `function(Route $route)`Disparado após a execução do middleware before.
- **flight.middleware.after**: `function(Route $route)` Disparado após a execução do middleware after.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Disparado após a execução de qualquer middleware
- **flight.route.matched**: `function(Route $route)` Disparado quando uma rota é correspondida, mas ainda não executada.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Disparado após a execução de uma rota e processamento. `$executionTime` é o tempo que levou para executar a rota (chamar o controlador, etc).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Disparado após uma visualização ser renderizada. `$executionTime` é o tempo que levou para renderizar o template. **Nota: Se você sobrescrever o método `render`, você precisará disparar este evento novamente.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Disparado após uma resposta ser enviada para o cliente. `$executionTime` é o tempo que levou para construir a resposta.