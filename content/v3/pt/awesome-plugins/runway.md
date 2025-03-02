# Pista

A Pista é uma aplicação CLI que ajuda a gerenciar suas aplicações Flight. Ela pode gerar controladores, exibir todas as rotas e mais. É baseada na excelente biblioteca [adhocore/php-cli](https://github.com/adhocore/php-cli).

Clique [aqui](https://github.com/flightphp/runway) para visualizar o código.

## Instalação

Instale com o composer.

```bash
composer require flightphp/runway
```

## Configuração Básica

Na primeira execução da Pista, ela guiará você por um processo de configuração e criará um arquivo de configuração `.runway.json` na raiz do seu projeto. Este arquivo conterá algumas configurações necessárias para que a Pista funcione corretamente.

## Utilização

A Pista possui vários comandos que você pode usar para gerenciar sua aplicação Flight. Existem duas maneiras fáceis de usar a Pista.

1. Se estiver usando o projeto esqueleto, você pode executar `php runway [comando]` a partir da raiz do seu projeto.
1. Se estiver usando a Pista como um pacote instalado via composer, você pode executar `vendor/bin/runway [comando]` a partir da raiz do seu projeto.

Para qualquer comando, você pode inserir a flag `--help` para obter mais informações sobre como utilizar o comando.

```bash
php runway routes --help
```

Aqui estão alguns exemplos:

### Gerar um Controlador

Com base na configuração em seu arquivo `.runway.json`, a localização padrão gerará um controlador para você no diretório `app/controllers/`.

```bash
php runway make:controller MeuControlador
```

### Gerar um Modelo Active Record

Com base na configuração em seu arquivo `.runway.json`, a localização padrão gerará um controlador para você no diretório `app/records/`.

```bash
php runway make:record usuários
```

Se por acaso você tiver a tabela `usuários` com o seguinte esquema: `id`, `nome`, `email`, `criado_em`, `atualizado_em`, um arquivo semelhante ao seguinte será criado no arquivo `app/records/RegistroUsuario.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe Active Record para a tabela de usuários.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $nome
 * @property string $email
 * @property string $criado_em
 * @property string $atualizado_em
 * // você também pode adicionar relacionamentos aqui uma vez que os definir no array $relações
 * @property RegistroEmpresa $empresa Exemplo de um relacionamento
 */
class RegistroUsuario extends \flight\ActiveRecord
{
    /**
     * @var array $relações Define os relacionamentos para o modelo
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relações = [];

    /**
     * Construtor
     * @param mixed $conexãoBancoDados A conexão com o banco de dados
     */
    public function __construct($conexãoBancoDados)
    {
        parent::__construct($conexãoBancoDados, 'usuários');
    }
}
```

### Exibir Todas as Rotas

Isso exibirá todas as rotas atualmente registradas com o Flight.

```bash
php runway routes
```

Se desejar visualizar apenas rotas específicas, você pode inserir uma flag para filtrar as rotas.

```bash
# Exibir apenas rotas GET
php runway routes --get

# Exibir apenas rotas POST
php runway routes --post

# etc.
```

## Personalizando a Pista

Se você está criando um pacote para o Flight ou deseja adicionar seus próprios comandos personalizados ao seu projeto, pode fazer isso criando um diretório `src/commands/`, `flight/commands/`, `app/commands/` ou `commands/` para o seu projeto/pacote.

Para criar um comando, você simplesmente estende a classe `AbstractBaseCommand` e implementa, no mínimo, um método `__construct` e um método `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ComandoExemplo extends AbstractBaseCommand
{
	/**
     * Construtor
     *
     * @param array<string,mixed> $config Configuração JSON de .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Criar um exemplo para a documentação', $config);
        $this->argument('<gif-engracado>', 'O nome do gif engraçado');
    }

	/**
     * Executa a função
     *
     * @return void
     */
    public function execute(string $controlador)
    {
        $io = $this->app()->io();

		$io->info('Criando exemplo...');

		// Faça algo aqui

		$io->ok('Exemplo criado!');
	}
}
```

Consulte a [Documentação do adhocore/php-cli](https://github.com/adhocore/php-cli) para mais informações sobre como criar seus próprios comandos personalizados em sua aplicação Flight!