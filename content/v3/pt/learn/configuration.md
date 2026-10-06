# Configuração

## Visão geral

O Flight fornece uma maneira simples de configurar vários aspectos do framework para atender às necessidades da sua aplicação. Alguns são definidos por padrão, mas você pode sobrescrevê-los conforme necessário. Você também pode definir suas próprias variáveis para serem usadas em toda a sua aplicação.

Uma configuração clara e em camadas (padrões de arquivo + segredos de ambiente) também ajuda [ferramentas de codificação de IA](/learn/ai): os agentes aprendem um lugar para literais e um lugar para segredos, em vez de inventar leituras `$_ENV` dentro dos controladores.

## Entendimento

Você pode personalizar certos comportamentos do Flight definindo valores de configuração através do método `set`.

```php
Flight::set('flight.log_errors', true);
```

Em uma aplicação estruturada (incluindo o [skeleton](https://github.com/flightphp/skeleton)), você normalmente carrega as configurações do projeto a partir de `app/config/config.php` e depois aplica as chaves relevantes ao Engine (por exemplo, `flight.base_url`, `flight.views.path`). Você também pode injetar um pequeno objeto de configuração nos controladores em vez de ler variáveis globais em todos os lugares—mais amigável para testes e para agentes que seguem `AGENTS.md`.

## Uso Básico

### Opções de Configuração do Flight

A seguir está uma lista de todas as configurações disponíveis:

- **flight.base_url** `?string` - Substitui a URL base da requisição se o Flight estiver rodando em um subdiretório. (padrão: null)
- **flight.case_sensitive** `bool` - Correspondência sensível a maiúsculas/minúsculas para URLs. (padrão: false)
- **flight.handle_errors** `bool` - Permite que o Flight lide com todos os erros internamente. (padrão: true)
  - Se você quiser que o Flight lide com os erros em vez do comportamento padrão do PHP, isso precisa ser true.
  - Se você tiver o [Tracy](/awesome-plugins/tracy) instalado, defina isso como false para que o Tracy possa lidar com os erros.
  - Se você tiver o plugin [APM](/awesome-plugins/apm) instalado, defina isso como true para que o APM possa registrar os erros.
- **flight.log_errors** `bool` - Registra erros no arquivo de log de erros do servidor web. (padrão: false)
  - Se você tiver o [Tracy](/awesome-plugins/tracy) instalado, o Tracy registrará erros com base nas configurações do Tracy, não nesta configuração.
- **flight.debug** `bool` - Exibe informações detalhadas de erro (mensagem de exceção, código e rastreamento de pilha) no navegador quando ocorre um erro. (padrão: false)
  - **Nunca ative isso em produção** — isso vaza detalhes internos da aplicação. Use apenas para desenvolvimento local ou staging.
  - Quando `false`, um `500 Internal Server Error` genérico é exibido. Combine com `flight.log_errors` para capturar erros no lado do servidor.
- **flight.allow_method_override** `bool` - Permite que o método HTTP seja sobrescrito via cabeçalho de requisição `X-HTTP-Method-Override` ou um campo `_method` no corpo POST. (padrão: true)
  - **Definir isso como `false` é recomendado** para aplicações que não precisam de falsificação de método baseada em formulários HTML, pois impede que clientes forjem requisições `DELETE` ou `PUT` por meio de um formulário POST padrão.
  - Consulte [Segurança](/learn/security#flight-configuration-hardening) para mais detalhes.
- **flight.views.path** `string` - Diretório contendo os arquivos de template de view. (padrão: ./views)
- **flight.views.extension** `string` - Extensão de arquivo de template de view. (padrão: `.php`; o skeleton oficial define isso como `.twig` ao usar Twig)
- **flight.views.restrict_to_path** `bool` - Quando `true`, a `View` nativa do Flight aceita apenas arquivos de template que resolvem dentro de `flight.views.path`. (padrão: `false`). **Ative isso** para aplicações que usam views nativas. Consulte [Segurança](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` - Define o cabeçalho `Content-Length`. (padrão: true)
  - Se você estiver usando [Tracy](/awesome-plugins/tracy), isso precisa ser definido como false para que o Tracy possa renderizar corretamente.
- **flight.v2.output_buffering** `bool` - Usa o buffer de saída legado. Consulte [migrando para v3](migrating-to-v3). (padrão: false)

### Configuração do Loader

Existe adicionalmente outra configuração para o loader. Isso permitirá que você autocarregue classes com `_` no nome da classe.

```php
// Habilita o carregamento de classes com sublinhados
// Padrão é true
Loader::$v2ClassLoading = false;
```

Lembre-se de que o [autoloading](/learn/autoloading) também depende da **caixa das pastas** (maiúsculas/minúsculas) corresponder aos seus namespaces—especialmente com o layout `App\` + `app/Controller/` do skeleton.

### Configuração do projeto e `.env` (padrão do skeleton)

O núcleo do Flight não exige arquivos `.env`. Muitas aplicações usam apenas um array de configuração PHP. O skeleton oficial organiza a configuração em camadas para que os segredos não fiquem no git, enquanto o Runway ainda pode reescrever a configuração **literal** com segurança:

1. **`.env` / ambiente real** — segredos e sobrescritas de deploy (ignorados pelo git).
2. **`app/config/config.php`** — padrões literais de array PHP (copiados de `config_sample.php`). Prefira **não** usar expressões `$_ENV[...]` dentro deste arquivo: ferramentas como `runway config:set` podem reescrevê-lo como valores estáticos e podem embutir segredos no arquivo.
3. **Mesclagem na inicialização** — o ambiente vence para chaves mapeadas; o código da aplicação lê um objeto de configuração ou `$app->get()`, não `$_ENV` nos controladores.

Exemplo de formato de `config_sample.php` / `config.php` (simplificado):

```php
<?php
// Apenas literais — segredos pertencem ao .env no fluxo do skeleton
return [
	'app' => [
		'env' => 'development',
		'debug' => true,
		'base_url' => '/',
		'timezone' => 'UTC',
	],
	'database' => [
		'driver' => 'sqlite', // ou mysql, ou '' para desabilitar
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'file_path' => __DIR__ . '/../../database.sqlite',
	],
	// ...
];
```

```bash
# .env.example → .env (skeleton)
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

Essa divisão é proposital para [projetos compatíveis com IA](/learn/ai): as instruções podem dizer “padrões em `config.php`, segredos em `.env`, injete Config / Engine—nunca invente acesso ao env em um controlador.” Aplicações existentes podem ignorar `.env` completamente e manter um único arquivo de configuração.

### Variáveis

O Flight permite que você salve variáveis para que elas possam ser usadas em qualquer lugar da sua aplicação.

```php
// Salve sua variável
Flight::set('id', 123);

// Em outro lugar da sua aplicação
$id = Flight::get('id');
```
Para verificar se uma variável foi definida, você pode fazer:

```php
if (Flight::has('id')) {
  // Faça algo
}
```

Você pode limpar uma variável fazendo:

```php
// Limpa a variável id
Flight::clear('id');

// Limpa todas as variáveis
Flight::clear();
```

> **Nota:** Só porque você pode definir uma variável não significa que deva. Use esse recurso com moderação. A razão é que qualquer coisa armazenada aqui se torna uma variável global. Variáveis globais são ruins porque podem ser alteradas de qualquer lugar na sua aplicação, dificultando rastrear bugs. Além disso, isso pode complicar coisas como [testes unitários](/guides/unit-testing). Prefira injeção via construtor (como no setup do skeleton + Dice) para serviços e configuração que os controladores precisam.

### Erros e Exceções

Todos os erros e exceções são capturados pelo Flight e passados para o método `error`, se `flight.handle_errors` estiver definido como true.

O comportamento padrão é enviar uma resposta genérica `HTTP 500 Internal Server Error` com algumas informações de erro.

Você pode [sobrescrever](/learn/extending) esse comportamento conforme suas necessidades:

```php
Flight::map('error', function (Throwable $error) {
  // Lida com o erro
  echo $error->getTraceAsString();
});
```

Por padrão, os erros não são registrados no servidor web. Você pode habilitar isso alterando a configuração:

```php
Flight::set('flight.log_errors', true);
```

#### 404 Não Encontrado

Quando uma URL não pode ser encontrada, o Flight chama o método `notFound`. O comportamento padrão é enviar uma resposta `HTTP 404 Not Found` com uma mensagem simples.

Você pode [sobrescrever](/learn/extending) esse comportamento conforme suas necessidades:

```php
Flight::map('notFound', function () {
  // Lida com não encontrado
});
```

## Veja Também
- [Instalação](/install) - Configuração do skeleton, `.env` e layout de inicialização.
- [Autoloading](/learn/autoloading) - Namespaces e caixa das pastas.
- [Estendendo o Flight](/learn/extending) - Como estender e personalizar a funcionalidade principal do Flight.
- [Testes Unitários](/guides/unit-testing) - Como escrever testes unitários para sua aplicação Flight.
- [IA & Experiência do Desenvolvedor](/learn/ai) - `AGENTS.md` e instruções consistentes do projeto.
- [Tracy](/awesome-plugins/tracy) - Um plugin para tratamento avançado de erros e depuração.
- [Extensões do Tracy](/awesome-plugins/tracy_extensions) - Extensões para integrar o Tracy com o Flight.
- [APM](/awesome-plugins/apm) - Um plugin para monitoramento de desempenho de aplicações e rastreamento de erros.
- [Segurança](/learn/security) - Flags de endurecimento e manipulação de segredos.

## Solução de Problemas
- Se você estiver tendo problemas para descobrir todos os valores da sua configuração, pode usar `var_dump(Flight::get());`
- Se o Runway ou ferramentas de deploy reescreveram `config.php`, confirme se os segredos não foram commitados—mantenha-os em `.env` ou no ambiente real ao usar o padrão do skeleton.

## Histórico de Alterações
- Documentação – Observado `flight.views.restrict_to_path` ao lado das configurações de caminho de view.
- Documentação – Documentada a configuração em camadas de `config.php` / `.env` no estilo do skeleton e a extensão de view Twig padrão para novos projetos.
- v3.18.1 - Adicionadas as opções de configuração `flight.debug` e `flight.allow_method_override`.
- v3.5.0 - Adicionada a configuração para `flight.v2.output_buffering` para suportar o comportamento legado de buffer de saída.
- v2.0 - Configurações principais adicionadas.