# Runway

Runway ist eine CLI-Anwendung, die Ihnen bei der Verwaltung Ihrer Flight-Anwendungen hilft. Sie kann Controller generieren, alle Routen anzeigen und mehr. Sie basiert auf der hervorragenden [adhocore/php-cli](https://github.com/adhocore/php-cli)-Bibliothek.

Klicken Sie [hier](https://github.com/flightphp/runway), um den Code anzusehen.

## Installation

Installieren Sie mit Composer.

```bash
composer require flightphp/runway
```

## Basis-Konfiguration

Beim ersten Ausführen von Runway sucht es nach einer `runway`-Konfiguration in `app/config/config.php` über den Schlüssel `'runway'`.

```php
<?php
// app/config/config.php
return [
    'runway' => [
        'app_root' => 'app/',
		'public_root' => 'public/',
    ],
];
```

> **HINWEIS** - Ab **v1.2.0** ist `.runway-config.json` veraltet. Bitte migrieren Sie Ihre Konfiguration zu `app/config/config.php`. Sie können dies einfach mit dem Befehl `php runway config:migrate` tun.

### Projekt-Root-Erkennung

Runway ist intelligent genug, um das Root-Verzeichnis Ihres Projekts zu erkennen, auch wenn Sie es aus einem Unterverzeichnis ausführen. Es sucht nach Indikatoren wie `composer.json`, `.git` oder `app/config/config.php`, um zu bestimmen, wo das Projekt-Root liegt. Das bedeutet, Sie können Runway-Befehle von überall in Ihrem Projekt ausführen!

## Verwendung

Runway verfügt über eine Reihe von Befehlen, die Sie zur Verwaltung Ihrer Flight-Anwendung verwenden können. Es gibt zwei einfache Wege, Runway zu nutzen.

1. Wenn Sie das Skeleton-Projekt verwenden, können Sie `php runway [command]` vom Root-Verzeichnis Ihres Projekts ausführen.
1. Wenn Sie Runway als Paket über Composer installiert haben, können Sie `vendor/bin/runway [command]` vom Root-Verzeichnis Ihres Projekts ausführen.

### Befehlsliste

Sie können eine Liste aller verfügbaren Befehle anzeigen, indem Sie den Befehl `php runway` ausführen.

```bash
php runway
```

### Befehls-Hilfe

Für jeden Befehl können Sie die `--help`-Flagge übergeben, um weitere Informationen zur Verwendung des Befehls zu erhalten.

```bash
php runway routes --help
```

Hier sind einige Beispiele:

### Einen Controller generieren

Basierend auf der Konfiguration in `runway.app_root` wird der Speicherort einen Controller für Sie im Verzeichnis `app/controllers/` generieren.

```bash
php runway make:controller MyController
```

### Ein Active Record-Modell generieren

Stellen Sie zuerst sicher, dass Sie das [Active Record](/awesome-plugins/active-record)-Plugin installiert haben. Basierend auf der Konfiguration in `runway.app_root` wird der Speicherort einen Datensatz für Sie im Verzeichnis `app/records/` generieren.

```bash
php runway make:record users
```

Falls Sie beispielsweise die Tabelle `users` mit dem folgenden Schema haben: `id`, `name`, `email`, `created_at`, `updated_at`, wird eine Datei ähnlich der folgenden in der Datei `app/records/UserRecord.php` erstellt:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord-Klasse für die Tabelle users.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // Sie könnten auch Beziehungen hier hinzufügen, sobald Sie sie im $relations-Array definieren
 * @property CompanyRecord $company Beispiel für eine Beziehung
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Setzt die Beziehungen für das Modell
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Konstruktor
     * @param mixed $databaseConnection Die Verbindung zur Datenbank
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Alle Routen anzeigen

Dies zeigt alle Routen an, die derzeit bei Flight registriert sind.

```bash
php runway routes
```

Wenn Sie nur spezifische Routen anzeigen möchten, können Sie eine Flagge übergeben, um die Routen zu filtern.

```bash
# Zeigt nur GET-Routen an
php runway routes --get

# Zeigt nur POST-Routen an
php runway routes --post

# usw.
```

## Hinzufügen benutzerdefinierter Befehle zu Runway

Wenn Sie entweder ein Paket für Flight erstellen oder eigene benutzerdefinierte Befehle zu Ihrem Projekt hinzufügen möchten, können Sie dies tun, indem Sie ein Verzeichnis `src/commands/`, `flight/commands/`, `app/commands/` oder `commands/` für Ihr Projekt/Paket erstellen. Wenn Sie weitere Anpassungen benötigen, siehe den Abschnitt unten zu Konfiguration.

Um einen Befehl zu erstellen, erweitern Sie einfach die Klasse `AbstractBaseCommand` und implementieren mindestens eine `__construct`-Methode und eine `execute`-Methode.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Konstruktor
     *
     * @param array<string,mixed> $config Konfiguration aus app/config/config.php
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Erstellt ein Beispiel für die Dokumentation', $config);
        $this->argument('<funny-gif>', 'Der Name des lustigen GIFs');
    }

	/**
     * Führt die Funktion aus
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Erstelle Beispiel...');

		// Hier etwas tun

		$io->ok('Beispiel erstellt!');
	}
}
```

Sehen Sie sich die [adhocore/php-cli-Dokumentation](https://github.com/adhocore/php-cli) für weitere Informationen an, wie Sie eigene benutzerdefinierte Befehle in Ihre Flight-Anwendung einbauen können!

## Konfigurationsverwaltung

Da die Konfiguration ab `v1.2.0` zu `app/config/config.php` verschoben wurde, gibt es einige Hilfsbefehle zur Verwaltung der Konfiguration.

### Alte Konfiguration migrieren

Wenn Sie eine alte `.runway-config.json`-Datei haben, können Sie sie einfach mit dem folgenden Befehl zu `app/config/config.php` migrieren:

```bash
php runway config:migrate
```

### Konfigurationswert setzen

Sie können einen Konfigurationswert mit dem Befehl `config:set` setzen. Dies ist nützlich, wenn Sie einen Konfigurationswert aktualisieren möchten, ohne die Datei zu öffnen.

```bash
php runway config:set app_root "app/"
```

### Konfigurationswert abrufen

Sie können einen Konfigurationswert mit dem Befehl `config:get` abrufen.

```bash
php runway config:get app_root
```

## Alle Runway-Konfigurationen

Wenn Sie die Konfiguration für Runway anpassen müssen, können Sie diese Werte in `app/config/config.php` setzen. Unten sind einige zusätzliche Konfigurationen, die Sie setzen können:

```php
<?php
// app/config/config.php
return [
    // ... andere Konfigurationswerte ...

    'runway' => [
        // Hier liegt Ihr Anwendungsverzeichnis
        'app_root' => 'app/',

        // Dies ist das Verzeichnis, in dem sich Ihre Root-Index-Datei befindet
        'index_root' => 'public/',

        // Dies sind die Pfade zu den Roots anderer Projekte
        'root_paths' => [
            '/home/user/different-project',
            '/var/www/another-project'
        ],

        // Basis-Pfade müssen wahrscheinlich nicht konfiguriert werden, aber es ist hier, falls Sie es wollen
        'base_paths' => [
            '/includes/libs/vendor', // falls Sie einen wirklich einzigartigen Pfad für Ihr Vendor-Verzeichnis haben oder so
        ],

        // Finale Pfade sind Orte innerhalb eines Projekts, um nach Befehlsdateien zu suchen
        'final_paths' => [
            'src/diff-path/commands',
            'app/module/admin/commands',
        ],

        // Wenn Sie einfach den vollständigen Pfad hinzufügen möchten, tun Sie es ruhig (absolut oder relativ zum Projekt-Root)
        'paths' => [
            '/home/user/different-project/src/diff-path/commands',
            '/var/www/another-project/app/module/admin/commands',
            'app/my-unique-commands'
        ]
    ]
];
```

### Zugriff auf Konfiguration

Wenn Sie die Konfigurationswerte effektiv abrufen müssen, können Sie sie über die `__construct`-Methode oder die `app()`-Methode abrufen. Es ist auch wichtig zu beachten, dass, wenn Sie eine Datei `app/config/services.php` haben, diese Dienste auch für Ihren Befehl verfügbar sind.

```php
public function execute()
{
    $io = $this->app()->io();
    
    // Konfiguration abrufen
    $app_root = $this->config['runway']['app_root'];
    
    // Dienste abrufen, wie vielleicht eine Datenbankverbindung
    $database = $this->config['database']
    
    // ...
}
```

## AI-Hilfs-Wrapper

Runway hat einige Hilfs-Wrapper, die es AI einfacher machen, Befehle zu generieren. Sie können `addOption` und `addArgument` auf eine Weise verwenden, die ähnlich wie bei Symfony Console wirkt. Dies ist hilfreich, wenn Sie AI-Tools verwenden, um Ihre Befehle zu generieren.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Erstellt ein Beispiel für die Dokumentation', $config);
    
    // Der Name-Argument ist nullable und standardmäßig vollständig optional
    $this->addOption('name', 'Der Name des Beispiels', null);
}
```