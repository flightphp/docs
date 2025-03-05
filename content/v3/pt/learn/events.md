# Sistema de Eventos no Flight PHP (v3.15.0+)

O Flight PHP introduz um sistema de eventos leve e intuitivo que permite registrar e acionar eventos personalizados em sua aplicação. Com a adição de `Flight::onEvent()` e `Flight::triggerEvent()`, agora você pode se conectar a momentos-chave do ciclo de vida do seu aplicativo ou definir seus próprios eventos para tornar seu código mais modular e extensível. Estes métodos fazem parte dos **métodos mapeáveis** do Flight, o que significa que você pode substituir seu comportamento para atender às suas necessidades.

Este guia cobre tudo que você precisa saber para começar com eventos, incluindo por que eles são valiosos, como usá-los e exemplos práticos para ajudar iniciantes a entender seu poder.

## Por Que Usar Eventos?

Eventos permitem que você separe diferentes partes da sua aplicação para que não dependam muito umas das outras. Essa separação—frequentemente chamada de **desacoplamento**—torna seu código mais fácil de atualizar, estender ou depurar. Em vez de escrever tudo em um único bloco grande, você pode dividir sua lógica em partes menores e independentes que respondem a ações específicas (eventos).

Imagine que você está construindo um aplicativo de blog:
- Quando um usuário posta um comentário, você pode querer:
  - Salvar o comentário no banco de dados.
  - Enviar um e-mail para o proprietário do blog.
  - Registrar a ação para segurança.

Sem eventos, você cramaria tudo em uma única função. Com eventos, você pode dividi-lo: uma parte salva o comentário, outra aciona um evento como `'comment.posted'`, e ouvintes separados lidam com o e-mail e o registro. Isso mantém seu código mais claro e lhe permite adicionar ou remover funcionalidades (como notificações) sem tocar na lógica central.

### Usos Comuns
- **Registro**: Registrar ações como logins ou erros sem sobrecarregar seu código principal.
- **Notificações**: Enviar e-mails ou alertas quando algo acontece.
- **Atualizações**: Atualizar caches ou notificar outros sistemas sobre mudanças.

## Registrando Ouvintes de Eventos

Para ouvir um evento, use `Flight::onEvent()`. Este método permite que você defina o que deve acontecer quando um evento ocorrer.

### Sintaxe
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Um nome para seu evento (por exemplo, `'user.login'`).
- `$callback`: A função a ser executada quando o evento for acionado.

### Como Funciona
Você "se inscreve" em um evento dizendo ao Flight o que fazer quando ele acontece. O callback pode aceitar argumentos passados do acionador do evento.

O sistema de eventos do Flight é síncrono, o que significa que cada ouvinte de evento é executado em sequência, um após o outro. Quando você aciona um evento, todos os ouvintes registrados para esse evento serão executados até a conclusão antes que seu código continue. Isso é importante de entender, pois difere de sistemas de eventos assíncronos onde os ouvintes podem ser executados em paralelo ou em um momento posterior.

### Exemplo Simples
```php
Flight::onEvent('user.login', function ($username) {
    echo "Bem-vindo de volta, $username!";
});
```
Aqui, quando o evento `'user.login'` é acionado, ele cumprimenta o usuário pelo nome.

### Pontos Chave
- Você pode adicionar múltiplos ouvintes ao mesmo evento—eles serão executados na ordem em que foram registrados.
- O callback pode ser uma função, uma função anônima ou um método de uma classe.

## Acionando Eventos

Para fazer um evento acontecer, use `Flight::triggerEvent()`. Isso diz ao Flight para executar todos os ouvintes registrados para esse evento, passando qualquer dado que você fornecer.

### Sintaxe
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: O nome do evento que você está acionando (deve corresponder a um evento registrado).
- `...$args`: Argumentos opcionais a serem enviados aos ouvintes (podem ser qualquer número de argumentos).

### Exemplo Simples
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Isso aciona o evento `'user.login'` e envia `'alice'` para o ouvinte que definimos anteriormente, que irá gerar: `Bem-vindo de volta, alice!`.

### Pontos Chave
- Se nenhum ouvinte estiver registrado, nada acontecerá—seu aplicativo não quebrará.
- Use o operador de spread (`...`) para passar múltiplos argumentos de forma flexível.

