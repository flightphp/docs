# Sistema de Eventos no Flight PHP (v3.15.0+)

O Flight PHP introduz um sistema de eventos leve e intuitivo que permite registrar e acionar eventos personalizados em sua aplicação. Com a adição de `Flight::onEvent()` e `Flight::triggerEvent()`, você agora pode se conectar a momentos-chave do ciclo de vida do seu aplicativo ou definir seus próprios eventos para tornar seu código mais modular e extensível. Esses métodos fazem parte dos **métodos mapeáveis** do Flight, o que significa que você pode sobrescrever seu comportamento para atender às suas necessidades.

Este guia cobre tudo o que você precisa saber para começar a trabalhar com eventos, incluindo por que eles são valiosos, como usá-los e exemplos práticos para ajudar iniciantes a entender seu poder.

## Por que Usar Eventos?

Eventos permitem que você separe diferentes partes da sua aplicação para que não dependam muito uma da outra. Essa separação—frequentemente chamada de **desacoplamento**—torna seu código mais fácil de atualizar, estender ou depurar. Em vez de escrever tudo em um único bloco grande, você pode dividir sua lógica em partes menores e independentes que respondem a ações específicas (eventos).

Imagine que você está construindo um aplicativo de blog:
- Quando um usuário publica um comentário, você pode querer:
  - Salvar o comentário no banco de dados.
  - Enviar um e-mail ao proprietário do blog.
  - Registrar a ação para segurança.

Sem eventos, você colocaria tudo isso em uma única função. Com eventos, você pode separá-los: uma parte salva o comentário, outra aciona um evento como `'comment.posted'`, e ouvintes separados lidam com o e-mail e o registro. Isso mantém seu código mais limpo e permite que você adicione ou remova recursos (como notificações) sem tocar na lógica principal.

### Usos Comuns
- **Registro**: Registre ações como logins ou erros sem poluir seu código principal.
- **Notificações**: Envie e-mails ou alertas quando algo acontecer.
- **Atualizações**: Atualize caches ou notifique outros sistemas sobre mudanças.

## Registrando Ouvintes de Eventos

Para ouvir um evento, use `Flight::onEvent()`. Este método permite que você defina o que deve acontecer quando um evento ocorre.

### Sintaxe
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Um nome para o seu evento (por exemplo, `'user.login'`).
- `$callback`: A função a ser executada quando o evento for acionado.

### Como Funciona
Você "se inscreve" em um evento informando ao Flight o que fazer quando ele acontece. O callback pode aceitar argumentos passados do acionador do evento.

O sistema de eventos do Flight é síncrono, o que significa que cada ouvinte de evento é executado em sequência, um após o outro. Quando você aciona um evento, todos os ouvintes registrados para esse evento serão executados até a conclusão antes que seu código continue. Isso é importante entender, pois difere de sistemas de eventos assíncronos onde os ouvintes podem ser executados em paralelo ou em um momento posterior.

### Exemplo Simples
```php
Flight::onEvent('user.login', function ($username) {
    echo "Bem-vindo de volta, $username!";
});
```
Aqui, quando o evento `'user.login'` é acionado, cumprimenta o usuário pelo nome.

### Pontos Chave
- Você pode adicionar vários ouvintes ao mesmo evento—eles serão executados na ordem em que você os registrou.
- O callback pode ser uma função, uma função anônima ou um método de uma classe.

## Acionando Eventos

Para fazer um evento acontecer, use `Flight::triggerEvent()`. Isso informa ao Flight para executar todos os ouvintes registrados para esse evento, passando qualquer dado que você fornecer.

### Sintaxe
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: O nome do evento que você está acionando (deve corresponder a um evento registrado).
- `...$args`: Argumentos opcionais a serem enviados aos ouvintes (pode ser qualquer número de argumentos).

### Exemplo Simples
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Isso aciona o evento `'user.login'` e envia `'alice'` para o ouvinte que definimos anteriormente, que irá produzir: `Bem-vindo de volta, alice!`.

### Pontos Chave
- Se nenhum ouvinte estiver registrado, nada acontece—seu aplicativo não quebrará.
- Use o operador de propagação (`...`) para passar múltiplos argumentos de forma flexível.

### Registrando Ouvintes de Eventos

...

**Parando Ouvintes Futuro**:
Se um ouvinte retornar `false`, nenhum ouvinte adicional para esse evento será executado. Isso permite que você interrompa a cadeia de eventos com base em condições específicas. Lembre-se, a ordem dos ouvintes importa, pois o primeiro a retornar `false` impedirá o restante de serem executados.

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

## Sobrescrevendo Métodos de Evento

