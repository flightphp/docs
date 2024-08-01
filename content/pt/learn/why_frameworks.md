# Por que um Framework?

Alguns programadores são veementemente contra o uso de frameworks. Eles argumentam que os frameworks são inflados, lentos e difíceis de aprender. Dizem que os frameworks são desnecessários e que você pode escrever um código melhor sem eles. Certamente, há alguns pontos válidos a serem considerados sobre as desvantagens do uso de frameworks. No entanto, também existem muitas vantagens em usar frameworks.

## Motivos para Usar um Framework

Aqui estão algumas razões pelas quais você pode querer considerar o uso de um framework:

- **Desenvolvimento Rápido**: Os frameworks fornecem muita funcionalidade pronta para uso. Isso significa que você pode construir aplicações web mais rapidamente. Você não precisa escrever tanto código, pois o framework fornece grande parte da funcionalidade necessária.
- **Consistência**: Os frameworks fornecem uma forma consistente de fazer as coisas. Isso facilita a compreensão de como o código funciona e torna mais fácil para outros desenvolvedores entenderem o seu código. Se você o tem script por script, pode perder a consistência entre scripts, especialmente se estiver trabalhando com uma equipe de desenvolvedores.
- **Segurança**: Os frameworks oferecem recursos de segurança que ajudam a proteger suas aplicações web contra ameaças de segurança comuns. Isso significa que você não precisa se preocupar tanto com a segurança, pois o framework cuida de grande parte disso para você.
- **Comunidade**: Os frameworks têm grandes comunidades de desenvolvedores que contribuem para o framework. Isso significa que você pode obter ajuda de outros desenvolvedores quando tiver perguntas ou problemas. Também significa que há muitos recursos disponíveis para ajudá-lo a aprender como usar o framework.
- **Melhores Práticas**: Os frameworks são construídos seguindo as melhores práticas. Isso significa que você pode aprender com o framework e usar as mesmas melhores práticas em seu próprio código. Isso pode ajudá-lo a se tornar um melhor programador. Às vezes, você não sabe o que não sabe e isso pode prejudicá-lo no final.
- **Extensibilidade**: Os frameworks são projetados para serem estendidos. Isso significa que você pode adicionar sua própria funcionalidade ao framework. Isso permite que você construa aplicações web adaptadas às suas necessidades específicas.

Flight é um micro-framework. Isso significa que ele é pequeno e leve. Ele não fornece tanta funcionalidade quanto frameworks maiores como Laravel ou Symfony. No entanto, ele fornece muita da funcionalidade necessária para construir aplicações web. É também fácil de aprender e usar. Isso o torna uma boa escolha para construir aplicações web rapidamente e facilmente. Se você é novo em frameworks, o Flight é um ótimo framework para iniciantes a começar. Vai ajudá-lo a entender as vantagens de usar frameworks sem sobrecarregá-lo com muita complexidade. Depois de adquirir alguma experiência com o Flight, será mais fácil passar para frameworks mais complexos como Laravel ou Symfony,    no entanto, o Flight ainda pode criar um aplicativo robusto e bem-sucedido.

## O Que é Roteamento?

O roteamento é o núcleo do framework Flight, mas o que é exatamente? Roteamento é o processo de pegar uma URL e correspondê-la a uma função específica em seu código. É assim que você pode fazer seu site fazer coisas diferentes com base na URL solicitada. Por exemplo, você pode querer mostrar o perfil de um usuário quando ele visita `/user/1234`, mas mostrar uma lista de todos os usuários quando eles visitam `/users`. Tudo isso é feito por meio do roteamento.

Pode funcionar algo assim:

- Um usuário vai para o seu navegador e digita `http://exemplo.com/user/1234`.
- O servidor recebe a solicitação, olha para a URL e a passa para o seu código de aplicativo Flight.
- Digamos que em seu código Flight você tenha algo como `Flight::route('/user/@id', [ 'ControladorDeUsuario', 'verPerfilDoUsuario' ]);`. Seu código de aplicativo Flight olha a URL e percebe que corresponde a uma rota que você definiu, e então executa o código que você definiu para essa rota.
- O roteador do Flight vai então chamar o método `verPerfilDoUsuario($id)` na classe `ControladorDeUsuario`, passando o `1234` como argumento `$id` no método.
- O código em seu método `verPerfilDoUsuario()` então vai rodar e fazer o que você mandou fazer. Você pode acabar ecoando um pouco de HTML para a página do perfil do usuário, ou se isso for uma API RESTful, você pode ecoar uma resposta JSON com a informação do usuário.
- O Flight embrulha isso em um laço bonito, gera os cabeçalhos de resposta e envia de volta para o navegador do usuário.
- O usuário fica cheio de alegria e dá a si mesmo um caloroso abraço!