### Registrando Ouvintes de Eventos

...

**Interrompendo Ouvintes Futuros**:
Se um ouvinte retornar `false`, nenhum ouvinte adicional para esse evento será executado. Isso permite que você interrompa a cadeia de eventos com base em condições específicas. Lembre-se, a ordem dos ouvintes importa, pois o primeiro a retornar `false` impedirá o restante de serem executados.

**Exemplo**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Interrompe ouvintes subsequentes
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // isso nunca é enviado
});
```

## Substituindo Métodos de Evento

`Flight::onEvent()` e `Flight::triggerEvent()` estão disponíveis para serem [estendidos](/learn/extending), o que significa que você pode redefinir como eles funcionam. Isso é ótimo para usuários avançados que desejam personalizar o sistema de eventos, como adicionar registro ou alterar como os eventos são enviados.

### Exemplo: Personalizando `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Registre cada registro de evento
    error_log("Novo ouvinte de evento adicionado para: $event");
    // Chame o comportamento padrão (supondo um sistema de eventos interno)
    Flight::_onEvent($event, $callback);
});
```
Agora, toda vez que você registra um evento, ele é registrado antes de prosseguir.

### Por Que Substituir?
- Adicionar depuração ou monitoramento.
- Restringir eventos em determinados ambientes (por exemplo, desativar em testes).
- Integrar-se com uma biblioteca de eventos diferente.

## Onde Colocar Seus Eventos

Como iniciante, você pode se perguntar: *onde registro todos esses eventos na minha aplicação?* A simplicidade do Flight significa que não há uma regra rígida—você pode colocá-los onde fizer sentido para seu projeto. No entanto, mantê-los organizados ajuda você a manter seu código à medida que seu aplicativo cresce. Aqui estão algumas opções práticas e melhores práticas, adaptadas à natureza leve do Flight:

### Opção 1: No Seu `index.php` Principal
Para aplicativos pequenos ou protótipos rápidos, você pode registrar eventos diretamente em seu arquivo `index.php` juntamente com suas rotas. Isso mantém tudo em um só lugar, o que é aceitável quando a simplicidade é sua prioridade.

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
    echo "Conectado!";
});

Flight::start();
```
- **Prós**: Simples, sem arquivos extras, ótimo para pequenos projetos.
- **Contras**: Pode ficar bagunçado à medida que seu aplicativo cresce com mais eventos e rotas.

### Opção 2: Um Arquivo `events.php` Separado
Para um aplicativo um pouco maior, considere mover os registros de eventos para um arquivo dedicado como `app/config/events.php`. Inclua este arquivo em seu `index.php` antes de suas rotas. Isso imita como as rotas costumam ser organizadas em `app/config/routes.php` nos projetos do Flight.

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
    echo "Conectado!";
});

