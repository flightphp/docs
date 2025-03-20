# FlightPHP APM Documentation

Bem-vindo ao FlightPHP APM—o coach pessoal de desempenho da sua aplicação! Este guia é seu mapa para configurar, usar e dominar o Monitoramento de Desempenho de Aplicações (APM) com o FlightPHP. Seja você um caçador de requisições lentas ou apenas queira se empolgar com gráficos de latência, estamos aqui para ajudar. Vamos tornar sua aplicação mais rápida, seus usuários mais felizes e suas sessões de depuração um passeio no parque!

## Por que o APM importa

Imagine isto: sua aplicação é um restaurante movimentado. Sem uma maneira de rastrear quanto tempo os pedidos levam ou onde a cozinha está travando, você está adivinhando por que os clientes estão saindo irritados. O APM é seu sous-chef—ele observa cada passo, desde requisições recebidas até consultas ao banco de dados, e sinaliza qualquer coisa que esteja te atrasando. Páginas lentas perdem usuários (estudos dizem que 53% abandonam se um site leva mais de 3 segundos para carregar!), e o APM te ajuda a detectar esses problemas *antes* que eles te causem dor. É uma paz de espírito proativa—menos momentos de “por que isso está quebrado?”, mais vitórias de “veja como isso funciona bem!”.

## Instalação

Comece com o Composer:

```bash
composer require flightphp/apm
```

Você vai precisar de:
- **PHP 7.4+**: Mantém nossa compatibilidade com distribuições Linux LTS enquanto suporta PHP moderno.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: O framework leve que estamos potenciando.

## Começando

Aqui está seu passo a passo para a maravilha do APM:

### 1. Registre o APM

Adicione isto ao seu `index.php` ou a um arquivo `services.php` para começar a rastrear:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**O que está acontecendo aqui?**
- `LoggerFactory::create()` pega sua configuração (mais sobre isso em breve) e configura um logger—SQLite por padrão.
- `Apm` é a estrela—ele escuta os eventos do Flight (requisições, rotas, erros, etc.) e coleta métricas.
- `bindEventsToFlightInstance($app)` amarra tudo à sua aplicação Flight.

