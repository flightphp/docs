# Documentação do FlightPHP APM

Bem-vindo ao FlightPHP APM—seu treinador pessoal de desempenho de aplicativos! Este guia é o seu roteiro para configurar, usar e dominar o Monitoramento de Desempenho de Aplicativos (APM) com FlightPHP. Seja você caçando solicitações lentas ou apenas querendo se empolgar com gráficos de latência, cobrimos tudo. Vamos tornar seu aplicativo mais rápido, seus usuários mais felizes e suas sessões de depuração uma brisa!

## Por que o APM Importa

Imagine isto: seu aplicativo é um restaurante movimentado. Sem uma forma de rastrear quanto tempo levam os pedidos ou onde a cozinha está travando, você está chutando por que os clientes estão saindo irritados. O APM é o seu subchefe—ele observa cada passo, desde solicitações de entrada até consultas de banco de dados, e sinaliza qualquer coisa que esteja desacelerando você. Páginas lentas perdem usuários (estudos dizem que 53% abandonam se um site leva mais de 3 segundos para carregar!), e o APM ajuda a pegar esses problemas *antes* que eles machuquem. É uma paz de espírito proativa—menos momentos de “por que isso está quebrado?” e mais vitórias de “olha como isso roda bem!”.

## Instalação

Comece com o Composer:

```bash
composer require flightphp/apm
```

