# Pista

**Pista** é uma aplicação de CLI que ajuda a gerenciar suas aplicações **Flight**. Pode gerar controladores, exibir todas as rotas e muito mais. Baseia-se na excelente biblioteca [adhocore/php-cli](https://github.com/adhocore/php-cli).

## Instalação

Instale com o composer.

```bash
composer require flightphp/runway
```

## Configuração Básica

Na primeira vez que você executar **Pista**, ele o guiará por um processo de configuração e criará um arquivo de configuração `.runway.json` na raiz do seu projeto. Esse arquivo conterá algumas configurações necessárias para que **Pista** funcione corretamente.

## Utilização

**Pista** possui vários comandos que você pode usar para gerenciar sua aplicação **Flight**. Existem duas maneiras fáceis de usar **Pista**.

1. Se estiver usando o projeto esqueleto, você pode executar `php runway [comando]` a partir da raiz do seu projeto.
1. Se estiver usando **Pista** como um pacote instalado via composer, você pode executar `vendor/bin/runway [comando]` a partir da raiz do seu projeto.

Para qualquer comando, você pode passar a flag `--help` para obter mais informações sobre como usar o comando.

```bash
php runway routes --help
```

Aqui estão alguns exemplos:

### Gerar um Controlador

Com base na configuração em seu arquivo `.runway.json`, a localização padrão gerará um controlador para você no diretório `app/controllers/`.

```bash
php runway make:controller MeuControlador
```

### Gerar um Modelo de Active Record

Com base na configuração em seu arquivo `.runway.json`, a localização padrão gerará um controlador para você no diretório `app/records/`.

```bash
php runway make:record users
```

Por exemplo, se você tiver a tabela `users` com o esquema a seguir: `id`, `name`, `email`, `created_at`, `updated_at`, um arquivo semelhante ao seguinte será criado no arquivo `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe Active Record para a tabela de usuários.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // você também pode adicionar relacionamentos aqui uma vez que os define no array $relations
 * @property CompanyRecord $company Exemplo de um relacionamento
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Defina os relacionamentos para o modelo
     *   https://docs.flightphp.com/awesome-plugins/active-record#relacionamentos
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

Isso exibirá todas as rotas que estão atualmente registradas no **Flight**.

```bash
php runway routes
```

Se desejar visualizar apenas rotas específicas, você pode passar uma flag para filtrar as rotas.

```bash
# Mostrar apenas rotas GET
php runway routes --get

# Mostrar apenas rotas POST
php runway routes --post

# etc.
```

## Personalizando a Pista

Se você estiver criando um pacote para o **Flight** ou deseja adicionar seus próprios comandos personalizados em seu projeto, pode fazer isso criando um diretório `src/commands/`, `flight/commands/`, `app/commands/` ou `commands/` para o seu projeto/pacote.

Para criar um comando, basta estender a classe `AbstractBaseCommand` e implementar, no mínimo, um método `__construct` e um método `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Construtor
     *
     * @param array<string,mixed> $config JSON config do .runway-config.json
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
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('Criando exemplo...');

		// Faça algo aqui

		$io->ok('Exemplo criado!');
	}
}
```

Consulte a [Documentação do adhocore/php-cli](https://github.com/adhocore/php-cli) para mais informações sobre como criar seus próprios comandos personalizados em sua aplicação **Flight**!