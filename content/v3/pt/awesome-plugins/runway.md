# Runway

Runway é uma aplicação CLI que ajuda você a gerenciar suas aplicações Flight. Ela pode gerar controladores, exibir todas as rotas e mais. É baseada na excelente biblioteca [adhocore/php-cli](https://github.com/adhocore/php-cli).

Clique [aqui](https://github.com/flightphp/runway) para visualizar o código.

## Instalação

Instale com o composer.

```bash
composer require flightphp/runway
```

## Configuração Básica

Na primeira vez que você executar o Runway, ele tentará encontrar uma configuração `runway` em `app/config/config.php` via a chave `'runway'`.

```php
<?php
// app/config/config.php
return [
    'runway' => [
        'app_root' => 'app/',
		'public_root' => 'public/',
    ],
];
```

> **NOTA** - A partir de **v1.2.0**, `.runway-config.json` está depreciado. Por favor, migre sua configuração para `app/config/config.php`. Você pode fazer isso facilmente com o comando `php runway config:migrate`.

### Detecção da Raiz do Projeto

Runway é inteligente o suficiente para detectar a raiz do seu projeto, mesmo se você o executar a partir de um subdiretório. Ele procura indicadores como `composer.json`, `.git` ou `app/config/config.php` para determinar onde está a raiz do projeto. Isso significa que você pode executar comandos do Runway de qualquer lugar no seu projeto! 

## Uso

Runway tem uma série de comandos que você pode usar para gerenciar sua aplicação Flight. Existem duas maneiras fáceis de usar o Runway.

1. Se você estiver usando o projeto esqueleto, você pode executar `php runway [command]` a partir da raiz do seu projeto.
1. Se você estiver usando Runway como um pacote instalado via composer, você pode executar `vendor/bin/runway [command]` a partir da raiz do seu projeto.

### Lista de Comandos

Você pode visualizar uma lista de todos os comandos disponíveis executando o comando `php runway`.

```bash
php runway
```

### Ajuda do Comando

Para qualquer comando, você pode passar a flag `--help` para obter mais informações sobre como usar o comando.

```bash
php runway routes --help
```

Aqui estão alguns exemplos:

### Gerar um Controlador

Com base na configuração em `runway.app_root`, o local gerará um controlador para você no diretório `app/controllers/`.

```bash
php runway make:controller MyController
```

### Gerar um Modelo Active Record

Primeiro, certifique-se de que você instalou o plugin [Active Record](/awesome-plugins/active-record). Com base na configuração em `runway.app_root`, o local gerará um registro para você no diretório `app/records/`.

```bash
php runway make:record users
```

Se, por exemplo, você tiver a tabela `users` com o seguinte esquema: `id`, `name`, `email`, `created_at`, `updated_at`, um arquivo semelhante ao seguinte será criado no arquivo `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe ActiveRecord para a tabela users.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // você também poderia adicionar relacionamentos aqui uma vez que os defina no array $relations
 * @property CompanyRecord $company Exemplo de um relacionamento
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Define os relacionamentos para o modelo
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Construtor
     * @param mixed $databaseConnection A conexão com o banco de dados
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Exibir Todas as Rotas

Isso exibirá todas as rotas que estão atualmente registradas com o Flight.

```bash
php runway routes
```

Se você quiser visualizar apenas rotas específicas, você pode passar uma flag para filtrar as rotas.

```bash
# Exibir apenas rotas GET
php runway routes --get

# Exibir apenas rotas POST
php runway routes --post

# etc.
```

## Adicionando Comandos Personalizados ao Runway

Se você estiver criando um pacote para o Flight ou quiser adicionar seus próprios comandos personalizados ao seu projeto, você pode fazer isso criando um diretório `src/commands/`, `flight/commands/`, `app/commands/` ou `commands/` para o seu projeto/pacote. Se precisar de mais personalização, veja a seção abaixo sobre Configuração.

Para criar um comando, você simplesmente estende a classe `AbstractBaseCommand` e implementa, no mínimo, um método `__construct` e um método `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Construtor
     *
     * @param array<string,mixed> $config Config de app/config/config.php
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Cria um exemplo para a documentação', $config);
        $this->argument('<funny-gif>', 'O nome do gif engraçado');
    }

	/**
     * Executa a função
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Criando exemplo...');

		// Faça algo aqui

		$io->ok('Exemplo criado!');
	}
}
```

Consulte a [Documentação do adhocore/php-cli](https://github.com/adhocore/php-cli) para mais informações sobre como construir seus próprios comandos personalizados para sua aplicação Flight!

## Gerenciamento de Configuração

Como a configuração foi movida para `app/config/config.php` a partir da `v1.2.0`, há alguns comandos auxiliares para gerenciar a configuração.

### Migrar Configuração Antiga

Se você tiver um arquivo `.runway-config.json` antigo, você pode migrá-lo facilmente para `app/config/config.php` com o seguinte comando:

```bash
php runway config:migrate
```

### Definir Valor de Configuração

Você pode definir um valor de configuração usando o comando `config:set`. Isso é útil se você quiser atualizar um valor de configuração sem abrir o arquivo.

```bash
php runway config:set app_root "app/"
```

### Obter Valor de Configuração

Você pode obter um valor de configuração usando o comando `config:get`.

```bash
php runway config:get app_root
```

## Todas as Configurações do Runway

Se você precisar personalizar a configuração para o Runway, você pode definir esses valores em `app/config/config.php`. Abaixo estão algumas configurações adicionais que você pode definir:

```php
<?php
// app/config/config.php
return [
    // ... outros valores de configuração ...

    'runway' => [
        // Este é o local onde o diretório da sua aplicação está localizado
        'app_root' => 'app/',

        // Este é o diretório onde o seu arquivo index raiz está localizado
        'index_root' => 'public/',

        // Estes são os caminhos para as raízes de outros projetos
        'root_paths' => [
            '/home/user/different-project',
            '/var/www/another-project'
        ],

        // Caminhos base provavelmente não precisam ser configurados, mas está aqui se você quiser
        'base_paths' => [
            '/includes/libs/vendor', // se você tiver um caminho realmente único para o seu diretório vendor ou algo assim
        ],

        // Caminhos finais são locais dentro de um projeto para procurar os arquivos de comando
        'final_paths' => [
            'src/diff-path/commands',
            'app/module/admin/commands',
        ],

        // Se você quiser apenas adicionar o caminho completo, vá em frente (absoluto ou relativo à raiz do projeto)
        'paths' => [
            '/home/user/different-project/src/diff-path/commands',
            '/var/www/another-project/app/module/admin/commands',
            'app/my-unique-commands'
        ]
    ]
];
```

### Acessando a Configuração

Se você precisar acessar os valores de configuração de forma eficaz, você pode acessá-los através do método `__construct` ou do método `app()`. Também é importante notar que, se você tiver um arquivo `app/config/services.php`, esses serviços também estarão disponíveis para o seu comando.

```php
public function execute()
{
    $io = $this->app()->io();
    
    // Acessar configuração
    $app_root = $this->config['runway']['app_root'];
    
    // Acessar serviços como talvez uma conexão com o banco de dados
    $database = $this->config['database']
    
    // ...
}
```

## Wrappers de Auxílio de IA

Runway tem alguns wrappers de auxílio que facilitam para a IA gerar comandos. Você pode usar `addOption` e `addArgument` de uma maneira que se sinta semelhante ao Symfony Console. Isso é útil se você estiver usando ferramentas de IA para gerar seus comandos.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Cria um exemplo para a documentação', $config);
    
    // O argumento mode é anulável e padrão para completamente opcional
    $this->addOption('name', 'O nome do exemplo', null);
}
```