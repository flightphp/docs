# Pacēlums

Pacēlums ir CLI lietotne, kas palīdz pārvaldīt jūsu Flight lietotnes. Tas var ģenerēt kontrolerus, parādīt visas maršrutu celtnes un vēl daudz ko citu. Tas balstās uz lielisko [adhocore/php-cli](https://github.com/adhocore/php-cli) bibliotēku.

## Instalācija

Instalējiet ar komponistu.

```bash
composer require flightphp/runway
```

## Pamata konfigurācija

Pirmo reizi palaistot Pacēlumu, tas izpildīs jūs cauri iestatīšanas procesam un izveidos `.runway.json` konfigurācijas failu jūsu projekta saknē. Šajā failā būs dažas nepieciešamās konfigurācijas, lai Pacēlums varētu pareizi darboties.

## Lietošana

Pacēlumam ir vairākas komandas, kuras varat izmantot, lai pārvaldītu savu Flight lietotni. Ir divi vienkārši veidi, kā izmantot Pacēlumu.

1. Ja izmantojat skeleta projektu, varat palaist `php runway [komanda]` no savas projekta saknes.
1. Ja izmantojat Pacēlumu kā pakotni, kas instalēta ar komponistu, varat palaist `vendor/bin/runway [komanda]` no savas projekta saknes.

Jebkurai komandai varat padot `--help` karodziņu, lai iegūtu vairāk informācijas par to, kā izmantot komandu.

```bash
php runway routes --help
```

Šeit ir daži piemēri:

### Ģenerēt kontrolieri

Balstoties uz konfigurāciju jūsu `.runway.json` failā, pēc noklusējuma atrašanās vieta ģenerēs jums kontroleri `app/controllers/` mapē.

```bash
php runway make:controller MyController
```

### Ģenerēt aktīvās ierakstu modeli

Balstoties uz konfigurāciju jūsu `.runway.json` failā, pēc noklusējuma atrašanās vieta ģenerēs jums kontrolieri `app/records/` mapē.

```bash
php runway make:record users
```

Ja, piemēram, jums ir `users` tabula ar sekojošo shēmu: `id`, `name`, `email`, `created_at`, `updated_at`, faila līdzīga tālāk redzamajai tiks izveidota `app/records/UserRecord.php` failā:

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
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Iestata attiecības modelim
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

Tiks parādīti visi maršruti, kas pašlaik ir reģistrēti ar Flight.

```bash
php runway routes
```

Ja vēlaties skatīt tikai konkrētus maršrutus, varat padot karodziņu, lai filtrētu maršrutus.

```bash
# Parādīt tikai GET maršrutus
php runway routes --get

# Parādīt tikai POST maršrutus
php runway routes --post

# u.t.t.
```

## Pacēluma pielāgošana

Ja jūs veidojat pakotni Flight, vai vēlaties pievienot savas pielāgotas komandas savā projektā, to varat izdarīt, izveidojot `src/commands/`, `flight/commands/`, `app/commands/` vai `commands/` direktoriju savam projektam/pakotnei.

Lai izveidotu komandu, vienkārši paplašiniet `AbstractBaseCommand` klasi un implementējiet vismaz `__construct` metodi un `execute` metodi.

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
        $this->argument('<funny-gif>', 'Smaidojošā gif attēla nosaukums');
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

		// Darīt kaut ko šeit

		$io->ok('Piemērs izveidots!');
	}
}
```

Skatiet [adhocore/php-cli dokumentāciju](https://github.com/adhocore/php-cli) papildinformācijai par to, kā izveidot savas pielāgotas komandas savā Flight lietotnē!