# Gerenciador de Eventos

_a partir da v3.15.0_

## Visão Geral

Os eventos permitem que você registre e dispare comportamentos personalizados em sua aplicação. Com a adição de `Flight::onEvent()` e `Flight::triggerEvent()`, você pode agora se conectar a momentos chave do ciclo de vida da sua aplicação ou definir seus próprios eventos (como notificações e e-mails) para tornar seu código mais modular e extensível. Esses métodos fazem parte dos [métodos mapeáveis da Flight](/learn/extending), o que significa que você pode sobrescrever seu comportamento para atender às suas necessidades.

## Entendendo

Os eventos permitem que você separe diferentes partes da sua aplicação para que elas não dependam excessivamente umas das outras. Essa separação — frequentemente chamada de **desacoplamento** — torna seu código mais fácil de atualizar, estender ou depurar. Em vez de escrever tudo em um grande bloco, você pode dividir sua lógica em peças menores e independentes que respondem a ações específicas (eventos).

Imagine que você está construindo uma aplicação de blog:
- Quando um usuário posta um comentário, você pode querer:
  - Salvar o comentário no banco de dados.
  - Enviar um e-mail para o proprietário do blog.
  - Registrar a ação para segurança.

Sem eventos, você enfiaria tudo isso em uma única função. Com eventos, você pode dividi-lo: uma parte salva o comentário, outra dispara um evento como `'comment.posted'`, e ouvintes separados lidam com o e-mail e o registro. Isso mantém seu código mais limpo e permite que você adicione ou remova recursos (como notificações) sem tocar na lógica principal.

### Casos de Uso Comuns

Na maior parte do tempo, os eventos são bons para coisas que são opcionais, mas não uma parte absolutamente central do seu sistema. Por exemplo, os seguintes são bons de ter, mas se eles falharem por algum motivo, sua aplicação ainda deve funcionar:

- **Registro**: Registrar ações como logins ou erros sem bagunçar seu código principal.
- **Notificações**: Enviar e-mails ou alertas quando algo acontece.
- **Atualizações de Cache**: Atualizar caches ou notificar outros sistemas sobre mudanças.

No entanto, digamos que você tenha um recurso de esquecimento de senha. Isso deve fazer parte da funcionalidade principal e não ser um evento, porque se esse e-mail não for enviado, o usuário não pode redefinir a senha e usar sua aplicação.

## Uso Básico

O sistema de eventos da Flight é construído em torno de dois métodos principais: `Flight::onEvent()` para registrar ouvintes de eventos e `Flight::triggerEvent()` para disparar eventos. Aqui está como você pode usá-los:

### Registrando Ouvintes de Eventos

Para escutar um evento, use `Flight::onEvent()`. Esse método permite que você defina o que deve acontecer quando um evento ocorre.

```php
Flight::onEvent(string $event, callable $callback): void
```

- `$event`: Um nome para o seu evento (ex.: `'user.login'`).
- `$callback`: A função a ser executada quando o evento é disparado.

Você "se inscreve" em um evento ao dizer à Flight o que fazer quando ele acontece. O callback pode aceitar argumentos passados do disparo do evento.

O sistema de eventos da Flight é síncrono, o que significa que cada ouvinte de evento é executado em sequência, um após o outro. Quando você dispara um evento, todos os ouvintes registrados para esse evento serão executados até o fim antes que seu código continue. Isso é importante de entender, pois difere de sistemas de eventos assíncronos onde os ouvintes podem rodar em paralelo ou em um momento posterior.

#### Exemplo Simples
```php
Flight::onEvent('user.login', function ($username) {
    echo "Bem-vindo de volta, $username!";

	// você pode enviar um e-mail se o login for de um novo local
});
```
Aqui, quando o evento `'user.login'` é disparado, ele cumprimentará o usuário pelo nome e também pode incluir lógica para enviar um e-mail se necessário.

> **Nota:** O callback pode ser uma função, uma função anônima ou um método de uma classe.

### Disparando Eventos

Para fazer um evento acontecer, use `Flight::triggerEvent()`. Isso diz à Flight para executar todos os ouvintes registrados para esse evento, passando qualquer dado que você fornecer.

```php
Flight::triggerEvent(string $event, ...$args): void
```

- `$event`: O nome do evento que você está disparando (deve corresponder a um evento registrado).
- `...$args`: Argumentos opcionais para enviar aos ouvintes (pode ser qualquer número de argumentos).

#### Exemplo Simples
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Isso dispara o evento `'user.login'` e envia `'alice'` para o ouvinte que definimos anteriormente, o que exibirá: `Welcome back, alice!`.

