# Aprenda Sobre o Flight

Flight é um framework rápido, simples e extensível para PHP. Ele é bastante versátil e pode ser usado para construir qualquer tipo de aplicação web. 
Ele é construído com simplicidade em mente e é escrito de uma forma que é fácil de entender e usar.

> **Nota:** Você verá exemplos que usam `Flight::` como uma variável estática e alguns que usam o objeto Engine `$app->`. Ambos funcionam de forma intercambiável com o outro. `$app` e `$this->app` em um controller/middleware é a abordagem recomendada pela equipe do Flight.

## Componentes Principais

### [Routing](/learn/routing)

Aprenda como gerenciar rotas para sua aplicação web. Isso também inclui agrupar rotas, parâmetros de rota e middleware.

### [Middleware](/learn/middleware)

Aprenda como usar middleware para filtrar requisições e respostas em sua aplicação.

### [Autoloading](/learn/autoloading)

Aprenda como autoloadar suas próprias classes em sua aplicação.

### [Requests](/learn/requests)

Aprenda como lidar com requisições e respostas em sua aplicação.

### [Responses](/learn/responses)

Aprenda como enviar respostas para seus usuários.

### [HTML Templates](/learn/templates)

Aprenda como usar o motor de visualização integrado para renderizar seus templates HTML.

### [Security](/learn/security)

Aprenda como proteger sua aplicação contra ameaças de segurança comuns.

### [Configuration](/learn/configuration)

Aprenda como configurar o framework para sua aplicação.

### [Event Manager](/learn/events)

Aprenda como usar o sistema de eventos para adicionar eventos personalizados à sua aplicação.

### [Extending Flight](/learn/extending)

Aprenda como estender o framework adicionando seus próprios métodos e classes.

### [Method Hooks and Filtering](/learn/filtering)

Aprenda como adicionar hooks de eventos aos seus métodos e métodos internos do framework.

### [Dependency Injection Container (DIC)](/learn/dependency-injection-container)

Aprenda como usar contêineres de injeção de dependência (DIC) para gerenciar as dependências de sua aplicação.

## Classes de Utilidade

### [Collections](/learn/collections)

Collections são usadas para armazenar dados e serem acessíveis como um array ou como um objeto para facilitar o uso.

### [JSON Wrapper](/learn/json)

Isso tem algumas funções simples para tornar a codificação e decodificação do seu JSON consistente.

### [PDO Wrapper](/learn/pdo-wrapper)

PDO às vezes pode adicionar mais dor de cabeça do que o necessário. Esta classe wrapper simples pode tornar significativamente mais fácil interagir com seu banco de dados.

### [Uploaded File Handler](/learn/uploaded-file)

Uma classe simples para ajudar a gerenciar arquivos enviados e movê-los para um local permanente.

## Conceitos Importantes

### [Por Que um Framework?](/learn/why-frameworks)

Aqui está um artigo curto sobre por que você deve usar um framework. É uma boa ideia entender os benefícios de usar um framework antes de começar a usar um.

Adicionalmente, um excelente tutorial foi criado por [@lubiana](https://git.php.fail/lubiana). Embora não entre em grandes detalhes sobre o Flight especificamente, 
este guia ajudará você a entender alguns dos principais conceitos ao redor de um framework e por que eles são benéficos de usar. 
Você pode encontrar o tutorial [aqui](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md).

### [Flight Comparado a Outros Frameworks](/learn/flight-vs-another-framework)

Se você está migrando de outro framework como Laravel, Slim, Fat-Free ou Symfony para o Flight, esta página ajudará você a entender as diferenças entre os dois.

## Outros Tópicos

### [Unit Testing](/learn/unit-testing)

Siga este guia para aprender como fazer testes unitários no seu código Flight para que seja sólido como uma rocha.

### [AI & Developer Experience](/learn/ai)

Aprenda como o Flight funciona com ferramentas de IA e fluxos de trabalho modernos de desenvolvedor para ajudá-lo a codificar mais rápido e de forma mais inteligente.

### [Migrating v2 -> v3](/learn/migrating-to-v3)

A compatibilidade com versões anteriores foi mantida na maior parte, mas há algumas mudanças das quais você deve estar ciente ao migrar da v2 para a v3.