`Flight::onEvent()` e `Flight::triggerEvent()` estão disponíveis para serem [estendidos](/learn/extending), o que significa que você pode redefinir como eles funcionam. Isso é ótimo para usuários avançados que desejam personalizar o sistema de eventos, como adicionar registro ou alterar como os eventos são despachados.

### Exemplo: Personalizando `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Registrar cada registro de evento
    error_log("Novo ouvinte de evento adicionado para: $event");
    // Chame o comportamento padrão (assumindo um sistema de eventos interno)
    Flight::_onEvent($event, $callback);
});
```
Agora, toda vez que você registrar um evento, ele será registrado antes de prosseguir.

### Por que Sobrescrever?
- Adicione depuração ou monitoramento.
- Restringir eventos em certos ambientes (por exemplo, desabilite em testes).
- Integre-se com uma biblioteca de eventos diferente.

## Onde Colocar Seus Eventos

Como iniciante, você pode se perguntar: *onde registro todos esses eventos no meu aplicativo?* A simplicidade do Flight significa que não há uma regra estrita—você pode colocá-los onde fizer sentido para seu projeto. No entanto, mantê-los organizados ajuda a manter seu código à medida que seu aplicativo cresce. Aqui estão algumas opções práticas e melhores práticas, adaptadas à natureza leve do Flight:

### Opção 1: No Seu Principal `index.php`
Para pequenos aplicativos ou protótipos rápidos, você pode registrar eventos diretamente no seu arquivo `index.php` junto com suas rotas. Isso mantém tudo em um só lugar, o que é aceitável quando a simplicidade é sua prioridade.

```php
require 'vendor/autoload.php';

// Registrar eventos
Flight::onEvent('user.login', function ($username) {
    error_log("$username fez login em " . date('Y-m-d H:i:s'));
});

// Definir rotas
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logado!";
});

Flight::start();
```
- **Prós**: Simples, sem arquivos extras, ótimo para pequenos projetos.
- **Contras**: Pode ficar bagunçado à medida que seu aplicativo cresce com mais eventos e rotas.

### Opção 2: Um Arquivo Separado `events.php`
Para um aplicativo um pouco maior, considere mover os registros de eventos para um arquivo dedicado, como `app/config/events.php`. Inclua este arquivo no seu `index.php` antes das suas rotas. Isso imita como as rotas são frequentemente organizadas em `app/config/routes.php` em projetos Flight.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username fez login em " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "E-mail enviado para $email: Bem-vindo, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logado!";
});

Flight::start();
```
- **Prós**: Mantém `index.php` focado em rotas, organiza eventos logicamente, fácil de encontrar e editar.
- **Contras**: Adiciona um pequeno grau de estrutura, o que pode parecer exagero para aplicativos muito pequenos.

### Opção 3: Perto de Onde Eles São Acionados
Outra abordagem é registrar eventos perto de onde eles são acionados, como dentro de um controlador ou definição de rota. Isso funciona bem se um evento for específico de uma parte do seu aplicativo.

```php
Flight::route('/signup', function () {
    // Registrar evento aqui
    Flight::onEvent('user.registered', function ($email) {
        echo "E-mail de boas-vindas enviado para $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Inscrito!";
});
```
- **Prós**: Mantém o código relacionado junto, bom para recursos isolados.
- **Contras**: Espalha registros de eventos, tornando mais difícil ver todos os eventos de uma vez; riscos de registros duplicados se não tiver cuidado.

### Melhor Prática para Flight
- **Comece Simples**: Para aplicativos pequenos, coloque eventos em `index.php`. É rápido e alinhado com o minimalismo do Flight.
- **Cresça Inteligentemente**: À medida que seu aplicativo se expande (por exemplo, mais de 5-10 eventos), use um arquivo `app/config/events.php`. É um passo natural, como organizar rotas, e mantém seu código arrumado sem adicionar frameworks complexos.
- **Evite Sobrecarga de Engenharia**: Não crie uma classe ou diretório de “gerenciador de eventos” completo, a menos que seu aplicativo fique enorme—Flight prospera na simplicidade, então mantenha leve.

### Dica: Agrupe por Objetivo
Em `events.php`, agrupe eventos relacionados (por exemplo, todos os eventos relacionados a usuários juntos) com comentários para clareza:

```php
// app/config/events.php
// Eventos de Usuário
Flight::onEvent('user.login', function ($username) {
    error_log("$username fez login");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Bem-vindo a $email!";
});

// Eventos de Página
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Essa estrutura se escala bem e permanece amigável para iniciantes.

## Exemplos para Iniciantes

Vamos passar por alguns cenários da vida real para mostrar como os eventos funcionam e por que são úteis.

### Exemplo 1: Registro de Um Login de Usuário
```php
// Etapa 1: Registrar um ouvinte
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username fez login em $time");
});

