# Configuração

## Visão geral

O Flight fornece uma maneira simples de configurar vários aspectos do framework para atender às necessidades da sua aplicação. Alguns são definidos por padrão, mas você pode substituí-los conforme necessário. Você também pode definir suas próprias variáveis para serem usadas em toda a sua aplicação.

Configuração clara e em camadas (padrões de arquivo + segredos de ambiente) também ajuda [ferramentas de programação com IA](/learn/ai): agentes aprendem um lugar para literais e um lugar para segredos, em vez de inventar leituras de `$_ENV` dentro de controllers.

## Compreensão

Você pode personalizar certos comportamentos do Flight definindo valores de configuração por meio do método `set`.

```php
Flight::set('flight.log_errors', true);
```

Em uma app estruturada (incluindo o [skeleton](https://github.com/flightphp/skeleton)), você normalmente carrega as configurações do projeto de `app/config/config.php` e então aplica as chaves relevantes no Engine (por exemplo, `flight.base_url`, `flight.views.path`). Você também pode injetar um pequeno objeto de configuração nos controllers em vez de ler globais em todo lugar — mais amigável para testes e para agentes que seguem `AGENTS.md`.

## Uso básico

### Opções de configuração do Flight

A seguir está uma lista de todas as configurações disponíveis:

- **flight.base_url** `?string` - Substitui a URL base da requisição se o Flight estiver sendo executado em um subdiretório. (padrão: null)
- **flight.case_sensitive** `bool` - Correspondência com diferenciação de maiúsculas e minúsculas para URLs. (padrão: false)
- **flight.handle_errors** `bool` - Permite que o Flight trate todos os erros internamente. (padrão: true)
  - Se você quiser que o Flight trate erros em vez do comportamento padrão do PHP, isto precisa ser true.
  - Se você tiver [Tracy](/awesome-plugins/tracy) instalado, você deve definir isto como false para que o Tracy possa tratar os erros.
  - Se você tiver o plugin [APM](/awesome-plugins/apm) instalado, você deve definir isto como true para que o APM possa registrar os erros.
- **flight.log_errors** `bool` - Registra erros no arquivo de log de erros do servidor web. (padrão: false)
  - Se você tiver [Tracy](/awesome-plugins/tracy) instalado, o Tracy registrará erros com base nas configurações do Tracy, não nesta configuração.
- **flight.debug** `bool` - Exibe informações detalhadas de erro (mensagem da exceção, código e stack trace) no navegador quando ocorre um erro. (padrão: false)
  - **Nunca habilite isto em produção** — isso vaza detalhes internos da aplicação. Use apenas para desenvolvimento local ou homologação.
  - Quando `false`, uma resposta genérica `500 Internal Server Error` é exibida. Combine com `flight.log_errors` para capturar erros no lado do servidor.
- **flight.allow_method_override** `bool` - Permite que o método HTTP seja substituído pelo cabeçalho de requisição `X-HTTP-Method-Override` ou por um campo `_method` no corpo POST. (padrão: true)
  - **Definir isto como `false` é recomendado** para aplicações que não precisam de falsificação de método baseada em formulários HTML, pois impede que clientes forjem requisições `DELETE` ou `PUT` por meio de um formulário POST padrão.
  - Veja [Segurança](/learn/security#flight-configuration-hardening) para mais detalhes.
- **flight.views.path** `string` - Diretório contendo arquivos de template de view. (padrão: ./views)
- **flight.views.extension** `string` - Extensão de arquivo de template de view. (padrão: `.php`; o skeleton oficial define isto como `.twig` ao usar Twig)
- **flight.views.restrict_to_path** `bool` - Quando `true`, a `View` nativa do Flight aceita apenas arquivos de template que são resolvidos dentro de `flight.views.path`. (padrão: `false`). **Ative isto** para apps que usam views nativas. Veja [Segurança](/learn/security#flightviewsrestrict_to_path).
- **flight.content_length** `bool` - Define o cabeçalho `Content-Length`. (padrão: true)
  - Se você estiver usando [Tracy](/awesome-plugins/tracy), isto precisa ser definido como false para que o Tracy possa renderizar corretamente.
- **flight.v2.output_buffering** `bool` - Usa buffer de saída legado. Veja [migrando para v3](migrating-to-v3). (padrão: false)

### Configuração do Loader

Há também outra configuração para o loader. Ela permitirá que você faça autoload de classes com `_` no nome da classe.

```php
// Ativa o carregamento de classes com sublinhados
// Padrão definido como true
Loader::$v2ClassLoading = false;
```

Lembre-se de que o [autoloading](/learn/autoloading) também depende do **uso de maiúsculas/minúsculas em pastas** correspondendo aos seus namespaces — especialmente com o layout `App\` + `app/Controller/` do skeleton.

### Configuração do projeto e `.env` (padrão skeleton)

O núcleo do Flight não exige arquivos `.env`. Muitos apps usam apenas um array de configuração PHP. O skeleton oficial organiza a configuração em camadas para que segredos fiquem fora do git enquanto o Runway ainda pode reescrever configurações **literais** com segurança:

1. **`.env` / ambiente real** — segredos e substituições de implantação (ignorados pelo git).
2. **`app/config/config.php`** — padrões literais em array PHP (copiados de `config_sample.php`). Prefira **não** usar expressões `$_ENV[...]` dentro deste arquivo: ferramentas como `runway config:set` podem reescrevê-lo como valores estáticos e podem incorporar segredos ao arquivo.
3. **Mesclagem no bootstrap** — o ambiente prevalece para chaves mapeadas; o código do app lê um objeto de configuração ou `$app->get()`, não `$_ENV` em controllers.

Exemplo de formato de `config_sample.php` / `config.php` (simplificado):

```php
<?php
// Apenas literais — segredos pertencem ao .env para o fluxo de trabalho do skeleton
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

Essa separação é intencional para [projetos amigáveis à IA](/learn/ai): as instruções podem dizer “padrões em `config.php`, segredos em `.env`, injete Config / Engine — nunca invente acesso ao ambiente em um controller.” Apps existentes podem ignorar `.env` completamente e manter um único arquivo de configuração.

### Variáveis

O Flight permite salvar variáveis para que possam ser usadas em qualquer lugar da sua aplicação.

```php
// Salve sua variável
Flight::set('id', 123);

// Em outro lugar da sua aplicação
$id = Flight::get('id');
```
Para ver se uma variável foi definida, você pode fazer:

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

> **Nota:** Só porque você pode definir uma variável não significa que deveria. Use este recurso com moderação. O motivo é que qualquer coisa armazenada aqui se torna uma variável global. Variáveis globais são ruins porque podem ser alteradas de qualquer lugar da sua aplicação, dificultando a localização de bugs. Além disso, isso pode complicar coisas como [testes unitários](/guides/unit-testing). Prefira injeção via construtor (como na configuração do skeleton + Dice) para serviços e configuração de que os controllers precisam.

### Erros e exceções

Todos os erros e exceções são capturados pelo Flight e passados para o método `error`.
Se `flight.handle_errors` estiver definido como true.

O comportamento padrão é enviar uma resposta genérica `HTTP 500 Internal Server Error`
com algumas informações de erro.

Você pode [substituir](/learn/extending) esse comportamento para suas próprias necessidades:

```php
Flight::map('error', function (Throwable $error) {
  // Trate o erro
  echo $error->getTraceAsString();
});
```

Por padrão, os erros não são registrados no servidor web. Você pode habilitar isso alterando a configuração:

```php
Flight::set('flight.log_errors', true);
```

#### 404 Não encontrado

Quando uma URL não pode ser encontrada, o Flight chama o método `notFound`. O comportamento padrão é enviar uma resposta `HTTP 404 Not Found` com uma mensagem simples.

Você pode [substituir](/learn/extending) esse comportamento para suas próprias necessidades:

```php
Flight::map('notFound', function () {
  // Trate não encontrado
});
```

## Veja também
- [Instalação](/install) - Configuração do skeleton, `.env` e estrutura de bootstrap.
- [Autoloading](/learn/autoloading) - Namespaces e uso de maiúsculas/minúsculas em pastas.
- [Estendendo o Flight](/learn/extending) - Como estender e personalizar a funcionalidade central do Flight.
- [Testes unitários](/guides/unit-testing) - Como escrever testes unitários para sua aplicação Flight.
- [IA e Experiência do Desenvolvedor](/learn/ai) - `AGENTS.md` e instruções consistentes do projeto.
- [Tracy](/awesome-plugins/tracy) - Um plugin para tratamento avançado de erros e depuração.
- [Extensões do Tracy](/awesome-plugins/tracy_extensions) - Extensões para integrar o Tracy ao Flight.
- [APM](/awesome-plugins/apm) - Um plugin para monitoramento de desempenho de aplicações e rastreamento de erros.
- [Segurança](/learn/security) - Flags de fortalecimento de segurança e tratamento de segredos.

## Solução de problemas
- Se você estiver tendo problemas para descobrir todos os valores da sua configuração, você pode fazer `var_dump(Flight::get());`
- Se o Runway ou ferramentas de implantação reescreveram `config.php`, confirme que segredos não foram commitados — mantenha-os em `.env` ou no ambiente real ao usar o padrão skeleton.

## Histórico de alterações
- Documentação – Anotado `flight.views.restrict_to_path` junto às configurações de caminho de views.
- Documentação – Documentado o padrão skeleton de configuração / camadas `.env` e a extensão padrão de views Twig para novos projetos.
- v3.18.1 - Adicionadas as opções de configuração `flight.debug` e `flight.allow_method_override`.
- v3.5.0 - Adicionada configuração para `flight.v2.output_buffering` para oferecer suporte ao comportamento legado de buffer de saída.
- v2.0 - Configurações principais adicionadas.