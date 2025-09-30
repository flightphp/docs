# JSON Apvalks

## Pārskats

`Json` klase Flight nodrošina vienkāršu, konsekventu veidu, kā kodēt un dekodēt JSON datus jūsu lietojumprogrammā. Tā apvalko PHP iebūvēto JSON funkciju ar labāku kļūdu apstrādi un dažām noderīgām noklusējuma vērtībām, padarot to vieglāku un drošāku strādāt ar JSON.

## Saprašana

Darbs ar JSON ir ārkārtīgi izplatīts mūsdienu PHP lietojumprogrammās, īpaši, kad veidojat API vai apstrādājat AJAX pieprasījumus. `Json` klase centralizē visu jūsu JSON kodēšanu un dekodēšanu, tāpēc jums nav jāuztraucas par dīvainiem malu gadījumiem vai nesaprotamām kļūdām no PHP iebūvētajām funkcijām.

Galvenās funkcijas:
- Konsekventa kļūdu apstrāde (izmet izņēmumus kļūdas gadījumā)
- Noklusējuma opcijas kodēšanai/dekodēšanai (piemēram, neizbēgtas slīpsvītras)
- Palīgfunkcijas skaistai izdrukai un validācijai

## Pamata Izmantošana

### Datu Kodēšana uz JSON

Lai pārveidotu PHP datus uz JSON virkni, izmantojiet `Json::encode()`:

```php
use flight\util\Json;

$data = [
  'framework' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$json = Json::encode($data);
echo $json;
// Output: {"framework":"Flight","version":3,"features":["routing","views","extending"]}
```

Ja kodēšana neizdodas, jūs saņemsiet izņēmumu ar noderīgu kļūdas ziņu.

### Skaista Izdruka

Vai vēlaties, lai jūsu JSON būtu lasāms cilvēkiem? Izmantojiet `prettyPrint()`:

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

### JSON Virkņu Dekodēšana

Lai pārveidotu JSON virkni atpakaļ uz PHP datiem, izmantojiet `Json::decode()`:

```php
$json = '{"framework":"Flight","version":3}';
$data = Json::decode($json);
echo $data->framework; // Output: Flight
```

Ja vēlaties asociatīvu masīvu nevis objektu, nododiet `true` kā otro argumentu:

```php
$data = Json::decode($json, true);
echo $data['framework']; // Output: Flight
```

Ja dekodēšana neizdodas, jūs saņemsiet izņēmumu ar skaidru kļūdas ziņu.

### JSON Validācija

Pārbaudiet, vai virkne ir derīgs JSON:

```php
if (Json::isValid($json)) {
  // Tas ir derīgs!
} else {
  // Nav derīgs JSON
}
```

### Pēdējās Kļūdas Iegūšana

Ja vēlaties pārbaudīt pēdējo JSON kļūdas ziņu (no iebūvētām PHP funkcijām):

```php
$error = Json::getLastError();
if ($error !== '') {
  echo "Last JSON error: $error";
}
```

## Uzlabota Izmantošana

Jūs varat pielāgot kodēšanas un dekodēšanas opcijas, ja vajag vairāk kontroles (skatiet [PHP json_encode opcijas](https://www.php.net/manual/en/json.constants.php)):

```php
// Kodēšana ar HEX_TAG opciju
$json = Json::encode($data, JSON_HEX_TAG);

// Dekodēšana ar pielāgotu dziļumu
$data = Json::decode($json, false, 1024);
```

## Skatīt Arī

- [Collections](/learn/collections) - Darbam ar strukturētiem datiem, kas viegli pārveidojami uz JSON.
- [Configuration](/learn/configuration) - Kā konfigurēt jūsu Flight lietojumprogrammu.
- [Extending](/learn/extending) - Kā pievienot savas palīgfunkcijas vai pārdefinēt kodola klases.

## Traucējummeklēšana

- Ja kodēšana vai dekodēšana neizdodas, tiek izmests izņēmums — i包ojiet savus izsaukumus try/catch, ja vēlaties apstrādāt kļūdas eleganti.
- Ja saņemat negaidītus rezultātus, pārbaudiet savus datus uz apļa atsaucēm vai ne-UTF8 rakstzīmēm.
- Izmantojiet `Json::isValid()`, lai pārbaudītu, vai virkne ir derīgs JSON pirms dekodēšanas.

## Izmaiņu Žurnāls

- v3.16.0 - Pievienota JSON apvalka palīgfunkciju klase.