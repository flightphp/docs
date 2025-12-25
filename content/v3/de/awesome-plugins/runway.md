# Runway

Runway ist eine CLI-Anwendung, die Ihnen hilft, Ihre Flight-Anwendungen zu verwalten. Sie kann Controller generieren, alle Routen anzeigen und mehr. Sie basiert auf der hervorragenden [adhocore/php-cli](https://github.com/adhocore/php-cli)-Bibliothek.

Klicken Sie [hier](https://github.com/flightphp/runway), um den Code anzusehen.

## Installation

Installieren Sie mit Composer.

```bash
composer require flightphp/runway
```

## Grundlegende Konfiguration

Beim ersten Ausführen von Runway durchläuft es einen Einrichtungsprozess und erstellt eine `.runway.json`-Konfigurationsdatei im Stammverzeichnis Ihres Projekts. Diese Datei enthält einige notwendige Konfigurationen, damit Runway ordnungsgemäß funktioniert.

## Verwendung

Runway verfügt über eine Reihe von Befehlen, die Sie verwenden können, um Ihre Flight-Anwendung zu verwalten. Es gibt zwei einfache Wege, Runway zu verwenden.

1. Wenn Sie das Skeleton-Projekt verwenden, können Sie `php runway [command]` vom Stammverzeichnis Ihres Projekts ausführen.
1. Wenn Sie Runway als über Composer installiertes Paket verwenden, können Sie `vendor/bin/runway [command]` vom Stammverzeichnis Ihres Projekts ausführen.

Für jeden Befehl können Sie die `--help`-Flagge übergeben, um weitere Informationen zur Verwendung des Befehls zu erhalten.

```bash
php runway routes --help
```

Hier sind einige Beispiele:

### Einen Controller generieren

Basierend auf der Konfiguration in Ihrer `.runway.json`-Datei wird der Standardort einen Controller für Sie im `app/controllers/`-Verzeichnis generieren.

```bash
php runway make:controller MyController
```

### Ein Active Record-Modell generieren

Basierend auf der Konfiguration in Ihrer `.runway.json`-Datei wird der Standardort einen Controller für Sie im `app/records/`-Verzeichnis generieren.

```bash
php runway make:record users
```

Falls Sie beispielsweise die `users`-Tabelle mit dem folgenden Schema haben: `id`, `name`, `email`, `created_at`, `updated_at`, wird eine Datei ähnlich der folgenden in der Datei `app/records/UserRecord.php` erstellt:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord-Klasse für die users-Tabelle.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // Sie könnten hier auch Beziehungen hinzufügen, sobald Sie sie im $relations-Array definieren
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

## Anpassen von Runway

Wenn Sie entweder ein Paket für Flight erstellen oder eigene benutzerdefinierte Befehle in Ihr Projekt hinzufügen möchten, können Sie dies tun, indem Sie ein `src/commands/`, `flight/commands/`, `app/commands/` oder `commands/`-Verzeichnis für Ihr Projekt/Paket erstellen. Wenn Sie weitere Anpassungen benötigen, siehe den Abschnitt unten zu Konfiguration.

Um einen Befehl zu erstellen, erweitern Sie einfach die `AbstractBaseCommand`-Klasse und implementieren mindestens eine `__construct`-Methode und eine `execute`-Methode.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Konstruktor
     *
     * @param array<string,mixed> $config JSON-Konfiguration aus .runway-config.json
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

		// Etwas hier tun

		$io->ok('Beispiel erstellt!');
	}
}
```

Siehe die [adhocore/php-cli-Dokumentation](https://github.com/adhocore/php-cli) für weitere Informationen darüber, wie Sie eigene benutzerdefinierte Befehle in Ihre Flight-Anwendung einbauen!

### Konfiguration

Wenn Sie die Konfiguration für Runway anpassen müssen, können Sie eine `.runway-config.json`-Datei im Stammverzeichnis Ihres Projekts erstellen. Nachfolgend sind einige zusätzliche Konfigurationen aufgeführt, die Sie setzen können:

```js
{

	// Hier befindet sich das Verzeichnis Ihrer Anwendung
	"app_root": "app/",

	// Dies ist das Verzeichnis, in dem sich Ihre root index-Datei befindet
	"index_root": "public/",

	// Dies sind die Pfade zu den Roots anderer Projekte
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// Base-Pfade müssen wahrscheinlich nicht konfiguriert werden, aber es ist hier, falls Sie es wollen
	"base_paths": {
		"/includes/libs/vendor", // falls Sie einen wirklich einzigartigen Pfad für Ihr Vendor-Verzeichnis oder Ähnliches haben
	},

	// Final-Pfade sind Orte innerhalb eines Projekts, um nach den Befehlsdateien zu suchen
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// Wenn Sie einfach den vollständigen Pfad hinzufügen möchten, tun Sie es ruhig (absolut oder relativ zum Projekt-Root)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```