Flight::start();
```
- **Prós**: Mantém `index.php` focado em rotas, organiza eventos logicamente, fácil de encontrar e editar.
- **Contras**: Adiciona um pequeno pouco de estrutura, o que pode parecer excessivo para aplicativos muito pequenos.

### Opção 3: Perto de Onde Eles São Acionados
Outra abordagem é registrar eventos perto de onde eles são acionados, como dentro de um controlador ou definição de rota. Isso funciona bem se um evento é específico de uma parte do seu aplicativo.

```php
Flight::route('/signup', function () {
    // Registrar evento aqui
    Flight::onEvent('user.registered', function ($email) {
        echo "E-mail de boas-vindas enviado para $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Registrado!";
});
```
- **Prós**: Mantém o código relacionado junto, bom para recursos isolados.
- **Contras**: Espalha registros de eventos, dificultando a visualização de todos os eventos de uma vez; riscos de registros duplicados se não tomar cuidado.

### Melhor Prática para Flight
- **Começar Simples**: Para aplicativos pequenos, coloque eventos em `index.php`. É rápido e alinha-se com o minimalismo do Flight.
- **Crescer de Forma Inteligente**: À medida que seu aplicativo se expande (por exemplo, mais de 5-10 eventos), use um arquivo `app/config/events.php`. É um passo natural, como organizar rotas, e mantém seu código limpo sem adicionar estruturas complexas.
- **Evitar Sobrecarga de Engenharia**: Não crie uma classe ou diretório de "gerenciador de eventos" completo a menos que seu aplicativo cresça muito—o Flight prospera na simplicidade, então mantenha-o leve.

### Dica: Agrupar por Propósito
No `events.php`, agrupe eventos relacionados (por exemplo, todos os eventos relacionados ao usuário juntos) com comentários para clareza:

```php
// app/config/events.php
// Eventos do Usuário
Flight::onEvent('user.login', function ($username) {
    error_log("$username fez login");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Bem-vindo ao $email!";
});

// Eventos de Página
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Essa estrutura escala bem e continua amigável para iniciantes.

## Exemplos para Iniciantes

Vamos percorrer alguns cenários do mundo real para mostrar como os eventos funcionam e por que eles são úteis.

### Exemplo 1: Registrando um Login de Usuário
```php
// Passo 1: Registrar um ouvinte
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username fez login em $time");
});

// Passo 2: Acionar no seu aplicativo
Flight::route('/login', function () {
    $username = 'bob'; // Suponha que isso venha de um formulário
    Flight::triggerEvent('user.login', $username);
    echo "Oi, $username!";
});
```
**Por que isso é útil**: O código de login não precisa saber sobre logging—ele apenas aciona o evento. Você pode adicionar mais ouvintes depois (por exemplo, enviar um e-mail de boas-vindas) sem alterar a rota.

### Exemplo 2: Notificando Sobre Novos Usuários
```php
// Ouvinte para novos registros
Flight::onEvent('user.registered', function ($email, $name) {
    // Simule o envio de um e-mail
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
**Por que isso é útil**: A lógica de inscrição foca na criação do usuário, enquanto o evento lida com as notificações. Você poderia adicionar mais ouvintes depois (por exemplo, registrar a inscrição).

### Exemplo 3: Limpando um Cache
```php
// Ouvinte para limpar um cache
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Limpe o cache da sessão, se aplicável
    echo "Cache limpo para a página $pageId.";
});

// Acionar quando uma página é editada
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Suponha que atualizamos a página
    Flight::triggerEvent('page.updated', $pageId);
    echo "Página $pageId atualizada.";
});
```
**Por que isso é útil**: O código de edição não se preocupa com o cache—ele apenas sinaliza a atualização. Outras partes do aplicativo podem reagir conforme necessário.

## Melhores Práticas

- **Nomeie Eventos Claramente**: Use nomes específicos como `'user.login'` ou `'page.updated'` para que fique óbvio o que eles fazem.
- **Mantenha Ouvintes Simples**: Não coloque tarefas lentas ou complexas em ouvintes—mantenha seu aplicativo rápido.
- **Teste Seus Eventos**: Acione-os manualmente para garantir que os ouvintes funcionam como esperado.
- **Use Eventos Com Sabedoria**: Eles são ótimos para desacoplamento, mas muitos podem tornar seu código difícil de seguir—use-os quando fizer sentido.

O sistema de eventos no Flight PHP, com `Flight::onEvent()` e `Flight::triggerEvent()`, oferece uma maneira simples e poderosa de construir aplicações flexíveis. Ao permitir que diferentes partes do seu aplicativo se comuniquem entre si por meio de eventos, você pode manter seu código organizado, reutilizável e fácil de expandir. Seja registrando ações, enviando notificações ou gerenciando atualizações, os eventos ajudam você a fazer isso sem emaranhar sua lógica. Além disso, com a capacidade de substituir esses métodos, você tem a liberdade de personalizar o sistema de acordo com suas necessidades. Comece pequeno com um único evento e veja como ele transforma a estrutura do seu aplicativo!

## Eventos Integrados

O Flight PHP vem com alguns eventos integrados que você pode usar para se conectar ao ciclo de vida do framework. Esses eventos são acionados em pontos específicos do ciclo de solicitação/resposta, permitindo que você execute lógica personalizada quando certas ações ocorrem.

### Lista de Eventos Integrados
- `flight.request.received`: Acionado quando uma solicitação é recebida, analisada e processada.
- `flight.route.middleware.before`: Acionado após a execução do middleware anterior.
- `flight.route.middleware.after`: Acionado após a execução do middleware posterior.
- `flight.route.executed`: Acionado após uma rota ser executada e processada.
- `flight.response.sent`: Acionado após uma resposta ser enviada ao cliente.