### E Por que é Importante?

Ter um roteador centralizado apropriado pode realmente tornar sua vida dramaticamente mais fácil! Pode ser difícil ver isso à primeira vista. Aqui estão algumas razões:

- **Roteamento Centralizado**: Você pode manter todas as suas rotas em um só lugar. Isso torna mais fácil ver quais rotas você tem e o que elas fazem. Também facilita alterá-las, se necessário.
- **Parâmetros de Rota**: Você pode usar parâmetros de rota para passar dados para seus métodos de rota. Esta é uma ótima maneira de manter seu código limpo e organizado.
- **Grupos de Rotas**: Você pode agrupar rotas juntas. Isso é ótimo para manter seu código organizado e para aplicar [middleware](middleware) a um grupo de rotas.
- **Alias de Rota**: Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente mais tarde em seu código (como um modelo, por exemplo). Ex: em vez de codificar `/user/1234` no seu código, você poderia referenciar o alias `user_view` e passar o `id` como parâmetro. Isso é maravilhoso no caso de decidir alterá-lo para `/admin/user/1234` posteriormente. Você não precisará mudar todas as suas URLs codificadas, apenas a URL associada à rota.
- **Middleware de Rota**: Você pode adicionar middleware às suas rotas. O middleware é incrivelmente poderoso para adicionar comportamentos específicos à sua aplicação, como autenticar que um determinado usuário pode acessar uma rota ou grupo de rotas.

Tenho certeza de que você está familiarizado com a maneira script por script de criar um site. Você pode ter um arquivo chamado `index.php` que possui um monte de declarações `if` para verificar a URL e em seguida executar uma função específica com base na URL. Isso é uma forma de roteamento, mas não é muito organizado e pode sair do controle rapidamente. O sistema de roteamento do Flight é uma maneira muito mais organizada e poderosa de lidar com o roteamento.

Isto?

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
Flight::route('/user/@id', [ 'ControladorDeUsuario', 'verPerfilDoUsuario' ]);
Flight::route('/user/@id/edit', [ 'ControladorDeUsuario', 'editarPerfilDoUsuario' ]);

// Em talvez o seu app/controllers/ControladorDeUsuario.php
class ControladorDeUsuario {
	public function verPerfilDoUsuario($id) {
		// faça algo
	}

	public function editarPerfilDoUsuario($id) {
		// faça algo
	}
}
```

Espero que você comece a ver os benefícios de usar um sistema de roteamento centralizado. É muito mais fácil de gerenciar e entender a longo prazo!

## Solicitações e Respostas

O Flight oferece uma maneira simples e fácil de lidar com solicitações e respostas. Este é o núcleo do que um framework web faz. Ele recebe uma solicitação de um navegador de um usuário, a processa e envia de volta uma resposta. Com isso, você pode construir aplicações web que realizem tarefas como mostrar o perfil de um usuário, permitir que um usuário faça login, ou permitir que um usuário poste uma nova postagem em um blog.

### Solicitações

Uma solicitação é o que o navegador de um usuário envia para o seu servidor quando eles visitam o seu site. Esta solicitação contém informações sobre o que o usuário quer fazer. Por exemplo, ela pode conter informações sobre qual URL o usuário deseja visitar, quais dados o usuário deseja enviar para o seu servidor, ou que tipo de dados o usuário deseja receber do seu servidor. É importante saber que uma solicitação é somente leitura. Você não pode alterar a solicitação, mas pode lê-la.

O Flight fornece uma maneira simples de acessar informações sobre a solicitação. Você pode acessar informações sobre a solicitação usando o método `Flight::request()`. Este método retorna um objeto `Request` que contém informações sobre a solicitação. Você pode usar esse objeto para acessar informações sobre a solicitação, como a URL, o método, ou os dados que o usuário enviou para o seu servidor.

### Respostas

Uma resposta é o que o seu servidor envia de volta para o navegador de um usuário quando eles visitam o seu site. Esta resposta contém informações sobre o que o seu servidor quer fazer. Por exemplo, ela pode conter informações sobre que tipo de dados o seu servidor quer enviar para o usuário, que tipo de dados seu servidor quer receber do usuário, ou que tipo de dados seu servidor quer armazenar no computador do usuário.

O Flight fornece uma maneira simples de enviar uma resposta para o navegador do usuário. Você pode enviar uma resposta usando o método `Flight::response()`. Este método recebe um objeto `Response` como argumento e envia a resposta para o navegador do usuário. Você pode usar este objeto para enviar uma resposta para o navegador do usuário, como HTML, JSON, ou um arquivo. O Flight ajuda a gerar automaticamente algumas partes da resposta para facilitar as coisas, mas, em última instância, você tem controle sobre o que enviar de volta para o usuário.