- Se nenhum ouvinte estiver registrado, nada acontece — sua aplicação não quebrará.
- Use o operador de espalhamento (`...`) para passar múltiplos argumentos de forma flexível.

### Parando Eventos

Se um ouvinte retornar `false`, nenhum ouvinte adicional para esse evento será executado. Isso permite que você pare a cadeia de eventos com base em condições específicas. Lembre-se, a ordem dos ouvintes importa, pois o primeiro a retornar `false` impedirá o resto de rodar.

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

### Sobrescrevendo Métodos de Eventos

`Flight::onEvent()` e `Flight::triggerEvent()` estão disponíveis para serem [estendidos](/learn/extending), o que significa que você pode redefinir como eles funcionam. Isso é ótimo para usuários avançados que querem personalizar o sistema de eventos, como adicionar registro ou alterar como os eventos são despachados.

#### Exemplo: Personalizando `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Registra toda inscrição de evento
    error_log("Novo ouvinte de evento adicionado para: $event");
    // Chama o comportamento padrão (assumindo um sistema de eventos interno)
    Flight::_onEvent($event, $callback);
});
```
Agora, toda vez que você registrar um evento, ele será registrado antes de prosseguir.

#### Por Que Sobrescrever?
- Adicionar depuração ou monitoramento.
- Restringir eventos em certos ambientes (ex.: desabilitar em testes).
- Integrar com uma biblioteca de eventos diferente.

### Onde Colocar Seus Eventos

Se você é novo nos conceitos de eventos no seu projeto, pode se perguntar: *onde eu registro todos esses eventos na minha aplicação?* A simplicidade da Flight significa que não há uma regra estrita — você pode colocá-los onde fizer sentido para o seu projeto. No entanto, mantê-los organizados ajuda a manter seu código à medida que sua aplicação cresce. Aqui estão algumas opções práticas e melhores práticas, adaptadas à natureza leve da Flight:

#### Opção 1: No Seu `index.php` Principal
Para aplicações pequenas ou protótipos rápidos, você pode registrar eventos diretamente no seu arquivo `index.php` junto com suas rotas. Isso mantém tudo em um só lugar, o que é bom quando a simplicidade é a prioridade.

```php
require 'vendor/autoload.php';

// Registra eventos
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// Define rotas
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Prós**: Simples, sem arquivos extras, ótimo para projetos pequenos.
- **Contras**: Pode ficar bagunçado à medida que sua aplicação cresce com mais eventos e rotas.

#### Opção 2: Um Arquivo `events.php` Separado
Para uma aplicação um pouco maior, considere mover os registros de eventos para um arquivo dedicado como `app/config/events.php`. Inclua este arquivo no seu `index.php` antes das suas rotas. Isso imita como as rotas são frequentemente organizadas em `app/config/routes.php` em projetos Flight.

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
- **Prós**: Mantém o `index.php` focado em roteamento, organiza eventos logicamente, fácil de encontrar e editar.
- **Contras**: Adiciona um pouquinho de estrutura, o que pode parecer exagero para aplicações muito pequenas.

#### Opção 3: Perto de Onde Eles São Disparados
Outra abordagem é registrar eventos perto de onde eles são disparados, como dentro de um controlador ou definição de rota. Isso funciona bem se um evento for específico para uma parte da sua aplicação.

```php
Flight::route('/signup', function () {
    // Registra evento aqui
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

#### Melhor Prática para Flight
- **Comece Simples**: Para aplicações minúsculas, coloque eventos no `index.php`. É rápido e alinha com o minimalismo da Flight.
- **Cresça de Forma Inteligente**: À medida que sua aplicação expande (ex.: mais de 5-10 eventos), use um arquivo `app/config/events.php`. É um passo natural, como organizar rotas, e mantém seu código organizado sem adicionar frameworks complexos.
- **Evite Superengenharia**: Não crie uma classe ou diretório “gerenciador de eventos” completo a menos que sua aplicação fique enorme — a Flight prospera na simplicidade, então mantenha leve.

#### Dica: Agrupe por Propósito
Em `events.php`, agrupe eventos relacionados (ex.: todos os eventos relacionados a usuários juntos) com comentários para clareza:

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
    Flight::cache()->delete("page_$pageId");
});
```

Essa estrutura escala bem e permanece amigável para iniciantes.

### Exemplos do Mundo Real

Vamos percorrer alguns cenários do mundo real para mostrar como os eventos funcionam e por que eles são úteis.

