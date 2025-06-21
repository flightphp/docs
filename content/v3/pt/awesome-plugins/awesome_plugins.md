# Plugins Incríveis

Flight é incrivelmente extensível. Há uma série de plugins que podem ser usados para adicionar funcionalidade à sua aplicação Flight. Alguns são oficialmente suportados pela equipa Flight e outros são bibliotecas micro/lite para ajudar a começar.

## Documentação da API

A documentação da API é crucial para qualquer API. Ela ajuda os desenvolvedores a entenderem como interagir com a sua API e o que esperar em retorno. Há algumas ferramentas disponíveis para ajudar a gerar documentação de API para os seus projetos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Postagem no blog escrita por Daniel Schreiber sobre como usar a especificação OpenAPI com FlightPHP para construir a sua API usando uma abordagem API first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI é uma ótima ferramenta para ajudar a gerar documentação de API para os seus projetos Flight. É muito fácil de usar e pode ser personalizado para atender às suas necessidades. Esta é a biblioteca PHP para ajudar a gerar a documentação Swagger.

## Monitoramento de Desempenho de Aplicações (APM)

O monitoramento de desempenho de aplicações (APM) é crucial para qualquer aplicação. Ele ajuda a entender como a sua aplicação está performando e onde estão os gargalos. Há uma série de ferramentas de APM que podem ser usadas com Flight.
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM é uma biblioteca de APM simples que pode ser usada para monitorar as suas aplicações Flight. Pode ser usada para monitorar o desempenho da sua aplicação e ajudar a identificar gargalos.

## Autenticação/Autorização

A autenticação e autorização são cruciais para qualquer aplicação que exija controles para definir quem pode acessar o quê.

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de permissões Flight. Esta biblioteca é uma forma simples de adicionar permissões de nível de usuário e aplicação à sua aplicação.

## Caching

O caching é uma ótima forma de acelerar a sua aplicação. Há uma série de bibliotecas de caching que podem ser usadas com Flight.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Classe leve, simples e independente de caching em arquivo PHP

## CLI

As aplicações CLI são uma ótima forma de interagir com a sua aplicação. Elas podem ser usadas para gerar controladores, exibir todas as rotas e muito mais.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway é uma aplicação CLI que ajuda a gerenciar as suas aplicações Flight.

## Cookies

Os cookies são uma ótima forma de armazenar pequenos pedaços de dados no lado do cliente. Eles podem ser usados para armazenar preferências de usuário, configurações de aplicação e muito mais.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie é uma biblioteca PHP que fornece uma forma simples e eficaz de gerenciar cookies.

## Depuração

A depuração é crucial quando você está desenvolvendo no seu ambiente local. Há alguns plugins que podem elevar a sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Trata-se de um manipulador de erros completo que pode ser usado com Flight. Ele possui uma série de painéis que podem ajudar a depurar a sua aplicação. Também é muito fácil de estender e adicionar os seus próprios painéis.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o [Tracy](/awesome-plugins/tracy) manipulador de erros, este plugin adiciona alguns painéis extras para ajudar na depuração especificamente para projetos Flight.

## Bancos de Dados

Os bancos de dados são o núcleo de muitas aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente wrappers para escrever consultas e outras são ORMs de pleno direito.

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper oficial do PDO Flight que faz parte do core. Trata-se de um wrapper simples para ajudar a simplificar o processo de escrita e execução de consultas. Não é um ORM.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper oficial do ActiveRecord Flight. Ótima pequena biblioteca para recuperar e armazenar dados no seu banco de dados facilmente.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para rastrear todas as alterações no banco de dados para o seu projeto.

## Criptografia

A criptografia é crucial para qualquer aplicação que armazene dados sensíveis. Criptografar e descriptografar os dados não é terrivelmente difícil, mas armazenar adequadamente a chave de criptografia [can](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [be](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficult](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). O mais importante é nunca armazenar a sua chave de criptografia em um diretório público ou cometê-la ao seu repositório de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a usar é bastante simples para começar a criptografar e descriptografar dados.

## Fila de Tarefas

As filas de tarefas são realmente úteis para processar tarefas de forma assíncrona. Isso pode ser enviado de e-mails, processamento de imagens ou qualquer coisa que não precise ser feita em tempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue é uma biblioteca que pode ser usada para processar tarefas de forma assíncrona. Pode ser usada com beanstalkd, MySQL/MariaDB, SQLite e PostgreSQL.

## Sessão

As sessões não são realmente úteis para APIs, mas para construir uma aplicação web, as sessões podem ser cruciais para manter o estado e informações de login.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - Biblioteca oficial de sessão Flight. Trata-se de uma biblioteca de sessão simples que pode ser usada para armazenar e recuperar dados de sessão. Ela usa o manuseio de sessão integrado do PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gerenciador de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa o PHP open_ssl para criptografia opcional/descriptografia de dados de sessão.

## Motor de Modelos

O motor de modelos é essencial para qualquer aplicação web com uma interface de usuário. Há uma série de motores de modelos que podem ser usados com Flight.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - Trata-se de um motor de modelos muito básico que faz parte do core. Não é recomendado usá-lo se você tiver mais do que algumas páginas no seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é um motor de modelos completo que é muito fácil de usar e se sente mais próximo de uma sintaxe PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar os seus próprios filtros e funções.

## Contribuindo

Tem um plugin que você gostaria de compartilhar? Envie uma solicitação de pull para adicioná-lo à lista!