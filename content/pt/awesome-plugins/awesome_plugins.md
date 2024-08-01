# Plugins Incríveis

Flight é incrivelmente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidades à sua aplicação Flight. Alguns são oficialmente suportados pela Equipe do Flight e outros são bibliotecas micro/lite para ajudá-lo a começar.

## Autenticação/Autorização

A autenticação e autorização são cruciais para qualquer aplicação que requer controles para determinar quem pode acessar o quê.

- [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca Oficial de Permissões do Flight. Esta biblioteca é uma maneira simples de adicionar permissões de nível de usuário e aplicativo à sua aplicação.

## Armazenamento em Cache

O armazenamento em cache é uma ótima maneira de acelerar sua aplicação. Existem várias bibliotecas de armazenamento em cache que podem ser usadas com o Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Light, simples e classe de armazenamento em arquivo PHP independente.

## CLI

Aplicações CLI são uma ótima maneira de interagir com sua aplicação. Você pode usá-las para gerar controladores, exibir todas as rotas e muito mais.

- [flightphp/runway](/awesome-plugins/runway) - Runway é uma aplicação CLI que ajuda a gerenciar suas aplicações Flight.

## Cookies

Cookies são uma ótima maneira de armazenar pequenos pedaços de dados no lado do cliente. Eles podem ser usados para armazenar preferências do usuário, configurações da aplicação e muito mais.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie é uma biblioteca PHP que fornece uma maneira simples e eficaz de gerenciar cookies.

## Depuração

A depuração é crucial ao desenvolver em seu ambiente local. Existem alguns plugins que podem aprimorar sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com o Flight. Possui vários painéis que podem ajudá-lo a depurar sua aplicação. Também é muito fácil de estender e adicionar seus próprios painéis.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o manipulador de erros [Tracy](/awesome-plugins/tracy), este plugin adiciona alguns painéis extras para ajudar na depuração especificamente em projetos Flight.

## Bancos de Dados

Bancos de dados são essenciais para a maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente wrappers para escrever consultas e outras são ORMs completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO Oficial do Flight que faz parte do núcleo. É um simples wrapper para ajudar a simplificar o processo de escrever consultas e executá-las. Não é um ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper Ativo do Flight Oficial. Ótima pequena biblioteca para recuperar e armazenar dados facilmente em seu banco de dados.

## Criptografia

Criptografia é crucial para qualquer aplicação que armazena dados sensíveis. Criptografar e descriptografar os dados não é tão difícil, mas armazenar corretamente a chave de criptografia pode ser difícil. A coisa mais importante é nunca armazenar sua chave de criptografia em um diretório público ou commitá-la em seu repositório de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a criptografar e descriptografar dados é bastante simples.

## Sessão

As sessões não são realmente úteis para APIs, mas para o desenvolvimento de aplicações web, as sessões podem ser cruciais para manter o estado e as informações de login.

- [Ghostff/Session](/awesome-plugins/session) - Gerenciador de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa open_ssl do PHP para criptografar/descriptografar opcionalmente os dados da sessão.

## Modelagem

A modelagem é essencial para qualquer aplicação web com uma UI. Existem várias engines de modelagem que podem ser usadas com o Flight.

- [flightphp/core View](/learn#views) - Esta é uma engine de modelagem muito básica que faz parte do núcleo. Não é recomendado usar se você tem mais do que algumas páginas em seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é uma engine de modelagem completa e fácil de usar que se sente mais próxima de uma sintaxe PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Contribuição

Tem um plugin que você gostaria de compartilhar? Envie um pull request para adicioná-lo à lista!