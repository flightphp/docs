# Instalēšanas instrukcijas

Ir daži pamatpriekšnoteikumi, pirms varat instalēt Flight. Galvenokārt jums būs jā:

1. [Instalē PHP sistēmā](#instalēšanas-php)
2. [Instalē Composer](https://getcomposer.org) labākai izstrādātāju pieredzei.

## Pamatinstalēšana

Ja izmantojat [Composer](https://getcomposer.org), varat izpildīt šādu
komandu:

```bash
composer require flightphp/core
```

Tas tikai ievietos Flight kodola failus jūsu sistēmā. Jums būs jādefinē projekta struktūra, [izkārtojums](/learn/templates), [atkarības](/learn/dependency-injection-container), [konfigurācijas](/learn/configuration), [automātiskā ielāde](/learn/autoloading) utt. Šī metode nodrošina, ka nav instalētas citas atkarības, izņemot Flight.

Varat arī [lejupielādēt failus](https://github.com/flightphp/core/archive/master.zip)
tieši un izvilkt tos savā tīmekļa direktorijā.

## Ieteicamā instalēšana

Ir augsti ieteicams sākt ar [flightphp/skeleton](https://github.com/flightphp/skeleton) lietotni jebkuram jaunam projektam. Instalēšana ir viegla.

```bash
composer create-project flightphp/skeleton my-project/
```

Tas iestatīs jūsu projekta struktūru, konfigurēs automātisko ielādi ar vārdtelpām, iestatīs konfigurāciju un nodrošinās citas rīkus, piemēram, [Tracy](/awesome-plugins/tracy), [Tracy Extensions](/awesome-plugins/tracy-extensions) un [Runway](/awesome-plugins/runway).

## Konfigurējiet savu tīmekļa serveri

### Iebūvētais PHP izstrādes serveris

Šī ir vienkāršākā metode, lai sāktu darbu. Varat izmantot iebūvēto serveri, lai palaistu savu lietotni un pat izmantot SQLite datubāzei (tik ilgi, kamēr sqlite3 ir instalēts jūsu sistēmā), un neprasīt gandrīz neko! Vienreiz instalējot PHP, vienkārši izpildiet šādu komandu:

```bash
php -S localhost:8000
# vai ar skeleton lietotni
composer start
```

Pēc tam atveriet pārlūku un dodieties uz `http://localhost:8000`.

Ja vēlaties padarīt sava projekta dokumenta saknes direktoriju citu direktoriju (Piem.: jūsu projekts ir `~/myproject`, bet jūsu dokumenta sakne ir `~/myproject/public/`), varat izpildīt šādu komandu, atrodoties `~/myproject` direktorijā:

```bash
php -S localhost:8000 -t public/
# ar skeleton lietotni tas jau ir konfigurēts
composer start
```

Pēc tam atveriet pārlūku un dodieties uz `http://localhost:8000`.

### Apache

Pārliecinieties, ka Apache jau ir instalēts jūsu sistēmā. Ja nē, meklējiet Google, kā instalēt Apache jūsu sistēmā.

Apache gadījumā rediģējiet savu `.htaccess` failu ar šādu:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Piezīme**: Ja nepieciešams izmantot flight apakšdirektorijā, pievienojiet rindu
> `RewriteBase /subdir/` tieši pēc `RewriteEngine On`.

> **Piezīme**: Ja vēlaties aizsargāt visus servera failus, piemēram, db vai env failu.
> Ievietojiet to savā `.htaccess` failā:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Pārliecinieties, ka Nginx jau ir instalēts jūsu sistēmā. Ja nē, meklējiet Google, kā instalēt Nginx jūsu sistēmā.

Nginx gadījumā pievienojiet šādu savai servera deklarācijai:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Izveidojiet savu `index.php` failu

Ja veicat pamatinstalēšanu, jums būs nepieciešams kāds kods, lai sāktu.

```php
<?php

// Ja izmantojat Composer, iekļaujiet autoloader.
require 'vendor/autoload.php';
// ja neizmantojat Composer, ielādējiet framework tieši
// require 'flight/Flight.php';

// Tad definējiet maršrutu un piešķiriet funkciju, lai apstrādātu pieprasījumu.
Flight::route('/', function () {
  echo 'hello world!';
});

// Visbeidzot, palaidiet framework.
Flight::start();
```

Ar skeleton lietotni tas jau ir konfigurēts un apstrādāts jūsu `app/config/routes.php` failā. Pakalpojumi ir konfigurēti `app/config/services.php`.

## PHP instalēšana

Ja jums jau ir instalēts `php` jūsu sistēmā, izlaidiet šīs instrukcijas un pārejiet uz [lejupielādes sadaļu](#download-the-files).

### **macOS**

#### **PHP instalēšana, izmantojot Homebrew**

1. **Instalējiet Homebrew** (ja vēl nav instalēts):
   - Atveriet Termināli un izpildiet:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Instalējiet PHP**:
   - Instalējiet jaunāko versiju:
     ```bash
     brew install php
     ```
   - Lai instalētu specifisku versiju, piemēram, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Pārslēdzieties starp PHP versijām**:
   - Atvienojiet pašreizējo versiju un saistiet vēlamo versiju:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - Pārbaudiet instalēto versiju:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **PHP instalēšana manuāli**

1. **Lejupielādējiet PHP**:
   - Apmeklējiet [PHP for Windows](https://windows.php.net/download/) un lejupielādējiet jaunāko vai specifisku versiju (piem., 7.4, 8.0) kā ne-vītu drošu zip failu.

2. **Izvelciet PHP**:
   - Izvelciet lejupielādēto zip failu uz `C:\php`.

3. **Pievienojiet PHP sistēmas PATH**:
   - Dodieties uz **Sistēmas īpašībām** > **Vides mainīgajiem**.
   - Sadaļā **Sistēmas mainīgie**, atrast **Path** un noklikšķiniet **Rediģēt**.
   - Pievienojiet ceļu `C:\php` (vai kur izvelcāt PHP).
   - Noklikšķiniet **Labi**, lai aizvērtu visus logus.

4. **Konfigurējiet PHP**:
   - Kopējiet `php.ini-development` uz `php.ini`.
   - Rediģējiet `php.ini`, lai konfigurētu PHP pēc vajadzības (piem., iestatiet `extension_dir`, iespējiet paplašinājumus).

5. **Pārbaudiet PHP instalēšanu**:
   - Atveriet Komandu uzvedni un izpildiet:
     ```cmd
     php -v
     ```

#### **Vairāku PHP versiju instalēšana**

1. **Atkārtojiet iepriekšējos soļus** katrai versijai, izvietojot katru atsevišķā direktorijā (piem., `C:\php7`, `C:\php8`).

2. **Pārslēdzieties starp versijām**, pielāgojot sistēmas PATH mainīgo, lai norādītu uz vēlamo versijas direktoriju.

### **Ubuntu (20.04, 22.04 utt.)**

#### **PHP instalēšana, izmantojot apt**

1. **Atjauniniet paketes sarakstus**:
   - Atveriet Termināli un izpildiet:
     ```bash
     sudo apt update
     ```

2. **Instalējiet PHP**:
   - Instalējiet jaunāko PHP versiju:
     ```bash
     sudo apt install php
     ```
   - Lai instalētu specifisku versiju, piemēram, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Instalējiet papildu moduļus** (pēc izvēles):
   - Piemēram, lai instalētu MySQL atbalstu:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Pārslēdzieties starp PHP versijām**:
   - Izmantojiet `update-alternatives`:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Pārbaudiet instalēto versiju**:
   - Izpildiet:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **PHP instalēšana, izmantojot yum/dnf**

1. **Iespējojiet EPEL repozitoriju**:
   - Atveriet Termināli un izpildiet:
     ```bash
     sudo dnf install epel-release
     ```

2. **Instalējiet Remi's repozitoriju**:
   - Izpildiet:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **Instalējiet PHP**:
   - Lai instalētu noklusēto versiju:
     ```bash
     sudo dnf install php
     ```
   - Lai instalētu specifisku versiju, piemēram, PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Pārslēdzieties starp PHP versijām**:
   - Izmantojiet `dnf` moduļa komandu:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Pārbaudiet instalēto versiju**:
   - Izpildiet:
     ```bash
     php -v
     ```

### **Vispārīgas piezīmes**

- Izstrādes vidēs ir svarīgi konfigurēt PHP iestatījumus saskaņā ar jūsu projekta prasībām. 
- Pārslēdzoties uz PHP versijām, pārliecinieties, ka visas attiecīgās PHP paplašinājumi ir instalēti specifiskajai versijai, ko plānojat izmantot.
- Pārsāciet savu tīmekļa serveri (Apache, Nginx utt.) pēc PHP versiju pārslēgšanas vai konfigurāciju atjaunināšanas, lai piemērotu izmaiņas.