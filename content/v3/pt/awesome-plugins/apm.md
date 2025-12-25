# Documentação do APM do FlightPHP

Bem-vindo ao FlightPHP APM—o treinador de desempenho pessoal do seu app! Este guia é o seu mapa para configurar, usar e dominar o Application Performance Monitoring (APM) com o FlightPHP. Seja caçando requisições lentas ou apenas querendo se empolgar com gráficos de latência, nós cobrimos tudo. Vamos tornar o seu app mais rápido, seus usuários mais felizes e suas sessões de depuração uma brisa!

Veja uma [demo](https://flightphp-docs-apm.sky-9.com/apm/dashboard) do dashboard para o site Flight Docs.

![FlightPHP APM](/images/apm.png)

## Por que o APM Importa

Imagine isso: o seu app é um restaurante movimentado. Sem uma forma de rastrear quanto tempo os pedidos demoram ou onde a cozinha está travando, você está adivinhando por que os clientes estão saindo de mau humor. O APM é o seu sous-chef—ele observa cada passo, desde requisições de entrada até consultas de banco de dados, e sinaliza qualquer coisa que esteja te atrasando. Páginas lentas perdem usuários (estudos dizem que 53% abandonam se um site demora mais de 3 segundos para carregar!), e o APM te ajuda a capturar esses problemas *antes* que eles doam. É uma paz de espírito proativa—menos momentos de “por que isso está quebrado?”, mais vitórias de “olha como isso roda suave!”.

## Instalação

Comece com o Composer:

```bash
composer require flightphp/apm
```

Você precisará de:
- **PHP 7.4+**: Mantém compatibilidade com distros Linux LTS enquanto suporta PHP moderno.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: O framework leve que estamos impulsionando.

## Bancos de Dados Suportados

O FlightPHP APM atualmente suporta os seguintes bancos de dados para armazenar métricas:

- **SQLite3**: Simples, baseado em arquivo, e ótimo para desenvolvimento local ou apps pequenos. Opção padrão na maioria das configurações.
- **MySQL/MariaDB**: Ideal para projetos maiores ou ambientes de produção onde você precisa de armazenamento robusto e escalável.

Você pode escolher o tipo de banco de dados durante o passo de configuração (veja abaixo). Certifique-se de que o seu ambiente PHP tenha as extensões necessárias instaladas (ex.: `pdo_sqlite` ou `pdo_mysql`).

## Primeiros Passos

Aqui está o seu passo a passo para o APM incrível:

### 1. Registrar o APM

Adicione isso no seu `index.php` ou arquivo `services.php` para começar o rastreamento:

```php
use flight\apm\logger\LoggerFactory;
use flight\database\PdoWrapper;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// Se você estiver adicionando uma conexão de banco de dados
// Deve ser PdoWrapper ou PdoQueryCapture das Extensões Tracy
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True obrigatório para habilitar o rastreamento no APM.
$Apm->addPdoConnection($pdo);
```

**O que está acontecendo aqui?**
- `LoggerFactory::create()` pega a sua configuração (mais sobre isso em breve) e configura um logger—SQLite por padrão.
- `Apm` é a estrela—ele escuta os eventos do Flight (requisições, rotas, erros, etc.) e coleta métricas.
- `bindEventsToFlightInstance($app)` conecta tudo ao seu app Flight.

**Dica Pro: Amostragem**
Se o seu app estiver ocupado, registrar *todas* as requisições pode sobrecarregar as coisas. Use uma taxa de amostragem (0.0 a 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Registra 10% das requisições
```

Isso mantém o desempenho ágil enquanto ainda te dá dados sólidos.

### 2. Configure-o

Execute isso para criar o seu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**O que isso faz?**
- Inicia um assistente perguntando de onde vêm as métricas brutas (fonte) e para onde vão os dados processados (destino).
- Padrão é SQLite—ex.: `sqlite:/tmp/apm_metrics.sqlite` para fonte, outro para destino.
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

> Esse processo também perguntará se você quer executar as migrações para essa configuração. Se você estiver configurando pela primeira vez, a resposta é sim.

**Por que dois locais?**
Métricas brutas se acumulam rápido (pense em logs não filtrados). O worker as processa em um destino estruturado para o dashboard. Mantém as coisas organizadas!

### 3. Processar Métricas com o Worker

O worker transforma métricas brutas em dados prontos para o dashboard. Execute uma vez:

```bash
php vendor/bin/runway apm:worker
```

**O que ele está fazendo?**
- Lê da sua fonte (ex.: `apm_metrics.sqlite`).
- Processa até 100 métricas (tamanho de lote padrão) no seu destino.
- Para quando termina ou se não há métricas restantes.

**Mantenha-o Rodando**
Para apps ao vivo, você vai querer processamento contínuo. Aqui estão suas opções:

- **Modo Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Roda para sempre, processando métricas à medida que chegam. Ótimo para dev ou configurações pequenas.

- **Crontab**:
  Adicione isso ao seu crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Dispara a cada minuto—perfeito para produção.

- **Tmux/Screen**:
  Inicie uma sessão destacável:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, então D para destacar; `tmux attach -t apm-worker` para reconectar
  ```
  Mantém rodando mesmo se você sair.

- **Ajustes Personalizados**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Processa 50 métricas de uma vez.
  - `--max_messages 1000`: Para após 1000 métricas.
  - `--timeout 300`: Sai após 5 minutos.

**Por que se preocupar?**
Sem o worker, o seu dashboard fica vazio. É a ponte entre logs brutos e insights acionáveis.

### 4. Iniciar o Dashboard

Veja os vitais do seu app:

```bash
php vendor/bin/runway apm:dashboard
```

**O que é isso?**
- Inicia um servidor PHP em `http://localhost:8001/apm/dashboard`.
- Mostra logs de requisições, rotas lentas, taxas de erro e mais.

**Personalize-o**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Acessível de qualquer IP (útil para visualização remota).
- `--port 8080`: Use uma porta diferente se 8001 estiver ocupada.
- `--php-path`: Aponta para o PHP se não estiver no seu PATH.

Acesse a URL no seu navegador e explore!

#### Modo Produção

Para produção, você pode precisar tentar algumas técnicas para fazer o dashboard rodar, já que provavelmente há firewalls e outras medidas de segurança no lugar. Aqui estão algumas opções:

- **Use um Proxy Reverso**: Configure o Nginx ou Apache para encaminhar requisições ao dashboard.
- **Túnel SSH**: Se você puder SSH no servidor, use `ssh -L 8080:localhost:8001 youruser@yourserver` para tunelar o dashboard para a sua máquina local.
- **VPN**: Se o seu servidor estiver atrás de uma VPN, conecte-se a ela e acesse o dashboard diretamente.
- **Configure o Firewall**: Abra a porta 8001 para o seu IP ou a rede do servidor. (ou qualquer porta que você definiu).
- **Configure Apache/Nginx**: Se você tiver um servidor web na frente do seu aplicativo, você pode configurá-lo para um domínio ou subdomínio. Se fizer isso, você definirá o document root para `/path/to/your/project/vendor/flightphp/apm/dashboard`

#### Quer um dashboard diferente?

Você pode construir o seu próprio dashboard se quiser! Olhe o diretório vendor/flightphp/apm/src/apm/presenter para ideias de como apresentar os dados para o seu próprio dashboard!

## Recursos do Dashboard

O dashboard é o seu QG do APM—aqui está o que você verá:

- **Log de Requisições**: Toda requisição com timestamp, URL, código de resposta e tempo total. Clique em “Detalhes” para middleware, consultas e erros.
- **Requisições Mais Lentas**: Top 5 requisições consumindo tempo (ex.: “/api/heavy” em 2.5s).
- **Rotas Mais Lentas**: Top 5 rotas por tempo médio—ótimo para detectar padrões.
- **Taxa de Erro**: Percentual de requisições falhando (ex.: 2.3% 500s).
- **Percentis de Latência**: 95º (p95) e 99º (p99) tempos de resposta—conheça seus cenários de pior caso.
- **Gráfico de Código de Resposta**: Visualize 200s, 404s, 500s ao longo do tempo.
- **Consultas/Middleware Longas**: Top 5 chamadas de banco de dados lentas e camadas de middleware.
- **Acerto/Falha de Cache**: Com que frequência o seu cache salva o dia.

**Extras**:
- Filtre por “Última Hora”, “Último Dia” ou “Última Semana”.
- Ative o modo escuro para aquelas sessões noturnas.

**Exemplo**:
Uma requisição para `/users` pode mostrar:
- Tempo Total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Consulta: `SELECT * FROM users` (80ms)
- Cache: Acerto em `user_list` (5ms)

## Adicionando Eventos Personalizados

Rastreie qualquer coisa—como uma chamada de API ou processo de pagamento:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Onde aparece?**
Nos detalhes de requisição do dashboard sob “Eventos Personalizados”—expandível com formatação JSON bonita.

**Caso de Uso**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Agora você verá se aquela API está arrastando o seu app para baixo!

## Monitoramento de Banco de Dados

Rastreie consultas PDO assim:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True obrigatório para habilitar o rastreamento no APM.
$Apm->addPdoConnection($pdo);
```

**O Que Você Obtém**:
- Texto da consulta (ex.: `SELECT * FROM users WHERE id = ?`)
- Tempo de execução (ex.: 0.015s)
- Contagem de linhas (ex.: 42)

**Atenção**:
- **Opcional**: Pule isso se não precisar de rastreamento de BD.
- **Apenas PdoWrapper**: PDO core ainda não está conectado—fique ligado!
- **Aviso de Desempenho**: Registrar toda consulta em um site pesado de BD pode desacelerar as coisas. Use amostragem (`$Apm = new Apm($ApmLogger, 0.1)`) para aliviar a carga.

**Saída de Exemplo**:
- Consulta: `SELECT name FROM products WHERE price > 100`
- Tempo: 0.023s
- Linhas: 15

## Opções do Worker

Ajuste o worker ao seu gosto:

- `--timeout 300`: Para após 5 minutos—bom para testes.
- `--max_messages 500`: Limita a 500 métricas—mantém finito.
- `--batch_size 200`: Processa 200 de uma vez—equilibra velocidade e memória.
- `--daemon`: Roda sem parar—ideal para monitoramento ao vivo.

**Exemplo**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Roda por uma hora, processando 100 métricas de uma vez.

## ID de Requisição no App

Cada requisição tem um ID de requisição único para rastreamento. Você pode usar esse ID no seu app para correlacionar logs e métricas. Por exemplo, você pode adicionar o ID de requisição a uma página de erro:

```php
Flight::map('error', function($message) {
	// Obtém o ID de requisição do cabeçalho de resposta X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Adicionalmente, você poderia buscá-lo da variável Flight
	// Esse método não funcionará bem em swoole ou outras plataformas async.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Erro: $message (ID de Requisição: $requestId)";
});
```

## Atualizando

Se você estiver atualizando para uma versão mais nova do APM, há uma chance de que haja migrações de banco de dados que precisam ser executadas. Você pode fazer isso executando o seguinte comando:

```bash
php vendor/bin/runway apm:migrate
```
Isso executará quaisquer migrações necessárias para atualizar o esquema do banco de dados para a versão mais recente.

**Nota:** Se o seu banco de dados APM for grande em tamanho, essas migrações podem demorar um pouco para rodar. Você pode querer executar esse comando durante horários de baixa demanda.

### Atualizando de 0.4.3 -> 0.5.0

Se você estiver atualizando de 0.4.3 para 0.5.0, você precisará executar o seguinte comando:

```bash
php vendor/bin/runway apm:config-migrate
```

Isso migrará a sua configuração do formato antigo usando o arquivo `.runway-config.json` para o novo formato que armazena as chaves/valores no arquivo `config.php`.

## Limpando Dados Antigos

Para manter o seu banco de dados organizado, você pode limpar dados antigos. Isso é especialmente útil se você estiver rodando um app movimentado e quiser manter o tamanho do banco de dados gerenciável.
Você pode fazer isso executando o seguinte comando:

```bash
php vendor/bin/runway apm:purge
```
Isso removerá todos os dados mais antigos que 30 dias do banco de dados. Você pode ajustar o número de dias passando um valor diferente para a opção `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Isso removerá todos os dados mais antigos que 7 dias do banco de dados.

## Resolução de Problemas

Travou? Tente estes:

- **Sem Dados no Dashboard?**
  - O worker está rodando? Verifique `ps aux | grep apm:worker`.
  - Caminhos de configuração batem? Verifique se os DSNs em `.runway-config.json` apontam para arquivos reais.
  - Execute `php vendor/bin/runway apm:worker` manualmente para processar métricas pendentes.

- **Erros no Worker?**
  - Dê uma olhada nos seus arquivos SQLite (ex.: `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Verifique os logs do PHP para stack traces.

- **Dashboard Não Inicia?**
  - Porta 8001 em uso? Use `--port 8080`.
  - PHP não encontrado? Use `--php-path /usr/bin/php`.
  - Firewall bloqueando? Abra a porta ou use `--host localhost`.

- **Muito Lento?**
  - Reduza a taxa de amostragem: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Reduza o tamanho do lote: `--batch_size 20`.

- **Não Rastreando Exceções/Erros?**
  - Se você tiver [Tracy](https://tracy.nette.org/) habilitado para o seu projeto, ele sobrescreverá o tratamento de erros do Flight. Você precisará desabilitar o Tracy e então garantir que `Flight::set('flight.handle_errors', true);` esteja definido.

- **Não Rastreando Consultas de Banco de Dados?**
  - Garanta que você esteja usando `PdoWrapper` para as suas conexões de banco de dados.
  - Certifique-se de que você esteja definindo o último argumento no construtor como `true`.