// Etapa 2: Acionar no seu aplicativo
Flight::route('/login', function () {
    $username = 'bob'; // Suponha que isso venha de um formulário
    Flight::triggerEvent('user.login', $username);
    echo "Oi, $username!";
});
```
**Por que é Útil**: O código de login não precisa saber sobre o registro—ele apenas aciona o evento. Você pode adicionar mais ouvintes mais tarde (por exemplo, enviar um e-mail de boas-vindas) sem alterar a rota.

### Exemplo 2: Notificando sobre Novos Usuários
```php
// Ouvinte para novas inscrições
Flight::onEvent('user.registered', function ($email, $name) {
    // Simular o envio de um e-mail
    echo "E-mail enviado para $email: Bem-vindo, $name!";
});

// Acionar quando alguém se inscreve
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Obrigado por se inscrever!";
});
```
**Por que é Útil**: A lógica de inscrição se concentra em criar o usuário, enquanto o evento lida com notificações. Você poderia adicionar mais ouvintes (por exemplo, registrar a inscrição) mais tarde.

### Exemplo 3: Limpando Um Cache
```php
// Ouvinte para limpar um cache
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Limpar cache de sessão se aplicável
    echo "Cache limpo para a página $pageId.";
});

// Acionar quando uma página é editada
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Suponha que atualizamos a página
    Flight::triggerEvent('page.updated', $pageId);
    echo "Página $pageId atualizada.";
});
```
**Por que é Útil**: O código de edição não se preocupa com o cache—ele apenas sinaliza a atualização. Outras partes do aplicativo podem reagir conforme necessário.

## Melhores Práticas

- **Nomeie os Eventos Claramente**: Use nomes específicos como `'user.login'` ou `'page.updated'` para que fique óbvio o que eles fazem.
- **Mantenha os Ouvintes Simples**: Não coloque tarefas lentas ou complexas em ouvintes—mantenha seu aplicativo rápido.
- **Teste Seus Eventos**: Acione-os manualmente para garantir que os ouvintes funcionem como esperado.
- **Use Eventos Com Sabedoria**: Eles são ótimos para desacoplamento, mas muitos podem tornar seu código difícil de seguir—use-os quando fizer sentido.

O sistema de eventos no Flight PHP, com `Flight::onEvent()` e `Flight::triggerEvent()`, oferece a você uma maneira simples, mas poderosa, de construir aplicativos flexíveis. Ao permitir que diferentes partes do seu aplicativo se comuniquem através de eventos, você pode manter seu código organizado, reutilizável e fácil de expandir. Se você está registrando ações, enviando notificações ou gerenciando atualizações, os eventos ajudam você a fazer isso sem emaranhar sua lógica. Além disso, com a capacidade de sobrescrever esses métodos, você tem a liberdade de adaptar o sistema às suas necessidades. Comece pequeno com um único evento e veja como isso transforma a estrutura do seu aplicativo!

## Eventos Embutidos

O Flight PHP vem com alguns eventos embutidos que você pode usar para se conectar ao ciclo de vida do framework. Esses eventos são acionados em pontos específicos do ciclo de requisição/resposta, permitindo que você execute lógica personalizada quando certas ações ocorrerem.

### Lista de Eventos Embutidos
- **flight.request.received**: `function(Request $request)` Acionado quando uma requisição é recebida, analisada e processada.
- **flight.error**: `function(Throwable $exception)` Acionado quando um erro ocorre durante o ciclo de requisição.
- **flight.redirect**: `function(string $url, int $status_code)` Acionado quando um redirecionamento é iniciado.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Acionado quando o cache é verificado para uma chave específica e se houve acerto ou falha no cache.
- **flight.middleware.before**: `function(Route $route)` Acionado após a execução do middleware de antes.
- **flight.middleware.after**: `function(Route $route)` Acionado após a execução do middleware de depois.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Acionado após qualquer middleware ser executado.
- **flight.route.matched**: `function(Route $route)` Acionado quando uma rota é correspondente, mas ainda não foi executada.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Acionado após uma rota ser executada e processada. `$executionTime` é o tempo que levou para executar a rota (chamar o controlador, etc).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Acionado após uma visualização ser renderizada. `$executionTime` é o tempo que levou para renderizar o template. **Nota: Se você sobrescrever o método `render`, precisará reverter este evento.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Acionado após uma resposta ser enviada ao cliente. `$executionTime` é o tempo que levou para construir a resposta.