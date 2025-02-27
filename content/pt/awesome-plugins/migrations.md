# Migrations

Uma migração para o seu projeto é o acompanhamento de todas as alterações de banco de dados envolvidas no seu projeto.
[byjg/php-migration](https://github.com/byjg/php-migration) é uma biblioteca central muito útil para
começar.

## Instalando

### Biblioteca PHP

Se você quiser usar apenas a Biblioteca PHP em seu projeto:

```bash
composer require "byjg/migration"
```

### Interface de Linha de Comando

A interface de linha de comando é autônoma e não requer instalação com seu projeto.

Você pode instalar globalmente e criar um link simbólico

```bash
composer require "byjg/migration-cli"
```

Por favor, visite [byjg/migration-cli](https://github.com/byjg/migration-cli) para obter mais informações sobre Migration CLI.

## Bancos de dados suportados

| Banco de dados  | Driver                                                                          | String de Conexão                                        |
| ----------------| ------------------------------------------------------------------------------- | -------------------------------------------------------- |
| Sqlite          | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                  | sqlite:///caminho/para/arquivo                           |
| MySql/MariaDb   | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                    | mysql://usuario:senha@hostname:porta/banco              |
| Postgres        | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                    | pgsql://usuario:senha@hostname:porta/banco              |
| Sql Server      | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux | dblib://usuario:senha@hostname:porta/banco              |
| Sql Server      | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows   | sqlsrv://usuario:senha@hostname:porta/banco             |

## Como Funciona?

A Migração de Banco de Dados utiliza SQL PURO para gerenciar a versionamento do banco de dados.
Para funcionar, você precisa de:

* Criar os Scripts SQL
* Gerenciar usando a Linha de Comando ou a API.

### Os Scripts SQL

Os scripts são divididos em três conjuntos de scripts:

* O script BASE contém TODOS os comandos sql para criar um banco de dados novo;
* Os scripts UP contêm todos os comandos de migração sql para "subir" a versão do banco de dados;
* Os scripts DOWN contêm todos os comandos de migração sql para "descer" ou reverter a versão do banco de dados;

O diretório dos scripts é:

```text
 <diretório raiz>
     |
     +-- base.sql
     |
     +-- /migrations
              |
              +-- /up
                   |
                   +-- 00001.sql
                   +-- 00002.sql
              +-- /down
                   |
                   +-- 00000.sql
                   +-- 00001.sql
```

* "base.sql" é o script base
* A pasta "up" contém os scripts para migrar para cima na versão.
   Por exemplo: 00002.sql é o script para mover o banco de dados da versão '1' para '2'.
* A pasta "down" contém os scripts para migrar para baixo na versão.
   Por exemplo: 00001.sql é o script para mover o banco de dados da versão '2' para '1'.
   A pasta "down" é opcional.

### Ambiente de Desenvolvimento Múltiplo

Se você trabalha com vários desenvolvedores e múltiplas ramificações, pode ser muito difícil determinar qual é o próximo número.

Nesse caso, você tem o sufixo "-dev" após o número da versão.

Veja o cenário:

* O Desenvolvedor 1 cria uma ramificação e a versão mais recente é, por exemplo, 42.
* O Desenvolvedor 2 cria uma ramificação ao mesmo tempo e tem o mesmo número de versão do banco de dados.

Em ambos os casos, os desenvolvedores criarão um arquivo chamado 43-dev.sql. Ambos os desenvolvedores migrarão para CIMA e para BAIXO sem
problemas e sua versão local será 43.

Mas o desenvolvedor 1 mesclou suas alterações e criou uma versão final 43.sql (`git mv 43-dev.sql 43.sql`). Se o desenvolvedor 2
atualizar sua ramificação local, ele terá um arquivo 43.sql (do dev 1) e seu arquivo 43-dev.sql.
Se ele tentar migrar para CIMA ou para BAIXO,
o script de migração irá falhar e alertá-lo que há DUAS versões 43. Nesse caso, o desenvolvedor 2 terá que atualizar seu
arquivo para 44-dev.sql e continuar a trabalhar até mesclar suas alterações e gerar uma versão final.

## Usando a API PHP e Integrando-a em seus projetos

O uso básico é

* Criar uma conexão com um objeto ConnectionManagement. Para mais informações, veja o componente "byjg/anydataset".
* Criar um objeto Migration com essa conexão e a pasta onde os scripts sql estão localizados.
* Use o comando apropriado para "resetar", "subir" ou "descer" os scripts de migração.

Veja um exemplo:

```php
<?php
// Criar o URI de Conexão
// Veja mais: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Registrar o Banco de Dados ou Bancos de Dados que podem manipular esse URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Criar a instância de Migração
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Adicionar uma função de progresso de callback para receber informações da execução
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Restaurar o banco de dados usando o script "base.sql"
// e executar TODOS os scripts existentes para subir a versão do banco de dados para a versão mais recente
$migration->reset();

// Executar TODOS os scripts existentes para subir ou descer a versão do banco de dados
// da versão atual até o número $version;
// Se o número da versão não for especificado, migrar até a última versão do banco de dados
$migration->update($version = null);
```

O objeto Migration controla a versão do banco de dados.

### Criando um controle de versão em seu projeto

```php
<?php
// Registrar o Banco de Dados ou Bancos de Dados que podem manipular esse URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Criar a instância de Migração
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Este comando criará a tabela de versões em seu banco de dados
$migration->createVersion();
```

### Obtendo a versão atual

```php
<?php
$migration->getCurrentVersion();
```

### Adicionar Callback para controlar o progresso

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Executando Comando: $command na versão $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Obtendo a instância do Driver de Db

```php
<?php
$migration->getDbDriver();
```

Para utilizá-lo, visite: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Evitando Migração Parcial (não disponível para MySQL)

Uma migração parcial é quando o script de migração é interrompido no meio do processo devido a um erro ou uma interrupção manual.

A tabela de migração ficará com o status `partial up` ou `partial down` e precisará ser corrigida manualmente antes que possa ser migrada novamente.

Para evitar essa situação, você pode especificar que a migração será executada em um contexto transacional.
Se o script de migração falhar, a transação será revertida e a tabela de migração será marcada como `complete` e
a versão será a imediatamente anterior antes do script que causou o erro.

Para habilitar esse recurso, você precisa chamar o método `withTransactionEnabled` passando `true` como parâmetro:

```php
<?php
$migration->withTransactionEnabled(true);
```

**NOTA: Este recurso não está disponível para MySQL, pois não suporta comandos DDL dentro de uma transação.**
Se você usar este método com MySQL, a Migração irá ignorá-lo silenciosamente.
Mais informações: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Dicas para escrever migrações SQL para Postgres

### Ao criar gatilhos e funções SQL

```sql
-- FAÇA
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Verifique se empname e salary foram fornecidos
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname não pode ser nulo'; -- não importa se esses comentários estão em branco ou não
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% não pode ter salário nulo', NEW.empname; --
        END IF; --

        -- Quem trabalha para nós quando eles devem pagar por isso?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% não pode ter um salário negativo', NEW.empname; --
        END IF; --

        -- Lembre-se de quem mudou a folha de pagamento quando
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- NÃO FAÇA
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Verifique se empname e salary foram fornecidos
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname não pode ser nulo';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% não pode ter salário nulo', NEW.empname;
        END IF;

        -- Quem trabalha para nós quando eles devem pagar por isso?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% não pode ter um salário negativo', NEW.empname;
        END IF;

        -- Lembre-se de quem mudou a folha de pagamento quando
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Como a camada de abstração de banco de dados `PDO` não pode executar lotes de instruções SQL,
quando `byjg/migration` lê um arquivo de migração, ele tem que dividir todo o conteúdo do arquivo SQL nos ponto e vírgula e executar as instruções uma a uma. No entanto, há um tipo de instrução que pode ter múltiplos ponto e vírgula em seu corpo: funções.

Para ser capaz de analisar funções corretamente, `byjg/migration` 2.1.0 começou a dividir arquivos de migração na sequência `ponto e vírgula + EOL` em vez de apenas no ponto e vírgula. Dessa forma, se você adicionar um comentário vazio após cada ponto e vírgula interno de uma definição de função, `byjg/migration` poderá analisá-lo.

Infelizmente, se você esquecer de adicionar algum desses comentários, a biblioteca dividirá a instrução `CREATE FUNCTION` em várias partes e a migração falhará.

### Evitar o caractere de dois-pontos (`:`)

```sql
-- FAÇA
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);


-- NÃO FAÇA
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

Como o `PDO` usa o caractere dois-pontos para prefixar parâmetros nomeados em instruções preparadas, seu uso causará problemas em outros contextos.

Por exemplo, as instruções PostgreSQL podem usar `::` para converter valores entre tipos. Por outro lado, o `PDO` lerá isso como um parâmetro nomeado inválido em um contexto inválido e falhará ao tentar executá-lo.

A única maneira de corrigir essa inconsistência é evitar completamente os dois-pontos (neste caso, o PostgreSQL também tem uma sintaxe alternativa: `CAST(value AS type)`).

### Use um editor SQL

Finalmente, escrever migrações SQL manuais pode ser cansativo, mas é significativamente mais fácil se
você usar um editor capaz de entender a sintaxe SQL, fornecendo autocompletar,
introspectando seu esquema de banco de dados atual e/ou autoformatando seu código.

## Lidar com diferentes migrações dentro de um esquema

Se você precisar criar diferentes scripts de migração e versões dentro do mesmo esquema, é possível
mas é muito arriscado e eu **não** recomendo de forma alguma.

Para fazer isso, você precisa criar diferentes "tabelas de migração" passando o parâmetro para o construtor.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

Por razões de segurança, esse recurso não está disponível na linha de comando, mas você pode usar a variável de ambiente
`MIGRATION_VERSION` para armazenar o nome.

Recomendamos fortemente não usar esse recurso. A recomendação é uma migração para um esquema.

## Executando Testes Unitários

Testes unitários básicos podem ser executados com:

```bash
vendor/bin/phpunit
```

## Executando testes de banco de dados

Executar testes de integração requer que você tenha os bancos de dados em funcionamento. Fornecemos um básico `docker-compose.yml` e você
pode usá-lo para iniciar os bancos de dados para testes.

### Executando os bancos de dados

```bash
docker-compose up -d postgres mysql mssql
```

### Executar os testes

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

Opcionalmente, você pode definir o host e a senha usados pelos testes unitários

```bash
export MYSQL_TEST_HOST=localhost     # padrão é localhost
export MYSQL_PASSWORD=newpassword    # use '.' se quiser ter uma senha nula
export PSQL_TEST_HOST=localhost      # padrão é localhost
export PSQL_PASSWORD=newpassword     # use '.' se quiser ter uma senha nula
export MSSQL_TEST_HOST=localhost     # padrão é localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # padrão é /tmp/test.db
```