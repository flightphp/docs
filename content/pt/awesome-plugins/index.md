# Plugins Incríveis

Flight é incrivelmente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidades à sua aplicação Flight. Alguns são oficialmente suportados pela Equipe FlightPHP e outros são bibliotecas micro/lite para ajudá-lo a começar.

## Cache

O cache é uma ótima maneira de acelerar sua aplicação. Existem várias bibliotecas de cache que podem ser usadas com o Flight.

- [Wruczek/PHP-File-Cache](/plugins-incriveis/php-file-cache) - Classe de cache leve, simples e independente de arquivos em PHP

## Depuração

A depuração é crucial quando você está desenvolvendo em seu ambiente local. Existem alguns plugins que podem aprimorar sua experiência de depuração.

- [tracy/tracy](/plugins-incriveis/tracy) - Este é um manipulador de erros completo que pode ser usado com o Flight. Possui vários painéis que podem ajudá-lo a depurar sua aplicação. Também é muito fácil de estender e adicionar seus próprios painéis.
- [flightphp/tracy-extensions](/plugins-incriveis/tracy-extensions) - Usado com o manipulador de erros [Tracy](/plugins-incriveis/tracy), este plugin adiciona alguns painéis extras para auxiliar na depuração especificamente para projetos Flight.

## Bancos de Dados

Bancos de dados são o núcleo da maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente wrappers para escrever consultas e outras são ORMs completos.

- [flightphp/core PdoWrapper](/plugins-incriveis/pdo-wrapper) - Wrapper PDO oficial do Flight que faz parte do núcleo. Este é um wrapper simples para ajudar a simplificar o processo de escrever consultas e executá-las. Não é um ORM.
- [flightphp/active-record](/plugins-incriveis/active-record) - ORM/Mapper ActiveRecord oficial do Flight. Ótima pequena biblioteca para recuperar e armazenar facilmente dados em seu banco de dados.

## Sessão

As sessões não são realmente úteis para APIs, mas para construir uma aplicação web, as sessões podem ser cruciais para manter o estado e informações de login.

- [Ghostff/Session](/plugins-incriveis/session) - Gerenciador de Sessões PHP (não bloqueador, flash, segmento, criptografia de sessão). Usa o PHP open_ssl para criptografar/descriptografar opcionalmente os dados da sessão.

## Templating

A criação de modelos é fundamental para qualquer aplicação web com uma interface de usuário. Existem várias engines de modelagem que podem ser usadas com o Flight.

- [flightphp/core View](/aprender#views) - Esta é uma engine de modelagem muito básica que faz parte do núcleo. Não é recomendado para uso se você tiver mais do que algumas páginas em seu projeto.
- [latte/latte](/plugins-incriveis/latte) - Latte é uma engine de modelagem completa que é muito fácil de usar e mais próxima da sintaxe do PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Contribuição

Tem um plugin que gostaria de compartilhar? Envie uma solicitação de pull para adicioná-lo à lista!