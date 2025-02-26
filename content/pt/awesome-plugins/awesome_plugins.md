# Plugins Incríveis

Flight é incrivelmente extensível. Há uma série de plugins que podem ser usados para adicionar funcionalidade à sua aplicação Flight. Alguns são oficialmente suportados pela equipe do Flight e outros são bibliotecas micro/lite para ajudar você a começar.

## Documentação da API

A documentação da API é crucial para qualquer API. Ela ajuda os desenvolvedores a entenderem como interagir com sua API e o que esperar em retorno. Existem algumas ferramentas disponíveis para ajudar você a gerar documentação da API para seus projetos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Postagem de blog escrita por Daniel Schreiber sobre como usar o OpenAPI Generator com FlightPHP para gerar documentação da API.
- [Swagger UI](https://github.com/zircote/swagger-php) - Swagger UI é uma ótima ferramenta para ajudar você a gerar documentação da API para seus projetos Flight. É muito fácil de usar e pode ser personalizado para atender às suas necessidades. Esta é a biblioteca PHP para ajudar você a gerar a documentação Swagger.

## Autenticação/Autorização

A autenticação e a autorização são cruciais para qualquer aplicação que exija controles sobre quem pode acessar o quê.

- [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de Permissões do Flight. Esta biblioteca é uma maneira simples de adicionar permissões ao nível de usuário e aplicação à sua aplicação.

## Cache

O cache é uma ótima maneira de acelerar sua aplicação. Há uma série de bibliotecas de cache que podem ser usadas com o Flight.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Classe de cache em arquivo PHP leve, simples e autônoma.

## CLI

Aplicações CLI são uma ótima maneira de interagir com sua aplicação. Você pode usá-las para gerar controladores, exibir todas as rotas e mais.

- [flightphp/runway](/awesome-plugins/runway) - Runway é uma aplicação CLI que ajuda você a gerenciar suas aplicações Flight.

## Cookies

Cookies são uma ótima maneira de armazenar pequenos pedaços de dados do lado do cliente. Eles podem ser usados para armazenar preferências do usuário, configurações da aplicação e mais.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie é uma biblioteca PHP que fornece uma maneira simples e eficaz de gerenciar cookies.

## Depuração

A depuração é crucial quando você está desenvolvendo em seu ambiente local. Existem alguns plugins que podem elevar sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com o Flight. Ele possui uma série de painéis que podem ajudar você a depurar sua aplicação. É também muito fácil de estender e adicionar seus próprios painéis.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o manipulador de erros [Tracy](/awesome-plugins/tracy), este plugin adiciona alguns painéis extras para ajudar na depuração especificamente para projetos Flight.

## Bancos de Dados

Bancos de dados são o núcleo da maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são apenas wrappers para escrever consultas e algumas são ORMs completas.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO oficial do Flight que faz parte do núcleo. Este é um wrapper simples para ajudar a simplificar o processo de escrita de consultas e sua execução. Não é um ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapeador ActiveRecord oficial do Flight. Ótima biblioteca para recuperar e armazenar dados facilmente em seu banco de dados.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para rastrear todas as mudanças no banco de dados para seu projeto.

## Criptografia

A criptografia é crucial para qualquer aplicação que armazena dados sensíveis. Criptografar e descriptografar os dados não é muito difícil, mas armazenar corretamente a chave de criptografia [pode](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). A coisa mais importante é nunca armazenar sua chave de criptografia em um diretório público ou comitá-la em seu repositório de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Inicializar é bastante simples para começar a criptografar e descriptografar dados.

## Sessão

As sessões não são realmente úteis para APIs, mas para construir uma aplicação web, as sessões podem ser cruciais para manter o estado e informações de login.

- [Ghostff/Session](/awesome-plugins/session) - Gerenciador de Sessões PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa PHP open_ssl para criptografar/descriptografar opcionalmente os dados da sessão.

## Modelagem

A modelagem é fundamental para qualquer aplicação web com uma interface de usuário. Há uma série de motores de modelagem que podem ser usados com o Flight.

- [flightphp/core View](/learn#views) - Este é um motor de modelagem muito básico que faz parte do núcleo. Não é recomendado o uso se você tiver mais de algumas páginas em seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é um motor de modelagem completo que é muito fácil de usar e se sente mais próximo de uma sintaxe PHP do que Twig ou Smarty. É também muito fácil de estender e adicionar seus próprios filtros e funções.

## Contribuindo

Tem um plugin que você gostaria de compartilhar? Envie uma solicitação de pull para adicioná-lo à lista!