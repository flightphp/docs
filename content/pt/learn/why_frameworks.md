# Por que um Framework?

Alguns programadores são veementemente contra o uso de frameworks. Eles argumentam que os frameworks são inchados, lentos e difíceis de aprender. Eles dizem que os frameworks são desnecessários e que você pode escrever um código melhor sem eles. Certamente há alguns pontos válidos a serem considerados sobre as desvantagens de usar frameworks. No entanto, também existem muitas vantagens em usar frameworks.

## Razões para Usar um Framework

Aqui estão algumas razões pelas quais você pode querer considerar o uso de um framework:

- **Desenvolvimento Rápido**: Os frameworks fornecem muitas funcionalidades prontas para uso. Isso significa que você pode construir aplicações web mais rapidamente. Você não precisa escrever tanto código porque o framework fornece grande parte da funcionalidade de que você precisa.
- **Consistência**: Os frameworks fornecem uma maneira consistente de fazer as coisas. Isso facilita a compreensão de como o código funciona e torna mais fácil para outros desenvolvedores entenderem seu código. Se você tem script por script, você pode perder a consistência entre os scripts, especialmente se estiver trabalhando com uma equipe de desenvolvedores.
- **Segurança**: Os frameworks fornecem recursos de segurança que ajudam a proteger suas aplicações web de ameaças de segurança comuns. Isso significa que você não precisa se preocupar tanto com a segurança, pois o framework cuida de grande parte disso para você.
- **Comunidade**: Os frameworks têm grandes comunidades de desenvolvedores que contribuem para o framework. Isso significa que você pode obter ajuda de outros desenvolvedores quando tiver dúvidas ou problemas. Também significa que há muitos recursos disponíveis para ajudá-lo a aprender como usar o framework.
- **Melhores Práticas**: Os frameworks são construídos usando melhores práticas. Isso significa que você pode aprender com o framework e usar as mesmas melhores práticas em seu próprio código. Isso pode ajudá-lo a se tornar um programador melhor. Às vezes você não sabe o que não sabe e isso pode prejudicá-lo no final.
- **Extensibilidade**: Os frameworks são projetados para serem estendidos. Isso significa que você pode adicionar sua própria funcionalidade ao framework. Isso permite que você construa aplicações web adaptadas às suas necessidades específicas.

Flight é um micro-framework. Isso significa que é pequeno e leve. Não fornece tantas funcionalidades quanto frameworks maiores como Laravel ou Symfony. No entanto, ele fornece grande parte da funcionalidade necessária para construir aplicações web. Também é fácil de aprender e usar. Isso o torna uma boa escolha para construir aplicações web de forma rápida e fácil. Se você é novo em frameworks, o Flight é um ótimo framework para iniciantes para começar. Isso o ajudará a aprender sobre as vantagens de usar frameworks sem sobrecarregar você com muita complexidade. Depois de ter alguma experiência com o Flight, será mais fácil passar para frameworks mais complexos como Laravel ou Symfony, no entanto, o Flight ainda pode criar uma aplicação robusta e bem-sucedida.

## O que é Roteamento?

O roteamento é o núcleo do framework Flight, mas o que é exatamente? O roteamento é o processo de pegar uma URL e combiná-la com uma função específica em seu código. É assim que você pode fazer com que seu site faça coisas diferentes com base na URL solicitada. Por exemplo, você pode querer mostrar o perfil de um usuário quando eles visitam `/user/1234`, mas mostrar uma lista de todos os usuários quando eles visitam `/users`. Tudo isso é feito por meio do roteamento.

Pode funcionar assim:

- Um usuário vai para o seu navegador e digita `http://exemplo.com/user/1234`.
- O servidor recebe a solicitação, olha para a URL e a passa para o seu código de aplicativo Flight.
- Digamos que no seu código Flight você tenha algo assim `Flight::route('/user/@id', [ 'UserController', 'viewUserProfile' ]);`. Seu código Flight olha para a URL e vê que ela corresponde a uma rota que você definiu e então executa o código que você definiu para aquela rota.
- O roteador Flight então executará e chamará o método `viewUserProfile($id)` na classe `UserController`, passando o `1234` como argumento `$id` no método.
- O código no seu método `viewUserProfile()` então será executado e fará o que você mandou fazer. Você pode acabar ecoando algum HTML para a página de perfil do usuário, ou se for uma API RESTful, pode ecoar uma resposta JSON com as informações do usuário.
- Flight embrulha isso em um laço bonito, gera os cabeçalhos da resposta e envia de volta para o navegador do usuário.
- O usuário fica cheio de alegria e se dá um abraço caloroso a si mesmo!

### E Por que é Importante?

