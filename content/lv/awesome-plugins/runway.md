# Lidmašīnas

Lidmašīnas ir CLI lietotne, kas palīdz pārvaldīt jūsu Flight lietojumprogrammas. Tā var ģenerēt kontrolierus, parādīt visus maršrutus un vairāk. Tā balstīta uz lielisko [adhocore/php-cli](https://github.com/adhocore/php-cli) bibliotēku.

Uzklikšķiniet [šeit](https://github.com/flightphp/runway), lai skatītu kodu.

## Instalēšana

Instalējiet to ar komponistu.

```bash
composer require flightphp/runway
```

## Pamata konfigurācija

Pirmo reizi palaižot Lidmašīnas, tā vadīs jūs caur iestatīšanas procesu un izveidos `.runway.json` konfigurācijas failu jūsu projekta saknē. Šajā failā būs dažas nepieciešamās konfigurācijas, lai Lidmašīnas pareizi darbotos.

## Lietojums

Lidmašīnā ir vairākas komandas, ar kurām varat pārvaldīt savu Flight lietojumprogrammu. Ir divi viegli veidi, kā izmantot Lidmašīnas.

1. Ja izmantojat ietvaru projektu, varat izpildīt `php runway [komanda]` no savu projekta saknes.
1. Ja izmantojat Lidmašīnas kā paketi, kas instalēts ar komponistu, varat izpildīt `vendor/bin/runway [komanda]` no savu projekta saknes.

Lai iegūtu papildinformāciju par jebkuru komandu, jūs varat padot `--help` karodziņa.

```bash
php runway routes --help
```

Šeit ir daži piemēri:

### Ģenerēt kontrolieri

Balstoties uz konfigurāciju jūsu `.runway.json` failā, noklusējuma atrašanās vieta jums ģenerēs kontrolieri `app/controllers/` direktorijā.

```bash
php runway make:controller MyController
```

### Ģenerēt aktīvās ierakstu modeles

Balstoties uz konfigurāciju jūsu `.runway.json` failā, noklusējuma atrašanās vieta jums ģenerēs kontrolieri `app/records/` direktorijā.

```bash
php runway make:record users
```

Ja, piemēram, ir `users` tabula ar sekojošu shēmu: `id`, `name`, `email`, `created_at`, `updated_at`, fails līdzīgs sekojošajam tiks izveidots `app/records/UserRecord.php` failā:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord klase lietotāju tabulai.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // šeit jūs varat pievienot attiecības, kad tās definētas $relations masīvā
 * @property CompanyRecord $company Attēlots attiecību piemērs
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Uzstādiet attiecības modeļim
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Konstruktors
     * @param mixed $databaseConnection Datu bāzes savienojums
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

Ja vēlaties skatīt tikai konkrētus maršrutus, jūs varat padot karodziņu, lai filtrētu maršrutus.

```bash
# Parādīt tikai GET maršrutus
php runway routes --get

# Parādīt tikai POST maršrutus
php runway routes --post

# u.c.
```

## Lidmašīnas pielāgošana

Ja jūs izveidojat paketi Flight, vai vēlaties pievienot savas pielāgotas komandas savā projektā, to varat izdarīt, izveidojot `src/commands/`, `flight/commands/`, `app/commands/` vai `commands/` direktoriju savam projektam/paketei.

Lai izveidotu komandu, jums vienkārši jāpaplašina `AbstractBaseCommand` klase un jāimplementē vismaz `__construct` metode un `execute` metode.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
    /**
     * Konstruktors
     *
     * @param array<string,mixed> $config JSON konfigurācija no .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Izveidot piemēru dokumentācijai', $config);
        $this->argument('<funny-gif>', 'Smaida GIF nosaukums');
    }

	/**
     * Izpilda funkciju
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('Izveido piemēru...');

		// Kaut ko dariet šeit

		$io->ok('Piemērs izveidots!');
	}
}
```

Skatiet [adhocore/php-cli Dokumentāciju](https://github.com/adhocore/php-cli), lai iegūtu vairāk informācijas par to, kā izveidot savas pielāgotas komandas savā Flight lietojumprogrammā!