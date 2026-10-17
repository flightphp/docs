# Documentação do FlightPHP APM

Bem-vindo ao FlightPHP APM—o treinador pessoal de desempenho do seu app! Este guia é o seu roteiro para configurar, usar e dominar o Application Performance Monitoring (APM) com FlightPHP. Se você está caçando requisições lentas ou só quer ficar fissurado nos gráficos de latência, nós cuidamos de tudo. Vamos deixar seu app mais rápido, seus usuários mais felizes e suas sessões de depuração muito mais tranquilas!

Veja uma [demo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) do dashboard para o site do Flight Docs.

![FlightPHP APM](/images/apm.png)

## Por que o APM importa

Imagine isto: seu app é um restaurante movimentado. Sem uma forma de rastrear quanto tempo os pedidos levam ou onde a cozinha está travando, você fica adivinhando por que os clientes estão indo embora irritados. O APM é o seu subchef—ele observa cada etapa, desde requisições recebidas até consultas ao banco de dados, e sinaliza qualquer coisa que esteja deixando você mais lento. Páginas lentas perdem usuários (estudos dizem que 53% abandonam se um site levar mais de 3 segundos para carregar!), e o APM ajuda você a detectar esses problemas *antes* que eles causem danos. É tranquilidade proativa—menos momentos de “por que isso está quebrado?” e mais vitórias do tipo “olha como isso roda bem!”.

## Instalação

Comece com o Composer:

```bash
composer require flightphp/apm
```

Você vai precisar de:
- **PHP 7.4+**: Mantém compatibilidade com distros Linux LTS enquanto oferece suporte a PHP moderno.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: O framework leve que estamos turbinando.

## Bancos de dados compatíveis

No momento, o FlightPHP APM oferece suporte aos seguintes bancos de dados para armazenar métricas:

- **SQLite3**: Simples, baseado em arquivo e ótimo para desenvolvimento local ou apps pequenos. Opção padrão na maioria das configurações.
- **MySQL/MariaDB**: Ideal para projetos maiores ou ambientes de produção em que você precisa de armazenamento robusto e escalável.

Você pode escolher o tipo de banco de dados durante a etapa de configuração (veja abaixo). Certifique-se de que seu ambiente PHP tenha as extensões necessárias instaladas (por exemplo, `pdo_sqlite` ou `pdo_mysql`).

## Primeiros passos

Aqui está o seu passo a passo para o incrível mundo do APM:

### 1. Registre o APM

Coloque isto no seu `index.php` ou em um arquivo `services.php` para começar o rastreamento:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\SimplePdo;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Se você estiver adicionando uma conexão de banco de dados
// Prefira SimplePdo (ou PdoQueryCapture das Tracy Extensions em desenvolvimento).
// Ative o rastreamento de consultas do APM por meio do array de opções (5º argumento).
$pdo = new SimplePdo('mysql:host=localhost;dbname=example', 'user', 'pass', null, [
	'trackApmQueries' => true, // obrigatório para capturar consultas para o APM
]);
$Apm->addPdoConnection($pdo);
```

**O que está acontecendo aqui?**
- `LoggerFactory::create()` pega sua configuração (mais sobre isso em breve) e configura um logger—SQLite por padrão.
- `Apm` é a estrela—ele escuta os eventos do Flight (requisições, rotas, erros etc.) e coleta métricas.
- `bindEventsToFlightInstance($app)` conecta tudo ao seu app Flight.

**Dica profissional: amostragem**
Se seu app estiver movimentado, registrar *todas* as requisições pode sobrecarregar as coisas. Use uma taxa de amostragem (0.0 a 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Registra 10% das requisições
```

Isso mantém o desempenho ágil e ainda fornece dados sólidos.

### 2. Configure

Execute isto para criar seu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**O que isso faz?**
- Inicia um assistente perguntando de onde vêm as métricas brutas (origem) e para onde vão os dados processados (destino).
- O padrão é SQLite—por exemplo, `sqlite:/tmp/apm_metrics.sqlite` para origem e outro para destino.
- Você terminará com uma configuração como:
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> Esse processo também perguntará se você deseja executar as migrações para esta configuração. Se você estiver configurando isso pela primeira vez, a resposta é sim.

**Por que dois locais?**
Métricas brutas se acumulam rápido (pense em logs não filtrados). O worker as processa em um destino estruturado para o dashboard. Mantém as coisas organizadas!

