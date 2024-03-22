# Plug-ins Incríveis

O Flight é incrivelmente extensível. Existem vários plug-ins que podem ser usados para adicionar funcionalidades à sua aplicação Flight. Alguns são oficialmente suportados pela Equipe do Flight e outros são bibliotecas micro/lite para ajudá-lo a começar.

## Caching

O armazenamento em cache é uma ótima maneira de acelerar a sua aplicação. Existem várias bibliotecas de cache que podem ser utilizadas com o Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Classe de armazenamento em cache PHP leve, simples e independente de arquivos

## Cookies

Os cookies são uma ótima maneira de armazenar pequenos pedaços de dados no lado do cliente. Eles podem ser usados para armazenar preferências do usuário, configurações da aplicação e muito mais.

- [overclokk/cookie](/awesome-plugins/php-cookie) - O PHP Cookie é uma biblioteca PHP que fornece uma maneira simples e eficaz de gerenciar cookies.

## Debugging

A depuração é crucial quando você está desenvolvendo em seu ambiente local. Existem alguns plug-ins que podem elevar sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com o Flight. Possui vários painéis que podem ajudá-lo a depurar a sua aplicação. Também é muito fácil de estender e adicionar seus próprios painéis.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o manipulador de erros [Tracy](/awesome-plugins/tracy), este plug-in adiciona alguns painéis extras para ajudar na depuração, especificamente para projetos Flight.

## Bancos de Dados

Bancos de dados são essenciais para a maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente envoltórios para escrever consultas e algumas são ORMs completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Envoltório PDO oficial do Flight que faz parte do núcleo. Este é um envoltório simples para ajudar a simplificar o processo de escrever consultas e executá-las. Não é um ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial do Flight. Ótima pequena biblioteca para recuperar e armazenar dados facilmente no seu banco de dados.

## Criptografia

A criptografia é crucial para qualquer aplicação que armazena dados sensíveis. Criptografar e descriptografar os dados não é muito difícil, mas armazenar corretamente a chave de criptografia pode ser difícil. A coisa mais importante é nunca armazenar sua chave de criptografia em um diretório público ou enviá-la para o seu repositório de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a usar é bastante simples para começar a criptografar e descriptografar dados.

## Sessão

As sessões não são realmente úteis para APIs, mas para o desenvolvimento de uma aplicação web, podem ser cruciais para manter o estado e as informações de login.

- [Ghostff/Session](/awesome-plugins/session) - Gerenciador de Sessões em PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa PHP open_ssl para criptografar/descriptografar opcionalmente os dados da sessão.

## Modelagem

A modelagem é fundamental para qualquer aplicação web com uma interface de usuário. Existem várias engines de modelagem que podem ser usadas com o Flight.

- [flightphp/core View](/learn#views) - Esta é uma engine de modelagem muito básica que faz parte do núcleo. Não é recomendado seu uso se você tiver mais do que algumas páginas no seu projeto.
- [latte/latte](/awesome-plugins/latte) - O Latte é uma engine de modelagem completa e muito fácil de usar, que se assemelha mais à sintaxe do PHP do que o Twig ou o Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Contribuindo

Tem um plug-in que gostaria de compartilhar? Envie um pull request para adicioná-lo à lista!