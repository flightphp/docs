# Runway

Runway é uma aplicação CLI que ajuda você a gerenciar suas aplicações Flight. Ela pode gerar controladores, exibir todas as rotas e mais. É baseada na excelente biblioteca [adhocore/php-cli](https://github.com/adhocore/php-cli).

Clique [aqui](https://github.com/flightphp/runway) para visualizar o código.

## Instalação

Instale com o composer.

```bash
composer require flightphp/runway
```

## Configuração Básica

Na primeira vez que você executar o Runway, ele o guiará por um processo de configuração e criará um arquivo de configuração `.runway.json` na raiz do seu projeto. Este arquivo conterá algumas configurações necessárias para o Runway funcionar corretamente.

## Uso

O Runway possui vários comandos que você pode usar para gerenciar sua aplicação Flight. Existem duas maneiras fáceis de usar o Runway.

1. Se você estiver usando o projeto skeleton, você pode executar `php runway [command]` a partir da raiz do seu projeto.
1. Se você estiver usando o Runway como um pacote instalado via composer, você pode executar `vendor/bin/runway [command]` a partir da raiz do seu projeto.

Para qualquer comando, você pode passar a flag `--help` para obter mais informações sobre como usar o comando.

```bash
php runway routes --help
```

Aqui estão alguns exemplos:

### Gerar um Controlador

Com base na configuração no seu arquivo `.runway.json`, o local padrão gerará um controlador para você no diretório `app/controllers/`.

```bash
php runway make:controller MyController
```

### Gerar um Modelo Active Record

Com base na configuração no seu arquivo `.runway.json`, o local padrão gerará um controlador para você no diretório `app/records/`.

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

Isso exibirá todas as rotas que estão atualmente registradas no Flight.

```bash
php runway routes
```

Se você quiser visualizar apenas rotas específicas, pode passar uma flag para filtrar as rotas.

```bash
# Exibir apenas rotas GET
php runway routes --get

# Exibir apenas rotas POST
php runway routes --post

# etc.
```

## Personalizando o Runway

Se você estiver criando um pacote para o Flight, ou quiser adicionar seus próprios comandos personalizados ao seu projeto, você pode fazer isso criando um diretório `src/commands/`, `flight/commands/`, `app/commands/`, ou `commands/` para o seu projeto/pacote. Se precisar de mais personalização, veja a seção abaixo sobre Configuração.

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
     * @param array<string,mixed> $config Configuração JSON de .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Criar um exemplo para a documentação', $config);
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

### Configuração

Se você precisar personalizar a configuração para o Runway, pode criar um arquivo `.runway-config.json` na raiz do seu projeto. Abaixo estão algumas configurações adicionais que você pode definir:

```js
{

	// Este é o local onde o diretório da sua aplicação está localizado
	"app_root": "app/",

	// Este é o diretório onde o arquivo index raiz está localizado
	"index_root": "public/",

	// Estes são os caminhos para as raízes de outros projetos
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// Caminhos base provavelmente não precisam ser configurados, mas está aqui se você quiser
	"base_paths": {
		"/includes/libs/vendor", // se você tiver um caminho realmente único para o diretório vendor ou algo assim
	},

	// Caminhos finais são locais dentro de um projeto para procurar os arquivos de comando
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// Se você quiser apenas adicionar o caminho completo, vá em frente (absoluto ou relativo à raiz do projeto)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```