### 3. Processe métricas com o worker

O worker transforma métricas brutas em dados prontos para o dashboard. Execute-o uma vez:

```bash
php vendor/bin/runway apm:worker
```

**O que ele está fazendo?**
- Lê da sua origem (por exemplo, `apm_metrics.sqlite`).
- Processa até 100 métricas (tamanho de lote padrão) no seu destino.
- Para quando termina ou se não houver mais métricas.

**Mantenha-o em execução**
Para apps em produção, você vai querer processamento contínuo. Aqui estão suas opções:

- **Modo daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Roda para sempre, processando métricas conforme chegam. Ótimo para desenvolvimento ou configurações pequenas.

- **Crontab**:
  Adicione isto ao seu crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Dispara a cada minuto—perfeito para produção.

- **Tmux/Screen**:
  Inicie uma sessão destacável:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, depois D para desconectar; `tmux attach -t apm-worker` para reconectar
  ```
  Mantém a execução mesmo se você sair.

- **Ajustes personalizados**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Processa 50 métricas por vez.
  - `--max_messages 1000`: Para após 1000 métricas.
  - `--timeout 300`: Encerra após 5 minutos.

**Por que se importar?**
Sem o worker, seu dashboard fica vazio. Ele é a ponte entre logs brutos e insights acionáveis.

### 4. Inicie o dashboard

Veja os sinais vitais do seu app:

```bash
php vendor/bin/runway apm:dashboard
```

**O que é isto?**
- Inicia um servidor PHP em `http://localhost:8001/apm/dashboard`.
- Mostra logs de requisições, rotas lentas, taxas de erro e mais.

**Personalize**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Acessível de qualquer IP (útil para visualização remota).
- `--port 8080`: Use uma porta diferente se a 8001 estiver ocupada.
- `--php-path`: Aponte para o PHP se ele não estiver no seu PATH.

Acesse a URL no seu navegador e explore!

#### Modo de produção

Em produção, talvez você precise tentar algumas técnicas para fazer o dashboard funcionar, já que provavelmente há firewalls e outras medidas de segurança em vigor. Aqui estão algumas opções:

- **Use um proxy reverso**: Configure Nginx ou Apache para encaminhar requisições ao dashboard.
- **Túnel SSH**: Se você consegue acessar o servidor via SSH, use `ssh -L 8080:localhost:8001
youruser@yourserver` para criar um túnel do dashboard para sua máquina local.
- **VPN**: Se seu servidor estiver atrás de uma VPN, conecte-se a ela e acesse o dashboard diretamente.
- **Configure o firewall**: Abra a porta 8001 para o seu IP ou para a rede do servidor (ou qualquer porta que você tenha definido).
- **Configure Apache/Nginx**: Se você tem um servidor web na frente da sua aplicação, pode configurá-lo para um domínio ou subdomínio. Aponte esse host para `dashboard/index.php` (o servidor embutido o usa como roteador). Arquivos estáticos ficam ao lado dele em `dashboard/css` e `dashboard/js`.
- **Autenticação básica**: defina `apm.dashboard_auth.user` e `apm.dashboard_auth.password` na configuração do runway. Se qualquer um dos valores estiver vazio, o dashboard permanece aberto. Esta é uma senha única compartilhada, não contas de usuário da aplicação.

#### Quer um dashboard diferente?

Você pode construir seu próprio dashboard se quiser! Dê uma olhada no diretório vendor/flightphp/apm/src/apm/presenter para ter ideias de como apresentar os dados no seu próprio dashboard!

## Recursos do dashboard

O dashboard abre com os números, depois os gráficos e então o log de requisições.

