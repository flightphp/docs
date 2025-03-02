# Startbahn

Startbahn ist eine CLI-Anwendung, die Ihnen dabei hilft, Ihre Flight-Anwendungen zu verwalten. Sie kann Controller generieren, alle Routen anzeigen und mehr. Sie basiert auf der ausgezeichneten [adhocore/php-cli](https://github.com/adhocore/php-cli) Bibliothek.

Klicken Sie [hier](https://github.com/flightphp/runway), um den Code anzusehen.

## Installation

Mit Composer installieren.

```bash
composer require flightphp/startbahn
```

## Grundkonfiguration

Das erste Mal, wenn Sie Startbahn ausführen, wird es Sie durch einen Einrichtungsprozess führen und eine .runway.json-Konfigurationsdatei im Stammverzeichnis Ihres Projekts erstellen. Diese Datei wird einige notwendige Konfigurationen enthalten, damit Startbahn ordnungsgemäß funktioniert.

## Verwendung

Startbahn hat mehrere Befehle, die Sie verwenden können, um Ihre Flight-Anwendung zu verwalten. Es gibt zwei einfache Möglichkeiten, Startbahn zu verwenden.

1. Wenn Sie das Grundgerüstprojekt verwenden, können Sie `php startbahn [Befehl]` im Stammverzeichnis Ihres Projekts ausführen.
1. Wenn Sie Startbahn als über Composer installiertes Paket verwenden, können Sie `vendor/bin/startbahn [Befehl]` im Stammverzeichnis Ihres Projekts ausführen.

Für jeden Befehl können Sie die `--help`-Flagge übergeben, um weitere Informationen zur Verwendung des Befehls zu erhalten.

```bash
php startbahn routes --help
```

Hier sind ein paar Beispiele:

### Einen Controller generieren

Basierend auf der Konfiguration in Ihrer .runway.json-Datei wird der Standardort einen Controller für Sie im `app/controllers/` Verzeichnis generieren.

```bash
php startbahn make:controller MeinController
```

### Einen Aktiven Datensatz-Model generieren

Basierend auf der Konfiguration in Ihrer .runway.json-Datei wird der Standardort einen Controller für Sie im `app/records/` Verzeichnis generieren.

```bash
php startbahn make:record benutzer
```

Wenn Sie zum Beispiel die `benutzer` Tabelle mit dem folgenden Schema haben: `id`, `name`, `email`, `created_at`, `updated_at`, wird eine Datei ähnlich der folgenden in der `app/records/BenutzerDatensatz.php` erstellt:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord-Klasse für die benutzer Tabelle.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // Sie können hier auch Beziehungen hinzufügen, sobald Sie sie im $relations-Array definieren
 * @property CompanyRecord $company Beispiel für eine Beziehung
 */
class BenutzerDatensatz erstreckt sich über \flight\ActiveRecord
{
    /**
     * @var array $relations Setzen Sie die Beziehungen für das Modell
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Konstruktor
     * @param gemischt $datenbankverbindung Die Verbindung zur Datenbank
     */
    public function __construct($datenbankverbindung)
    {
        parent::__construct($datenbankverbindung, 'benutzer');
    }
}
```

### Alle Routen anzeigen

Dies wird alle Routen anzeigen, die derzeit bei Flight registriert sind.

```bash
php startbahn routes
```

Wenn Sie nur bestimmte Routen anzeigen möchten, können Sie eine Flagge übergeben, um die Routen zu filtern.

```bash
# Nur GET-Routen anzeigen
php startbahn routes --get

# Nur POST-Routen anzeigen
php startbahn routes --post

# usw.
```

## Anpassen von Startbahn

Wenn Sie entweder ein Paket für Flight erstellen oder Ihre eigenen benutzerdefinierten Befehle in Ihr Projekt integrieren möchten, können Sie dies tun, indem Sie ein `src/befehle/`, `flight/befehle/`, `app/befehle/` oder `befehle/` Verzeichnis für Ihr Projekt/Paket erstellen.

Um einen Befehl zu erstellen, erweitern Sie einfach die Klasse `AbstractBaseCommand` und implementieren Sie mindestens eine `__construct`-Methode und eine `ausführen`-Methode.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

Klasse BeispielBefehl erweitert AbstractBaseCommand
{
	/**
     * Konstruieren
     *
     * @param array<string, gemischt> $konfig JSON-Konfiguration aus .runway-config.json
     */
    public function __construct(array $konfig)
    {
        parent::__construct('make:beispiel', 'Erstellt ein Beispiel für die Dokumentation', $konfig);
        $this->argument('<lustiges-gif>', 'Der Name des lustigen Gifs');
    }

	/**
     * Führt die Funktion aus
     *
     * @return Leer
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('Beispiel wird erstellt...');

		// Hier etwas machen

		$io->ok('Beispiel erstellt!');
	}
}
```

Siehe die [adhocore/php-cli Dokumentation](https://github.com/adhocore/php-cli) für weitere Informationen, wie Sie Ihre eigenen benutzerdefinierten Befehle in Ihre Flight-Anwendung integrieren können!