Você precisará de:
- **PHP 7.4+**: Mantém a compatibilidade com distribuições LTS do Linux enquanto suporta PHP moderno.
- **[Núcleo do FlightPHP](https://github.com/flightphp/core) v3.15+**: O framework leve que estamos aprimorando.

## Começando

Aqui está o seu passo a passo para o APM incrível:

### 1. Registrar o APM

Adicione isso ao seu `index.php` ou um arquivo `services.php` para começar a rastrear:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**O que está acontecendo aqui?**
- `LoggerFactory::create()` pega sua configuração (mais sobre isso em breve) e configura um logger—SQLite por padrão.
- `Apm` é a estrela—ele escuta os eventos do Flight (solicitações, rotas, erros, etc.) e coleta métricas.
- `bindEventsToFlightInstance($app)` liga tudo ao seu aplicativo Flight.

**Dica Profissional: Amostragem**
Se seu aplicativo estiver movimentado, registrar *toda* solicitação pode sobrecarregar as coisas. Use uma taxa de amostragem (de 0.0 a 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Registra 10% das solicitações
```

Isso mantém o desempenho ágil enquanto ainda fornece dados sólidos.

### 2. Configure-o

Execute isso para criar seu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**O que isso faz?**
- Inicia um assistente perguntando de onde vêm as métricas brutas (fonte) e para onde vão os dados processados (destino).
- O padrão é SQLite—por exemplo, `sqlite:/tmp/apm_metrics.sqlite` para a fonte, outro para o destino.
- Você acabará com uma configuração como:
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

> Esse processo também perguntará se você quer executar as migrações para essa configuração. Se você está configurando isso pela primeira vez, a resposta é sim.

**Por que dois locais?**
Métricas brutas se acumulam rapidamente (pense em logs não filtrados). O worker processa-as em um destino estruturado para o painel. Mantém tudo organizado!

### 3. Processar Métricas com o Worker

O worker transforma métricas brutas em dados prontos para o painel. Execute-o uma vez:

```bash
php vendor/bin/runway apm:worker
```

**O que ele está fazendo?**
- Lê da sua fonte (por exemplo, `apm_metrics.sqlite`).
- Processa até 100 métricas (tamanho do lote padrão) para o seu destino.
- Para quando terminar ou se não houver mais métricas.

**Mantenha-o em Execução**
Para aplicativos ao vivo, você vai querer processamento contínuo. Aqui estão suas opções:

- **Modo Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Executa para sempre, processando métricas à medida que chegam. Ótimo para desenvolvimento ou configurações pequenas.

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
  # Ctrl+B, depois D para destacar; `tmux attach -t apm-worker` para reconectar
  ```
  Mantém isso em execução mesmo se você sair.

- **Ajustes Personalizados**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Processa 50 métricas de cada vez.
  - `--max_messages 1000`: Para após 1000 métricas.
  - `--timeout 300`: Sai após 5 minutos.

**Por que se dar ao trabalho?**
Sem o worker, seu painel está vazio. É a ponte entre logs brutos e insights acionáveis.

### 4. Inicie o Painel

Veja os sinais vitais do seu aplicativo:

```bash
php vendor/bin/runway apm:dashboard
```

**O que é isso?**
- Inicia um servidor PHP em `http://localhost:8001/apm/dashboard`.
- Mostra logs de solicitações, rotas lentas, taxas de erros e mais.

**Personalize-o**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Acessível de qualquer IP (útil para visualização remota).
- `--port 8080`: Use uma porta diferente se 8001 estiver ocupada.
- `--php-path`: Aponte para o PHP se ele não estiver no seu PATH.

Acesse a URL no seu navegador e explore!

#### Modo de Produção

Para produção, você pode ter que tentar algumas técnicas para fazer o painel funcionar, pois provavelmente há firewalls e outras medidas de segurança. Aqui estão algumas opções:

- **Use um Proxy Inverso**: Configure o Nginx ou Apache para encaminhar solicitações para o painel.
- **Túnel SSH**: Se você puder fazer SSH no servidor, use `ssh -L 8080:localhost:8001 youruser@yourserver` para tunelizar o painel para sua máquina local.
- **VPN**: Se seu servidor estiver atrás de uma VPN, conecte-se a ela e acesse o painel diretamente.
- **Configure o Firewall**: Abra a porta 8001 para o seu IP ou a rede do servidor (ou qualquer porta que você definir).
- **Configure Apache/Nginx**: Se você tiver um servidor web na frente do seu aplicativo, você pode configurá-lo para um domínio ou subdomínio. Se fizer isso, defina a raiz do documento para `/path/to/your/project/vendor/flightphp/apm/dashboard`.

#### Quer um painel diferente?

Você pode criar seu próprio painel se quiser! Olhe no diretório vendor/flightphp/apm/src/apm/presenter para ideias sobre como apresentar os dados para o seu próprio painel!

## Recursos do Painel

O painel é o seu QG do APM—aqui está o que você verá:

- **Log de Solicitações**: Toda solicitação com carimbo de data/hora, URL, código de resposta e tempo total. Clique em “Detalhes” para middleware, consultas e erros.
- **Solicitações Mais Lentas**: As 5 principais solicitações consumindo tempo (por exemplo, “/api/heavy” em 2,5s).
- **Rotas Mais Lentas**: As 5 principais rotas pelo tempo médio—ótimo para identificar padrões.
- **Taxa de Erros**: Porcentagem de solicitações falhando (por exemplo, 2,3% de 500s).
- **Percentis de Latência**: 95º (p95) e 99º (p99) tempos de resposta—conheça seus cenários de pior caso.
- **Gráfico de Códigos de Resposta**: Visualize 200s, 404s, 500s ao longo do tempo.
- **Consultas/Middleware Longas**: As 5 principais chamadas de banco de dados lentas e camadas de middleware.
- **Acerto/Falha de Cache**: Com que frequência seu cache salva o dia.

**Extras**:
- Filtre por “Última Hora”, “Último Dia” ou “Última Semana”.
- Ative o modo escuro para aquelas sessões noturnas.

**Exemplo**:
Uma solicitação para `/users` pode mostrar:
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

**Onde isso aparece?**
Nos detalhes da solicitação do painel sob “Eventos Personalizados”—expandível com formatação JSON bonita.

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
Agora você verá se essa API está arrastando seu aplicativo para baixo!

## Monitoramento de Banco de Dados

Rastreie consultas PDO assim:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**O que Você Obtém**:
- Texto da consulta (por exemplo, `SELECT * FROM users WHERE id = ?`)
- Tempo de execução (por exemplo, 0.015s)
- Contagem de linhas (por exemplo, 42)

**Atenção**:
- **Opcional**: Pule isso se você não precisar de rastreamento de DB.
- **Apenas PdoWrapper**: PDO principal ainda não está conectado—fique atento!
- **Aviso de Desempenho**: Registrar toda consulta em um site pesado de DB pode desacelerar as coisas. Use amostragem (`$Apm = new Apm($ApmLogger, 0.1)`) para aliviar a carga.

**Saída de Exemplo**:
- Consulta: `SELECT name FROM products WHERE price > 100`
- Tempo: 0.023s
- Linhas: 15

## Opções do Worker

Ajuste o worker ao seu gosto:

- `--timeout 300`: Para após 5 minutos—bom para testes.
- `--max_messages 500`: Limita em 500 métricas—mantém finito.
- `--batch_size 200`: Processa 200 de uma vez—equilibra velocidade e memória.
- `--daemon`: Executa sem parar—ideal para monitoramento ao vivo.

**Exemplo**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Executa por uma hora, processando 100 métricas de cada vez.

## ID de Solicitação no Aplicativo

Cada solicitação tem um ID de solicitação único para rastreamento. Você pode usar esse ID no seu aplicativo para correlacionar logs e métricas. Por exemplo, você pode adicionar o ID de solicitação a uma página de erro:

```php
Flight::map('error', function($message) {
	// Obtenha o ID de solicitação do cabeçalho de resposta X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// Além disso, você poderia buscá-lo da variável Flight
	// Esse método não funciona bem em plataformas swoole ou outras assíncronas.
	// $requestId = Flight::get('apm.request_id');
	
	echo "Erro: $message (ID de Solicitação: $requestId)";
});
```

## Atualizando

Se você estiver atualizando para uma versão mais nova do APM, há uma chance de que haja migrações de banco de dados que precisam ser executadas. Você pode fazer isso executando o seguinte comando:

```bash
php vendor/bin/runway apm:migrate
```
Isso executará quaisquer migrações necessárias para atualizar o esquema do banco de dados para a versão mais recente.

**Nota:** Se o seu banco de dados do APM for grande, essas migrações podem levar algum tempo para serem executadas. Você pode querer executar esse comando durante horários de pico baixo.

## Excluindo Dados Antigos

Para manter seu banco de dados organizado, você pode excluir dados antigos. Isso é especialmente útil se você estiver executando um aplicativo movimentado e quiser manter o tamanho do banco de dados gerenciável.
Você pode fazer isso executando o seguinte comando:

```bash
php vendor/bin/runway apm:purge
```
Isso removerá todos os dados mais antigos que 30 dias do banco de dados. Você pode ajustar o número de dias passando um valor diferente para a opção `--days`:

```bash
php vendor/bin/runway apm:purge --days 7
```
Isso removerá todos os dados mais antigos que 7 dias do banco de dados.

## Solução de Problemas

Preso? Tente isso:

- **Nenhum Dado no Painel?**
  - O worker está em execução? Verifique `ps aux | grep apm:worker`.
  - Os caminhos de configuração correspondem? Verifique se os DSNs em `.runway-config.json` apontam para arquivos reais.
  - Execute `php vendor/bin/runway apm:worker` manualmente para processar métricas pendentes.

- **Erros no Worker?**
  - Dê uma olhada nos seus arquivos SQLite (por exemplo, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Verifique os logs do PHP para rastreamentos de pilha.

- **Painel Não Inicia?**
  - A porta 8001 está em uso? Use `--port 8080`.
  - PHP não encontrado? Use `--php-path /usr/bin/php`.
  - Firewall bloqueando? Abra a porta ou use `--host localhost`.

- **Muito Lento?**
  - Reduza a taxa de amostragem: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Reduza o tamanho do lote: `--batch_size 20`.