- **Linha de estatísticas**: contagem de requisições, taxa de erro, p95, p99, latência média, throughput por minuto e taxa de acerto de cache.
- **Acúmulo do worker**: quantas métricas brutas ainda estão em `apm_metrics_log`. Um número alto significa que o worker está atrasado. Um dashboard vazio com acúmulo de 0 significa que nada foi coletado.
- **Gráfico de latência**: latência média e contagem de requisições para os mesmos intervalos. A última hora usa intervalos de 5 minutos, o último dia usa intervalos de 15 minutos e a última semana usa intervalos de 6 horas. Um intervalo personalizado escolhe um bucket que mantém cerca de 40 pontos. Os horários são armazenados em UTC e desenhados no fuso horário que você escolher.
- **Gráfico de status**: contagens empilhadas de `2xx`, `3xx`, `4xx` e `5xx`. Clique em uma série para filtrar o log para essa classe.
- **Rotas, middleware, consultas e requisições lentas**: cinco de cada. As consultas são agrupadas, então `WHERE id = 1` e `WHERE id = 2` contam como uma única instrução. Clique em uma rota, uma requisição lenta ou um grupo de erros para filtrar o log.
- **Eventos personalizados**: cada tipo de evento personalizado que seu app dispara, com sua contagem, quando foi visto pela última vez e uma pequena linha de tendência para a janela selecionada. Classificados por contagem. Respeita o intervalo de tempo, Ocultar bots e os outros filtros. Clique em um tipo para filtrar o log por ele.
- **Log de requisições**: método, URL, status, tempo, memória, host e indicador de bot. Os detalhes carregam quando você abre a linha e incluem uma barra de tempo, visualizações, parâmetros de consulta, o rastreamento do erro e eventos personalizados.
- **Ocultar bots**: deixa o tráfego de bots fora das agregações e do log. Rastreadores de busca e rastreadores de LLM (GPTBot, ClaudeBot, PerplexityBot, SearxNG e outros) são marcados como bots.
- **Intervalo personalizado**: escolha uma janela de/para além da última hora, dia ou semana.
- **Filtros**: URL, status, ID da requisição, rota, IP, host, sessão, user agent, tipo de evento personalizado, dados do evento e mensagem de erro. A query string os mantém após uma atualização.

**Exemplo**:
Uma requisição para `/users` pode mostrar:
- Tempo total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Consulta: `SELECT * FROM users` (80ms)
- Tempo de renderização da view
- Cache: Hit em `user_list` (5ms)

**Exemplo**:
Uma requisição para `/users` pode mostrar:
- Tempo total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Consulta: `SELECT * FROM users` (80ms)
- Cache: Hit em `user_list` (5ms)

## Adicionando eventos personalizados

Rastreie qualquer coisa—como uma chamada de API ou um processo de pagamento:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Onde isso aparece?**
Nos detalhes da requisição do dashboard, em “Custom Events”—expansível com uma formatação JSON bonita. O painel **Custom events** também conta cada tipo para você, então um simples evento `docs.markdown_served` é tudo de que você precisa para responder “com que frequência isso acontece?”

**Caso de uso**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Agora você verá se essa API está deixando seu app mais lento!

## Monitoramento de banco de dados

Rastreie consultas PDO assim:

```php
use flight\database\SimplePdo;

$pdo = new SimplePdo('sqlite:/path/to/db.sqlite', null, null, null, [
	'trackApmQueries' => true, // obrigatório para capturar consultas para o APM
]);
$Apm->addPdoConnection($pdo);
```

**O que você obtém**:
- Texto da consulta (por exemplo, `SELECT * FROM users WHERE id = ?`)
- Tempo de execução (por exemplo, 0.015s)
- Contagem de linhas (por exemplo, 42)

**Atenção**:
- **Opcional**: Pule isto se você não precisa de rastreamento de BD.
- **SimplePdo (preferido)**: Use `SimplePdo` com `trackApmQueries => true`. O `PdoWrapper` obsoleto ainda funciona (5º argumento do construtor `true`). O PDO principal bruto ainda não está conectado—aguarde!
- **Aviso de desempenho**: Registrar todas as consultas em um site com muito uso de BD pode deixar as coisas mais lentas. Use amostragem (`$Apm = new Apm($ApmLogger, 0.1)`) para aliviar a carga.

**Exemplo de saída**:
- Consulta: `SELECT name FROM products WHERE price > 100`
- Tempo: 0.023s
- Linhas: 15

## Opções do worker

Ajuste o worker ao seu gosto:

- `--timeout 300`: Para após 5 minutos—bom para testes.
- `--max_messages 500`: Limita a 500 métricas—mantém finito.
- `--batch_size 200`: Processa 200 por vez—equilibra velocidade e memória.
- `--daemon`: Roda sem parar—ideal para monitoramento em tempo real.

**Exemplo**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Roda por uma hora, processando 100 métricas por vez.

## ID da requisição no app

Cada requisição tem um ID único para rastreamento. Você pode usar esse ID no seu app para correlacionar logs e métricas. Por exemplo, você pode adicionar o ID da requisição a uma página de erro:

