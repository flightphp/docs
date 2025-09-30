# Coleções

## Visão Geral

A classe `Collection` no Flight é uma utilidade prática para gerenciar conjuntos de dados. Ela permite acessar e manipular dados usando tanto notação de array quanto de objeto, tornando seu código mais limpo e flexível.

## Entendendo

Uma `Collection` é basicamente um wrapper ao redor de um array, mas com alguns poderes extras. Você pode usá-la como um array, iterar sobre ela, contar seus itens e até acessar itens como se fossem propriedades de objeto. Isso é especialmente útil quando você quer passar dados estruturados em seu app, ou quando quer tornar seu código um pouco mais legível.

Collections implementam várias interfaces do PHP:
- `ArrayAccess` (para que você possa usar sintaxe de array)
- `Iterator` (para que você possa iterar com `foreach`)
- `Countable` (para que você possa usar `count()`)
- `JsonSerializable` (para que você possa converter facilmente para JSON)

## Uso Básico

### Criando uma Collection

Você pode criar uma collection simplesmente passando um array para seu construtor:

```php
use flight\util\Collection;

$data = [
  'name' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$collection = new Collection($data);
```

### Acessando Itens

Você pode acessar itens usando notação de array ou de objeto:

```php
// Notação de array
echo $collection['name']; // Saída: FlightPHP

// Notação de objeto
echo $collection->version; // Saída: 3
```

Se você tentar acessar uma chave que não existe, você obterá `null` em vez de um erro.

### Definindo Itens

Você pode definir itens usando qualquer uma das notações também:

```php
// Notação de array
$collection['author'] = 'Mike Cao';

// Notação de objeto
$collection->license = 'MIT';
```

### Verificando e Removendo Itens

Verifique se um item existe:

```php
if (isset($collection['name'])) {
  // Faça algo
}

if (isset($collection->version)) {
  // Faça algo
}
```

Remova um item:

```php
unset($collection['author']);
unset($collection->license);
```

### Iterando Sobre uma Collection

Collections são iteráveis, então você pode usá-las em um loop `foreach`:

```php
foreach ($collection as $key => $value) {
  echo "$key: $value\n";
}
```

### Contando Itens

Você pode contar o número de itens em uma collection:

```php
echo count($collection); // Saída: 4
```

### Obtendo Todas as Chaves ou Dados

Obtenha todas as chaves:

```php
$keys = $collection->keys(); // ['name', 'version', 'features', 'license']
```

Obtenha todos os dados como um array:

```php
$data = $collection->getData();
```

### Limpando a Collection

Remova todos os itens:

```php
$collection->clear();
```

### Serialização JSON

Collections podem ser facilmente convertidas para JSON:

```php
echo json_encode($collection);
// Saída: {"name":"FlightPHP","version":3,"features":["routing","views","extending"],"license":"MIT"}
```

## Uso Avançado

Você pode substituir o array de dados interno completamente, se necessário:

```php
$collection->setData(['foo' => 'bar']);
```

Collections são especialmente úteis quando você quer passar dados estruturados entre componentes, ou quando quer fornecer uma interface mais orientada a objetos para dados de array.

## Veja Também

- [Requests](/learn/requests) - Aprenda como lidar com requisições HTTP e como collections podem ser usadas para gerenciar dados de requisição.
- [PDO Wrapper](/learn/pdo-wrapper) - Aprenda como usar o wrapper PDO no Flight e como collections podem ser usadas para gerenciar resultados de banco de dados.

## Solução de Problemas

- Se você tentar acessar uma chave que não existe, você obterá `null` em vez de um erro.
- Lembre-se de que collections não são recursivas: arrays aninhados não são automaticamente convertidos para collections.
- Se você precisar redefinir a collection, use `$collection->clear()` ou `$collection->setData([])`.

## Changelog

- v3.0 - Melhorias em type hints e suporte ao PHP 8+.
- v1.0 - Lançamento inicial da classe Collection.