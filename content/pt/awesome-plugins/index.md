# Plugins Incríveis

O Flight é incrivelmente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidades à sua aplicação Flight. Alguns são oficialmente suportados pela Equipe FlightPHP e outros são bibliotecas micro/lite para ajudar você a começar.

## Armazenamento em Cache

O armazenamento em cache é uma ótima maneira de acelerar sua aplicação. Existem várias bibliotecas de cache que podem ser usadas com o Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Classe de armazenamento em arquivo PHP leve, simples e independente

## Depuração

A depuração é crucial quando você está desenvolvendo em seu ambiente local. Existem alguns plugins que podem aprimorar sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com o Flight. Possui vários painéis que podem ajudar você a depurar sua aplicação. Também é muito fácil de estender e adicionar seus próprios painéis.
- [flightphp/tracy-extensions](/awesome-plugins/extensoes-tracy) - Usado com o manipulador de erros [Tracy](/awesome-plugins/tracy), este plugin adiciona alguns painéis extras para ajudar na depuração, especificamente para projetos com Flight.

## Bancos de Dados

Bancos de dados são essenciais para a maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente wrappers para escrever consultas e outras são ORMs completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO oficial do Flight que faz parte do núcleo. Este é um wrapper simples para ajudar a simplificar o processo de escrever consultas e executá-las. Não é um ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapeador ActiveRecord oficial do Flight. Ótima biblioteca para recuperar e armazenar dados em seu banco de dados facilmente.

## Sessão

As sessões não são realmente úteis para APIs, mas para desenvolver uma aplicação web, as sessões podem ser cruciais para manter o estado e as informações de login.

- [Ghostff/Session](/awesome-plugins/sessao) - Gerenciador de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa o PHP open_ssl para criptografar/descriptografar dados de sessão de forma opcional.

## Modelagem

A modelagem é essencial para qualquer aplicação web com uma interface de usuário. Existem várias engines de modelagem que podem ser usadas com o Flight.

- [flightphp/core View](/aprender#visualizacoes) - Esta é uma engine de modelagem muito básica que faz parte do núcleo. Não é recomendado usá-la se você tiver mais do que algumas páginas em seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é uma engine de modelagem completa, muito fácil de usar e mais próxima da sintaxe PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Contribuições

Tem um plugin que gostaria de compartilhar? Envie uma pull request para adicioná-lo à lista!