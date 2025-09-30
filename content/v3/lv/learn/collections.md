# Kolekcijas

## Pārskats

`Collection` klase Flight ir ērts rīks datu kopu pārvaldībai. Tā ļauj piekļūt un manipulēt datus, izmantojot gan masīva, gan objekta notāciju, padarot jūsu kodu tīrāku un elastīgāku.

## Saprašana

`Collection` būtībā ir apvalks ap masīvu, bet ar dažām papildu spējām. Jūs varat izmantot to kā masīvu, iterēt pār to, skaitīt tā vienumus un pat piekļūt vienumiem tā, it kā tie būtu objekta īpašības. Tas ir īpaši noderīgi, kad vēlaties nodot strukturētus datus savā lietojumprogrammā vai kad vēlaties padarīt savu kodu nedaudz lasāmāku.

Kolekcijas īsteno vairākas PHP saskarnes:
- `ArrayAccess` (tāpēc jūs varat izmantot masīva sintaksi)
- `Iterator` (tāpēc jūs varat iterēt ar `foreach`)
- `Countable` (tāpēc jūs varat izmantot `count()`)
- `JsonSerializable` (tāpēc jūs varat viegli konvertēt uz JSON)

## Pamata Izmantošana

### Kolekcijas Izveidošana

Jūs varat izveidot kolekciju, vienkārši nododot masīvu tās konstruktoram:

```php
use flight\util\Collection;

$data = [
  'name' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$collection = new Collection($data);
```

### Vienumu Piekļūšana

Jūs varat piekļūt vienumiem, izmantojot gan masīva, gan objekta notāciju:

```php
// Masīva notācija
echo $collection['name']; // Izvade: Flight

// Objekta notācija
echo $collection->version; // Izvade: 3
```

Ja mēģināsiet piekļūt atslēgai, kas nepastāv, saņemsiet `null` nevis kļūdu.

### Vienumu Iestatīšana

Jūs varat iestatīt vienumus, izmantojot gan notāciju:

```php
// Masīva notācija
$collection['author'] = 'Mike Cao';

// Objekta notācija
$collection->license = 'MIT';
```

### Vienumu Pārbaude un Noņemšana

Pārbaudiet, vai vienums pastāv:

```php
if (isset($collection['name'])) {
  // Dariet kaut ko
}

if (isset($collection->version)) {
  // Dariet kaut ko
}
```

Noņemiet vienumu:

```php
unset($collection['author']);
unset($collection->license);
```

### Iterēšana Pār Kolekciju

Kolekcijas ir iterējamas, tāpēc jūs varat izmantot tās `foreach` cilpā:

```php
foreach ($collection as $key => $value) {
  echo "$key: $value\n";
}
```

### Vienumu Skaitīšana

Jūs varat skaitīt vienumu skaitu kolekcijā:

```php
echo count($collection); // Izvade: 4
```

### Visu Atslēgu vai Datu Iegūšana

Iegūstiet visas atslēgas:

```php
$keys = $collection->keys(); // ['name', 'version', 'features', 'license']
```

Iegūstiet visus datus kā masīvu:

```php
$data = $collection->getData();
```

### Kolekcijas Notīrīšana

Noņemiet visus vienumus:

```php
$collection->clear();
```

### JSON Serializācija

Kolekcijas var viegli konvertēt uz JSON:

```php
echo json_encode($collection);
// Izvade: {"name":"Flight","version":3,"features":["routing","views","extending"],"license":"MIT"}
```

## Uzlabota Izmantošana

Jūs varat pilnībā aizstāt iekšējo datu masīvu, ja nepieciešams:

```php
$collection->setData(['foo' => 'bar']);
```

Kolekcijas ir īpaši noderīgas, kad vēlaties nodot strukturētus datus starp komponentiem vai kad vēlaties nodrošināt vairāk objektorientētu saskarni masīva datiem.

## Skatīt Arī

- [Requests](/learn/requests) - Uzziniet, kā apstrādāt HTTP pieprasījumus un kā kolekcijas var tikt izmantotas, lai pārvaldītu pieprasījuma datus.
- [PDO Wrapper](/learn/pdo-wrapper) - Uzziniet, kā izmantot PDO apvalku Flight un kā kolekcijas var tikt izmantotas, lai pārvaldītu datubāzes rezultātus.

## Traucējumu Novēršana

- Ja mēģināsiet piekļūt atslēgai, kas nepastāv, saņemsiet `null` nevis kļūdu.
- Atcerieties, ka kolekcijas nav rekursīvas: iekļautie masīvi netiek automātiski konvertēti uz kolekcijām.
- Ja nepieciešams atiestatīt kolekciju, izmantojiet `$collection->clear()` vai `$collection->setData([])`.

## Izmaiņu Žurnāls

- v3.0 - Uzlaboti tipa mājieni un PHP 8+ atbalsts.
- v1.0 - Sākotnējā Collection klases izlaišana.