Ter um roteador centralizado adequado pode realmente facilitar dramaticamente sua vida! Pode ser difícil ver a princípio. Aqui estão algumas razões pelas quais:

- **Roteamento Centralizado**: Você pode manter todas as suas rotas em um só lugar. Isso facilita ver quais rotas você tem e o que elas fazem. Também facilita alterá-las se necessário.
- **Parâmetros de Rota**: Você pode usar parâmetros de rota para passar dados para seus métodos de rota. Esta é uma ótima maneira de manter seu código limpo e organizado.
- **Grupos de Rotas**: Você pode agrupar rotas juntas. Isso é ótimo para manter seu código organizado e para aplicar [middleware](middleware) a um grupo de rotas.
- **Alias de Rota**: Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente mais tarde em seu código (como um modelo, por exemplo). Ex: em vez de codificar `/user/1234` no seu código, você pode fazer referência ao alias `user_view` e passar o `id` como parâmetro. Isso é maravilhoso no caso de decidir mudá-lo para `/admin/user/1234` mais tarde. Você não precisa alterar todas as suas URLs codificadas, apenas a URL associada à rota.
- **Middleware de Rota**: Você pode adicionar middleware às suas rotas. O middleware é incrivelmente poderoso para adicionar comportamentos específicos à sua aplicação, como autenticar que um determinado usuário pode acessar uma rota ou grupo de rotas.

Tenho certeza de que você está familiarizado com a maneria script por script de criar um site. Você pode ter um arquivo chamado `index.php` que possui uma série de declarações `if` para verificar a URL e executar uma função específica com base na URL. Isso é uma forma de roteamento, mas não é muito organizado e pode sair rapidamente do controle. O sistema de roteamento do Flight é uma forma muito mais organizada e poderosa de lidar com o roteamento.

Isso?

```php

// /user/view_profile.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	viewUserProfile($id);
}

// /user/edit_profile.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	editUserProfile($id);
}

// etc...
```

Ou isso?

```php

// index.php
Flight::route('/user/@id', [ 'UserController', 'viewUserProfile' ]);
Flight::route('/user/@id/edit', [ 'UserController', 'editUserProfile' ]);

// Em talvez seu app/controllers/UserController.php
class UserController {
	public function viewUserProfile($id) {
		// faça algo
	}

	public function editUserProfile($id) {
		// faça algo
	}
}
```

Espero que você possa começar a ver os benefícios de usar um sistema de roteamento centralizado. É muito mais fácil de gerenciar e entender a longo prazo!

## Solicitações e Respostas

O Flight fornece uma maneira simples e fácil de lidar com solicitações e respostas. Isso é o núcleo do que um framework web faz. Ele recebe uma solicitação do navegador do usuário, a processa e depois envia uma resposta de volta. É assim que você pode construir aplicações web que fazem coisas como mostrar o perfil de um usuário, permitir que um usuário faça login ou permitir que um usuário publique uma nova postagem em um blog.

### Solicitações

Uma solicitação é o que o navegador do usuário envia para o seu servidor quando ele visita seu site. Esta solicitação contém informações sobre o que o usuário deseja fazer. Por exemplo, pode conter informações sobre qual URL o usuário deseja visitar, quais dados o usuário deseja enviar para seu servidor ou que tipo de dados o usuário deseja receber do seu servidor. É importante saber que uma solicitação é somente leitura. Você não pode alterar a solicitação, mas pode lê-la.

O Flight fornece uma maneira simples de acessar informações sobre a solicitação. Você pode acessar informações sobre a solicitação usando o método `Flight::request()`. Este método retorna um objeto `Request` que contém informações sobre a solicitação. Você pode usar este objeto para acessar informações sobre a solicitação, como a URL, o método ou os dados que o usuário enviou para o seu servidor.

### Respostas

Uma resposta é o que seu servidor envia de volta para o navegador do usuário quando ele visita seu site. Esta resposta contém informações sobre o que seu servidor deseja fazer. Por exemplo, pode conter informações sobre que tipo de dados seu servidor deseja enviar para o usuário, que tipo de dados seu servidor deseja receber do usuário ou que tipo de dados seu servidor deseja armazenar no computador do usuário.

O Flight fornece uma maneira simples de enviar uma resposta ao navegador do usuário. Você pode enviar uma resposta usando o método `Flight::response()`. Este método recebe um objeto `Response` como argumento e envia a resposta ao navegador do usuário. Você pode usar este objeto para enviar uma resposta ao navegador do usuário, como HTML, JSON ou um arquivo. O Flight ajuda a gerar automaticamente algumas partes da resposta para facilitar as coisas, mas você tem controle sobre o que envia de volta para o usuário.