#### Exemplo 1: Registrando um Login de Usuário
```php
// Passo 1: Registra um ouvinte
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Passo 2: Dispare-o na sua aplicação
Flight::route('/login', function () {
    $username = 'bob'; // Finja que isso vem de um formulário
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Por Que É Útil**: O código de login não precisa saber sobre o registro — ele apenas dispara o evento. Você pode adicionar mais ouvintes depois (ex.: enviar um e-mail de boas-vindas) sem alterar a rota.

#### Exemplo 2: Notificando Sobre Novos Usuários
```php
// Ouvinte para novos registros
Flight::onEvent('user.registered', function ($email, $name) {
    // Simula o envio de um e-mail
    echo "Email sent to $email: Welcome, $name!";
});

// Dispare quando alguém se inscreve
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Por Que É Útil**: A lógica de inscrição foca em criar o usuário, enquanto o evento lida com notificações. Você pode adicionar mais ouvintes (ex.: registrar a inscrição) depois.

#### Exemplo 3: Limpando um Cache
```php
// Ouvinte para limpar um cache
Flight::onEvent('page.updated', function ($pageId) {
	// se usando o plugin flightphp/cache
    Flight::cache()->delete("page_$pageId");
    echo "Cache cleared for page $pageId.";
});

// Dispare quando uma página é editada
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Finja que atualizamos a página
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Por Que É Útil**: O código de edição não se importa com cache — ele apenas sinaliza a atualização. Outras partes da aplicação podem reagir conforme necessário.

### Melhores Práticas

- **Nomeie Eventos Claramente**: Use nomes específicos como `'user.login'` ou `'page.updated'` para que seja óbvio o que eles fazem.
- **Mantenha Ouvintes Simples**: Não coloque tarefas lentas ou complexas em ouvintes — mantenha sua aplicação rápida.
- **Teste Seus Eventos**: Dispare-os manualmente para garantir que os ouvintes funcionem como esperado.
- **Use Eventos com Sabedoria**: Eles são ótimos para desacoplamento, mas muitos podem tornar seu código difícil de seguir — use-os quando fizer sentido.

O sistema de eventos na Flight PHP, com `Flight::onEvent()` e `Flight::triggerEvent()`, dá a você uma forma simples, mas poderosa, de construir aplicações flexíveis. Ao permitir que diferentes partes da sua aplicação se comuniquem através de eventos, você pode manter seu código organizado, reutilizável e fácil de expandir. Seja registrando ações, enviando notificações ou gerenciando atualizações, os eventos ajudam você a fazer isso sem emaranhar sua lógica. Além disso, com a capacidade de sobrescrever esses métodos, você tem a liberdade de adaptar o sistema às suas necessidades. Comece pequeno com um único evento e veja como ele transforma a estrutura da sua aplicação!

### Eventos Integrados

A Flight PHP vem com alguns eventos integrados que você pode usar para se conectar ao ciclo de vida do framework. Esses eventos são disparados em pontos específicos do ciclo de solicitação/resposta, permitindo que você execute lógica personalizada quando certas ações ocorrem.

#### Lista de Eventos Integrados
- **flight.request.received**: `function(Request $request)` Disparado quando uma solicitação é recebida, analisada e processada.
- **flight.error**: `function(Throwable $exception)` Disparado quando um erro ocorre durante o ciclo de vida da solicitação.
- **flight.redirect**: `function(string $url, int $status_code)` Disparado quando um redirecionamento é iniciado.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Disparado quando o cache é verificado para uma chave específica e se houve acerto ou falha no cache.
- **flight.middleware.before**: `function(Route $route)`Disparado após a execução do middleware before.
- **flight.middleware.after**: `function(Route $route)` Disparado após a execução do middleware after.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Disparado após a execução de qualquer middleware
- **flight.route.matched**: `function(Route $route)` Disparado quando uma rota é correspondida, mas ainda não executada.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Disparado após uma rota ser executada e processada. `$executionTime` é o tempo que levou para executar a rota (chamar o controlador, etc).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Disparado após uma view ser renderizada. `$executionTime` é o tempo que levou para renderizar o template. **Nota: Se você sobrescrever o método `render`, precisará disparar novamente este evento.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Disparado após uma resposta ser enviada ao cliente. `$executionTime` é o tempo que levou para construir a resposta.

## Veja Também
- [Estendendo a Flight](/learn/extending) - Como estender e personalizar a funcionalidade principal da Flight.
- [Cache](/awesome-plugins/php_file_cache) - Exemplo de uso de eventos para limpar o cache quando uma página é atualizada.

## Solução de Problemas
- Se você não estiver vendo seus ouvintes de eventos sendo chamados, certifique-se de registrá-los antes de disparar os eventos. A ordem de registro importa.

## Registro de Alterações
- v3.15.0 - Adicionados eventos à Flight.