# Lidmašīna

Lidmašīna ir CLI lietojumprogramma, kas palīdz pārvaldīt jūsu Flight lietojumprogrammas. Tā var ģenerēt kontrolierus, parādīt visus maršrutus un daudz ko citu. Tā balstās uz lielisko [adhocore/php-cli](https://github.com/adhocore/php-cli) bibliotēku.

## Instalēšana

Instalējiet ar komponistu.

```bash
composer require flightphp/runway
```

## Pamata konfigurācija

Pirmo reizi palaistot Lidmašīnu, tā pavadīs jūs cauri uzstādīšanas procesam un izveidos `.runway.json` konfigurācijas failu jūsu projekta saknē. Šajā failā būs iekļautas dažas nepieciešamās konfigurācijas, lai Lidmašīna varētu pareizi darboties.

## Lietošana

Lidmašīnai ir vairākas komandas, ar kurām varat pārvaldīt savu Flight lietojumprogrammu. Ir divi vienkārši veidi, kā izmantot Lidmašīnu.

1. Ja izmantojat skeleta projektu, jūs varat palaist `php runway [komanda]` no projekta saknes.
1. Ja izmantojat Lidmašīnu kā pakotni, ko instalējat ar komponistu, jūs varat palaist `vendor/bin/runway [komanda]` no projekta saknes.

Jebkurai komandai varat norādīt `--help` karodziņu, lai iegūtu vairāk informācijas par komandas lietošanu.

```bash
php runway routes --help
```

Šeit ir daži piemēri:

### Ģenerēt kontrolieri

Pamatojoties uz konfigurāciju jūsu `.runway.json` failā, noklusējuma atrašanās vieta jums ģenerēs kontrolieri `app/controllers/` direktorijā.

```bash
php runway make:controller MansKontrolieris
```

### Ģenerēt aktīvās ieraksta modeļus

Pamatojoties uz konfigurāciju jūsu `.runway.json` failā, noklusējuma atrašanās vieta jums ģenerēs ierakstu modeļi `app/records/` direktorijā.

```bash
php runway make:record lietotaji
```

Ja piemēram, jums ir `lietotaji` tabula ar šādu shēmu: `id`, `vārds`, `e-pasts`, `izveidots_pie`, `atjaunināts_pie`, fails līdzīgs sekojošajam tiks izveidots `app/records/UserRecord.php` failā:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Aktīvā ieraksta klase lietotāju tabulai.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // šeit jūs varat pievienot attiecības, kad tās definē tas būs $relations masīvā
 * @property CompanyRecord $company Piemērs attiecībām
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Iestatiet attiecības modelim
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Konstruktors
     * @param mixed $databaseConnection Datubāzes pieslēgums
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Parādīt visus maršrutus

Tas parādīs visus maršrutus, kas pašlaik ir reģistrēti ar Flight.

```bash
php runway routes
```

Ja vēlaties aplūkot tikai konkrētus maršrutus, jūs varat norādīt karodziņu, lai filtrētu maršrutus.

```bash
# Parādīt tikai GET maršrutus
php runway routes --get

# Parādīt tikai POST maršrutus
php runway routes --post

# utt.
```

## Lidmašīnas pielāgošana

Ja jūs veidojat pakotni Flight, vai vēlaties pievienot savas pielāgotās komandas savā projektā, to varat izdarīt, izveidojot `src/commands/`, `flight/commands/`, `app/commands/` vai `commands/` direktoriju savam projektam/pakotnei.

Lai izveidotu komandu, vienkārši paplašiniet `AbstractBaseCommand` klasi un implementējiet vismaz `__construct` metodi un `execute` metodi.

```php
<?php

declare(strict_types=1);

namespace lidosta\komandas;

class PiemēraKomanda paplašina AbstractBaseCommand
{
	/**
     * Konstruktors
     *
     * @param array<string,mixed> $config JSON konfigurācija no .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:piemērs', 'Izveidot piemēru dokumentācijai', $config);
        $this->argument('<jociņš-ar-jokiem>', 'Smejīgā gif attēla nosaukums');
    }

	/**
     * Izpilda funkciju
     *
     * @return void
     */
    public function execute(string $kontrolieris)
    {
        $io = $this->app()->io();

		$io->info('Veido piemēru...');

		// Dariet kaut ko šeit
          		
		$io->ok('Piemērs izveidots!');
	}
}
```

Skatiet [adhocore/php-cli dokumentāciju](https://github.com/adhocore/php-cli), lai iegūtu vairāk informācijas par to, kā izveidot savas pielāgotas komandas savā Flight lietojumprogrammā!