**Dica Pro: Amostragem**
Se sua aplicação estiver ocupada, registrar *todas* as requisições pode sobrecarregar as coisas. Use uma taxa de amostragem (0.0 a 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Registra 10% das requisições
```

Isso mantém o desempenho ágil enquanto ainda te dá dados sólidos.

### 2. Configure

Execute isto para criar seu `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**O que isso faz?**
- Lança um assistente perguntando de onde vêm as métricas brutas (origem) e para onde os dados processados vão (destino).
- O padrão é SQLite—por exemplo, `sqlite:/tmp/apm_metrics.sqlite` para origem, outro para destino.
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

**Por que dois locais?**
As métricas brutas se acumulam rapidamente (pense em logs não filtrados). O trabalhador as processa em um destino estruturado para o painel. Mantém as coisas organizadas!

### 3. Processar Métricas com o Trabalhador

O trabalhador transforma métricas brutas em dados prontos para o painel. Execute uma vez:

```bash
php vendor/bin/runway apm:worker
```

**O que está fazendo?**
- Lê a partir de sua origem (por exemplo, `apm_metrics.sqlite`).
- Processa até 100 métricas (tamanho do lote padrão) em seu destino.
- Para quando terminar ou se não houver mais métricas.

**Mantenha-o em funcionamento**
Para aplicativos ao vivo, você vai querer processamento contínuo. Aqui estão suas opções:

- **Modo Daemon**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Roda para sempre, processando métricas conforme elas chegam. Ótimo para desenvolvimento ou pequenas configurações.

- **Crontab**:
  Adicione isto ao seu crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Executa a cada minuto—perfeito para produção.

- **Tmux/Screen**:
  Inicie uma sessão destacável:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, depois D para destacar; `tmux attach -t apm-worker` para reconectar
  ```
  Mantém em funcionamento mesmo que você saia.

- **Ajustes Personalizados**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Processa 50 métricas por vez.
  - `--max_messages 1000`: Para após 1000 métricas.
  - `--timeout 300`: Sai após 5 minutos.

**Por que se preocupar?**
Sem o trabalhador, seu painel ficará vazio. Ele é a ponte entre logs brutos e insights acionáveis.

### 4. Lançar o Painel

Veja os vitais da sua aplicação:

```bash
php vendor/bin/runway apm:dashboard
```

**O que isso é?**
- Levanta um servidor PHP em `http://localhost:8001/apm/dashboard`.
- Mostra logs de requisições, rotas lentas, taxas de erro e mais.

**Personalize-o**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Acessível de qualquer IP (útil para visualização remota).
- `--port 8080`: Use uma porta diferente se 8001 já estiver em uso.
- `--php-path`: Aponte para o PHP se não estiver em seu PATH.

Acesse a URL no seu navegador e explore!

#### Modo de Produção

Para produção, você pode precisar tentar algumas técnicas para fazer o painel funcionar, já que provavelmente há firewalls e outras medidas de segurança em vigor. Aqui estão algumas opções:

- **Use um Proxy Reverso**: Configure o Nginx ou Apache para encaminhar requisições para o painel.
- **Túnel SSH**: Se você conseguir SSH na máquina, use `ssh -L 8080:localhost:8001 youruser@yourserver` para criar um túnel do painel para sua máquina local.
- **VPN**: Se seu servidor está por trás de uma VPN, conecte-se e acesse o painel diretamente.
- **Configurar Firewall**: Abra a porta 8001 para seu IP ou a rede do servidor. (ou qualquer que seja a porta que você configurar).
- **Configurar Apache/Nginx**: Se você tem um servidor web na frente de sua aplicação, pode configurá-lo para um domínio ou subdomínio. Se fizer isso, defina a raiz do documento como `/path/to/your/project/vendor/flightphp/apm/dashboard`

#### Quer um painel diferente?

Você pode construir seu próprio painel se quiser! Olhe no diretório vendor/flightphp/apm/src/apm/presenter para ideias de como apresentar os dados para seu próprio painel!

## Recursos do Painel

O painel é seu HQ do APM—aqui está o que você verá:

- **Log de Requisições**: Cada requisição com timestamp, URL, código de resposta e tempo total. Clique em “Detalhes” para middleware, consultas e erros.
- **Requisições Mais Lentas**: Top 5 requisições que consomem tempo (por exemplo, “/api/heavy” em 2.5s).
- **Rotas Mais Lentas**: Top 5 rotas por tempo médio—ótimo para identificar padrões.
- **Taxa de Erro**: Percentagem de requisições falhando (por exemplo, 2.3% 500s).
- **Percentis de Latência**: 95º (p95) e 99º (p99) tempos de resposta—saiba quais são seus piores cenários.
- **Gráfico de Códigos de Resposta**: Visualize 200s, 404s, 500s ao longo do tempo.
- **Consultas Longas/Middleware**: Top 5 chamadas lentas de banco de dados e camadas de middleware.
- **Cache Hit/Miss**: Com que frequência seu cache salva o dia.

**Extras**:
- Filtre por “Última Hora”, “Último Dia” ou “Última Semana”.
- Ative o modo escuro para aquelas sessões noturnas.

**Exemplo**:
Uma requisição para `/users` pode mostrar:
- Tempo Total: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Consulta: `SELECT * FROM users` (80ms)
- Cache: Hit em `user_list` (5ms)

## Adicionando Eventos Personalizados

Rastreie qualquer coisa—como uma chamada de API ou processo de pagamento:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->emit('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Onde isso aparece?**
Nos detalhes de requisições do painel sob “Eventos Personalizados”—expansível com formatação JSON bonita.

**Caso de Uso**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->emit('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Agora você verá se essa API está arrastando sua aplicação para baixo!

## Monitoramento de Banco de Dados

Rastreie consultas PDO assim:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**O que você recebe**:
- Texto da consulta (por exemplo, `SELECT * FROM users WHERE id = ?`)
- Tempo de execução (por exemplo, 0.015s)
- Contagem de linhas (por exemplo, 42)

**Atenção**:
- **Opcional**: Pule isto se você não precisar de rastreamento de banco de dados.
- **Somente PdoWrapper**: PDO Core ainda não está conectado—fique ligado!
- **Aviso de Desempenho**: Registrar cada consulta em um site com muito banco de dados pode desacelerar as coisas. Use amostragem (`$Apm = new Apm($ApmLogger, 0.1)`) para aliviar a carga.

**Saída de Exemplo**:
- Consulta: `SELECT name FROM products WHERE price > 100`
- Tempo: 0.023s
- Linhas: 15

## Opções do Trabalhador

Ajuste o trabalhador do seu jeito:

- `--timeout 300`: Para após 5 minutos—bom para testes.
- `--max_messages 500`: Limita a 500 métricas—mantém as coisas finitas.
- `--batch_size 200`: Processa 200 de uma vez—equilibra velocidade e memória.
- `--daemon`: Roda sem parar—ideal para monitoramento ao vivo.

**Exemplo**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Roda por uma hora, processando 100 métricas por vez.

## Resolução de Problemas

Trancado? Tente isto:

- **Sem Dados no Painel?**
  - O trabalhador está funcionando? Verifique `ps aux | grep apm:worker`.
  - Os caminhos de configuração correspondem? Verifique se os DSNs do `.runway-config.json` apontam para arquivos reais.
  - Execute `php vendor/bin/runway apm:worker` manualmente para processar métricas pendentes.

- **Erros de Trabalhador?**
  - Dê uma olhada nos seus arquivos SQLite (por exemplo, `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Verifique os logs do PHP por rastros de pilha.

- **Painel não Inicia?**
  - Porta 8001 em uso? Use `--port 8080`.
  - PHP não encontrado? Use `--php-path /usr/bin/php`.
  - Firewall bloqueando? Abra a porta ou use `--host localhost`.

- **Muito Lento?**
  - Reduza a taxa de amostragem: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Reduza o tamanho do lote: `--batch_size 20`.