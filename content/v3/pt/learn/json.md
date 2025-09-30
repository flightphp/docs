# JSON Wrapper

## Visão Geral

A classe `Json` no Flight fornece uma maneira simples e consistente de codificar e decodificar dados JSON em sua aplicação. Ela envolve as funções JSON nativas do PHP com melhor tratamento de erros e alguns padrões úteis, tornando mais fácil e seguro trabalhar com JSON.

## Entendendo

Trabalhar com JSON é super comum em aplicações PHP modernas, especialmente ao construir APIs ou lidar com requisições AJAX. A classe `Json` centraliza toda a codificação e decodificação JSON, para que você não precise se preocupar com casos de borda estranhos ou erros crípticos das funções integradas do PHP.

Principais recursos:
- Tratamento de erros consistente (lança exceções em caso de falha)
- Opções padrão para codificação/decodificação (como barras invertidas não escapadas)
- Métodos utilitários para impressão formatada e validação

## Uso Básico

### Codificando Dados para JSON

Para converter dados PHP em uma string JSON, use `Json::encode()`:

```php
use flight\util\Json;

$data = [
  'framework' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$json = Json::encode($data);
echo $json;
// Saída: {"framework":"Flight","version":3,"features":["routing","views","extending"]}
```

Se a codificação falhar, você receberá uma exceção com uma mensagem de erro útil.

### Impressão Formatada

Quer que seu JSON seja legível por humanos? Use `prettyPrint()`:

```php
echo Json::prettyPrint($data);
/*
{
  "framework": "Flight",
  "version": 3,
  "features": [
    "routing",
    "views",
    "extending"
  ]
}
*/
```

### Decodificando Strings JSON

Para converter uma string JSON de volta para dados PHP, use `Json::decode()`:

```php
$json = '{"framework":"Flight","version":3}';
$data = Json::decode($json);
echo $data->framework; // Saída: Flight
```

Se você quiser um array associativo em vez de um objeto, passe `true` como o segundo argumento:

```php
$data = Json::decode($json, true);
echo $data['framework']; // Saída: Flight
```

Se a decodificação falhar, você receberá uma exceção com uma mensagem de erro clara.

### Validando JSON

Verifique se uma string é um JSON válido:

```php
if (Json::isValid($json)) {
  // É válido!
} else {
  // Não é JSON válido
}
```

### Obtendo o Último Erro

Se você quiser verificar a última mensagem de erro JSON (das funções nativas do PHP):

```php
$error = Json::getLastError();
if ($error !== '') {
  echo "Último erro JSON: $error";
}
```

## Uso Avançado

Você pode personalizar as opções de codificação e decodificação se precisar de mais controle (veja [opções do json_encode do PHP](https://www.php.net/manual/en/json.constants.php)):

```php
// Codificar com a opção JSON_HEX_TAG
$json = Json::encode($data, JSON_HEX_TAG);

// Decodificar com profundidade personalizada
$data = Json::decode($json, false, 1024);
```

## Veja Também

- [Collections](/learn/collections) - Para trabalhar com dados estruturados que podem ser facilmente convertidos para JSON.
- [Configuration](/learn/configuration) - Como configurar sua aplicação Flight.
- [Extending](/learn/extending) - Como adicionar suas próprias utilidades ou substituir classes principais.

## Solução de Problemas

- Se a codificação ou decodificação falhar, uma exceção é lançada — envolva suas chamadas em try/catch se quiser lidar com erros de forma graciosa.
- Se você obtiver resultados inesperados, verifique seus dados em busca de referências circulares ou caracteres não-UTF8.
- Use `Json::isValid()` para verificar se uma string é um JSON válido antes de decodificar.

## Changelog

- v3.16.0 - Adicionada classe utilitária de wrapper JSON.