```php
Flight::map('error', function($message) {
	// Obtenha o ID da requisição a partir do cabeçalho de resposta X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Além disso, você poderia obtê-lo da variável do Flight
	// Este método não funcionará bem em Swoole ou outras plataformas assíncronas.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## Atualização

Se você está atualizando para uma versão mais recente do APM, pode haver migrações de banco de dados que precisam ser executadas. Você pode fazer isso executando o seguinte comando:

```bash
php vendor/bin/runway apm:migrate
```
Isso executará quaisquer migrações necessárias para atualizar o esquema do banco de dados para a versão mais recente.

**Nota:** Se seu banco de dados do APM for grande, essas migrações podem levar algum tempo para executar. Execute-as durante um período de baixa atividade. O worker pode continuar rodando. Migrações de índice não reescrevem as linhas coletadas.

### Atualizando para além da 0.5.2 (dashboard e índices)

A 0.5.2 apenas ampliou a detecção de bots. Os arquivos de índice abaixo são incluídos na versão após a 0.5.2.

Faça backup do banco de dados de destino primeiro. Copie o arquivo SQLite ou use `mysqldump` para o esquema MySQL/MariaDB.

```bash
composer update flightphp/apm
php vendor/bin/runway apm:migrate
```

Isso aplica:

- SQLite: `0006-dest-indexes.sql` (os índices `request_dt` foram removidos quando `0005` reconstruiu `apm_requests`)
- MySQL/MariaDB: `0002-dest-indexes.sql` (`request_dt` e os outros filtros do dashboard)

`apm:migrate` registra o nome do arquivo e o ignora na próxima vez. Executá-lo duas vezes é seguro. `apm:purge` compara `request_dt` com um limite UTC, o que corresponde a como as linhas são armazenadas. A primeira purga após esta atualização pode manter uma janela diferente se o fuso horário do servidor não era UTC antes.

`X-Flight-Request-Id` corresponde ao `request_token` armazenado para essa requisição.

### Atualizando de 0.4.3 -> 0.5.0

Se você está atualizando de 0.4.3 para 0.5.0, precisará executar o seguinte comando:

```bash
php vendor/bin/runway apm:config-migrate
```

Isso migrará sua configuração do formato antigo usando o arquivo `.runway-config.json` para o novo formato, que armazena os pares chave/valor no arquivo `config.php`.

## Limpando dados antigos

Para manter seu banco de dados organizado, você pode limpar dados antigos. Isso é especialmente útil se você está executando um app movimentado e quer manter o tamanho do banco de dados gerenciável.
Você pode fazer isso executando o seguinte comando:

```bash
php vendor/bin/runway apm:purge
```
Isso removerá todos os dados com mais de 30 dias do banco de dados. Você pode ajustar o número de dias passando um valor diferente para a opção `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Isso removerá todos os dados com mais de 7 dias do banco de dados.

## Solução de problemas

Com problemas? Tente isto:

- **Sem dados no dashboard?**
  - O worker está rodando? Verifique `ps aux | grep apm:worker`.
  - Os caminhos da configuração correspondem? Verifique se os DSNs em `.runway-config.json` apontam para arquivos reais.
  - Execute `php vendor/bin/runway apm:worker` manualmente para processar métricas pendentes.

- **Erros no worker?**
  - Dê uma olhada nos seus arquivos SQLite (por exemplo, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Verifique os logs do PHP em busca de rastreamentos de pilha.

- **O dashboard não inicia?**
  - A porta 8001 está em uso? Use `--port 8080`.
  - PHP não encontrado? Use `--php-path /usr/bin/php`.
  - Firewall bloqueando? Abra a porta ou use `--host localhost`.

- **Muito lento?**
  - Reduza a taxa de amostragem: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Reduza o tamanho do lote: `--batch_size 20`.

- **Não está rastreando exceções/erros?**
  - Se você tiver o [Tracy](https://tracy.nette.org/) habilitado no seu projeto, ele substituirá o tratamento de erros do Flight. Você precisará desabilitar o Tracy e então garantir que `Flight::set('flight.handle_errors', true);` esteja definido.

- **Não está rastreando consultas ao banco de dados?**
  - Prefira `SimplePdo` com `['trackApmQueries' => true]` como o 5º argumento do construtor (array de opções).
  - Se você ainda usa o `PdoWrapper` obsoleto, passe `true` como o 5º argumento.
  - Chame `$Apm->addPdoConnection($pdo)` após criar a conexão.