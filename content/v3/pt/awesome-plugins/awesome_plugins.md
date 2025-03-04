# Plugins Incríveis

Flight é incrivelmente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidade à sua aplicação Flight. Alguns são oficialmente suportados pela Equipe Flight e outros são bibliotecas micro/lite para ajudá-lo a começar.

## Documentação da API

A documentação da API é crucial para qualquer API. Ela ajuda os desenvolvedores a entender como interagir com sua API e o que esperar em troca. Existem algumas ferramentas disponíveis para ajudá-lo a gerar documentação da API para seus Projetos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Post no blog escrito por Daniel Schreiber sobre como usar a Especificação OpenAPI com FlightPHP para construir sua API usando uma abordagem de API primeiro.
- [SwaggerUI](https://github.com/zircote/swagger-php) - O Swagger UI é uma ótima ferramenta para ajudá-lo a gerar documentação da API para seus projetos Flight. É muito fácil de usar e pode ser personalizado para atender às suas necessidades. Esta é a biblioteca PHP para ajudá-lo a gerar a documentação Swagger.

## Autenticação/Autorização

A autenticação e a autorização são cruciais para qualquer aplicação que requer controles sobre quem pode acessar o que. 

- [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de Permissões do Flight. Esta biblioteca é uma maneira simples de adicionar permissões em nível de usuário e aplicativo à sua aplicação.

## Cache

O cache é uma ótima maneira de acelerar sua aplicação. Existem várias bibliotecas de cache que podem ser usadas com Flight.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Classe de cache em arquivo PHP leve, simples e independente.

## CLI

Aplicações CLI são uma ótima maneira de interagir com sua aplicação. Você pode usá-las para gerar controladores, exibir todas as rotas e mais.

- [flightphp/runway](/awesome-plugins/runway) - Runway é uma aplicação CLI que ajuda você a gerenciar suas aplicações Flight.

## Cookies

Os cookies são uma ótima maneira de armazenar pequenos trechos de dados do lado do cliente. Eles podem ser usados para armazenar preferências do usuário, configurações da aplicação e mais.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie é uma biblioteca PHP que fornece uma maneira simples e eficaz de gerenciar cookies.

## Depuração

A depuração é crucial quando você está desenvolvendo em seu ambiente local. Existem alguns plugins que podem elevar sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com o Flight. Ele possui vários painéis que podem ajudá-lo a depurar sua aplicação. Também é muito fácil de estender e adicionar seus próprios painéis.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o manipulador de erros [Tracy](/awesome-plugins/tracy), este plugin adiciona alguns painéis extras para auxiliar na depuração especificamente para projetos Flight.

## Bancos de Dados

Bancos de dados são o núcleo da maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são apenas wrappers para escrever consultas e outras são ORMs completas.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO oficial do Flight que faz parte do núcleo. Este é um wrapper simples para ajudar a simplificar o processo de escrita de consultas e executá-las. Não é um ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial do Flight. Ótima pequena biblioteca para recuperar e armazenar dados facilmente em seu banco de dados.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para acompanhar todas as mudanças no banco de dados do seu projeto.

## Criptografia

A criptografia é crucial para qualquer aplicativo que armazena dados sensíveis. Criptografar e descriptografar os dados não é muito difícil, mas armazenar adequadamente a chave de criptografia [pode](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). A coisa mais importante é nunca armazenar sua chave de criptografia em um diretório público ou comitá-la em seu repositório de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a usar é bastante simples para começar a criptografar e descriptografar dados.

## Fila de Trabalho

Filas de trabalho são muito úteis para processar tarefas de forma assíncrona. Isso pode incluir o envio de e-mails, processamento de imagens ou qualquer coisa que não precise ser feita em tempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - A Filas de trabalho simples é uma biblioteca que pode ser usada para processar trabalhos assíncronamente. Pode ser usada com beanstalkd, MySQL/MariaDB, SQLite e PostgreSQL.

## Sessão

As sessões não são realmente úteis para APIs, mas para construir uma aplicação web, as sessões podem ser cruciais para manter o estado e as informações de login.

- [Ghostff/Session](/awesome-plugins/session) - Gerenciador de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa PHP open_ssl para criptografia/descriptografia opcional dos dados da sessão.

## Modelagem

A modelagem é fundamental para qualquer aplicação web com uma interface de usuário. Existem vários mecanismos de modelagem que podem ser usados com o Flight.

- [flightphp/core View](/learn#views) - Este é um mecanismo de modelagem muito básico que faz parte do núcleo. Não é recomendado ser usado se você tiver mais de algumas páginas em seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é um mecanismo de modelagem completo que é muito fácil de usar e parece mais próximo da sintaxe PHP do que Twig ou Smarty. Também é muito fácil estender e adicionar seus próprios filtros e funções.

## Contribuindo

Tem um plugin que gostaria de compartilhar? Envie um pull request para adicioná-lo à lista!