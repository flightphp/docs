# Incríveis Plugins

Flight é incrivelmente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidades à sua aplicação do Flight. Alguns são oficialmente suportados pela Equipe do Flight e outros são bibliotecas micro/lite para ajudá-lo a começar.

## Cache

O cache é uma ótima maneira de acelerar sua aplicação. Existem várias bibliotecas de cache que podem ser usadas com o Flight.

- [Wruczek/PHP-File-Cache](/pt/awesome-plugins/php-file-cache) - Classe de cache PHP leve, simples e autônoma em arquivo

## CLI

Aplicações CLI são uma ótima maneira de interagir com sua aplicação. Você pode usá-las para gerar controladores, exibir todas as rotas e muito mais.

- [flightphp/runway](/pt/awesome-plugins/runway) - Runway é uma aplicação CLI que ajuda a gerenciar suas aplicações do Flight.

## Cookies

Cookies são uma ótima maneira de armazenar pequenos pedaços de dados no lado do cliente. Eles podem ser usados para armazenar preferências do usuário, configurações da aplicação e muito mais.

- [overclokk/cookie](/pt/awesome-plugins/php-cookie) - O PHP Cookie é uma biblioteca PHP que fornece uma maneira simples e eficaz de gerenciar cookies.

## Depuração

A depuração é crucial ao desenvolver em seu ambiente local. Existem alguns plugins que podem aprimorar sua experiência de depuração.

- [tracy/tracy](/pt/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com o Flight. Possui várias abas que podem ajudá-lo a depurar sua aplicação. Também é muito fácil de estender e adicionar suas próprias abas.
- [flightphp/tracy-extensions](/pt/awesome-plugins/tracy-extensions) - Usado com o manipulador de erros [Tracy](/pt/awesome-plugins/tracy), este plugin adiciona algumas abas extras para ajudar na depuração especificamente para projetos do Flight.

## Bancos de Dados

Bancos de dados são essenciais para a maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente wrappers para escrever consultas e outras são ORMs completos.

- [flightphp/core PdoWrapper](/pt/awesome-plugins/pdo-wrapper) - Wrapper PDO oficial do Flight que faz parte do núcleo. Este é um wrapper simples para ajudar a simplificar o processo de escrever consultas e executá-las. Não é um ORM.
- [flightphp/active-record](/pt/awesome-plugins/active-record) - ORM/Mapper Active Record oficial do Flight. Ótima biblioteca para recuperar e armazenar dados em seu banco de dados de forma fácil.

## Criptografia

A criptografia é crucial para qualquer aplicação que armazena dados sensíveis. Criptografar e descriptografar os dados não é tão difícil, mas armazenar corretamente a chave de criptografia pode ser difícil. A coisa mais importante é nunca armazenar sua chave de criptografia em um diretório público ou incluí-la em seu repositório de código.

- [defuse/php-encryption](/pt/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a usar é bastante simples para começar a criptografar e descriptografar dados.

## Sessão

As sessões não são realmente úteis para APIs, mas para construir uma aplicação web, as sessões podem ser cruciais para manter o estado e as informações de login.

- [Ghostff/Session](/pt/awesome-plugins/session) - Gerenciador de Sessões PHP (não bloqueador, flash, segmento, criptografia de sessão). Usa o open_ssl do PHP para criptografia/opcional decriptação de dados de sessão.

## Templating

A criação de modelos é fundamental para qualquer aplicação web com uma UI. Existem várias engines de templating que podem ser usadas com o Flight.

- [flightphp/core View](/pt/learn#views) - Esta é uma engine de templating muito básica que faz parte do núcleo. Não é recomendado usá-la se você tiver mais do que algumas páginas em seu projeto.
- [latte/latte](/pt/awesome-plugins/latte) - Latte é uma engine de templating completa e muito fácil de usar, que se aproxima da sintaxe do PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Contribuição

Tem um plugin que gostaria de compartilhar? Envie um pull request para adicioná-lo à lista!