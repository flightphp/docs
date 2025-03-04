# Plugins Incríveis

O Flight é incrivelmente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidades à sua aplicação Flight. Alguns são oficialmente suportados pela Equipe do Flight e outros são bibliotecas micro/lite para ajudá-lo a começar.

## Caching

O armazenamento em cache é uma ótima maneira de acelerar a sua aplicação. Existem diversas bibliotecas de cache que podem ser usadas com o Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Classe de armazenamento em arquivo PHP leve, simples e independente.

## Debugging

Depurar é crucial quando você está desenvolvendo no seu ambiente local. Existem alguns plugins que podem aprimorar a sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com o Flight. Possui várias painéis que podem ajudá-lo a depurar a sua aplicação. Também é muito fácil de estender e adicionar os seus próprios painéis.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o manipulador de erros [Tracy](/awesome-plugins/tracy), este plugin adiciona alguns painéis extras para ajudar na depuração especificamente em projetos do Flight.

## Bancos de Dados

Os bancos de dados são a base da maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente wrappers para escrever consultas e outras são ORMs completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO Oficial do Flight que faz parte do core. Este é um wrapper simples para ajudar a simplificar o processo de escrever consultas e executá-las. Não é um ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper Active Record oficial do Flight. Ótima pequena biblioteca para recuperar e armazenar dados facilmente no seu banco de dados.

## Sessão

As sessões não são realmente úteis para APIs, mas para desenvolver uma aplicação web, as sessões podem ser cruciais para manter o estado e as informações de login.

- [Ghostff/Session](/awesome-plugins/session) - Gerenciador de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa o open_ssl do PHP para criptografar/criptografar opcionalmente os dados da sessão.

## Templating

A criação de modelos é essencial para qualquer aplicação web com uma interface de usuário. Existem várias engines de templates que podem ser usadas com o Flight.

- [flightphp/core View](/learn#views) - Esta é uma engine de templates muito básica que faz parte do core. Não é recomendado para uso se você tiver mais do que algumas páginas no seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é uma engine de templates completa que é muito fácil de usar e se aproxima mais de uma sintaxe PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar os seus próprios filtros e funções.

## Contribuindo

Tem um plugin que gostaria de compartilhar? Envie uma solicitação pull para adicioná-lo à lista!