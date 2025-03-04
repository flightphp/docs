# Instalācija

## Lejupielādējiet failus

Pārliecinieties, ka jūsu sistēmā ir instalēts PHP. Ja tas nav, noklikšķiniet [šeit](#installing-php), lai iegūtu norādes par to, kā to instalēt savai sistēmai.

Ja izmantojat [Composer](https://getcomposer.org), varat izpildīt šādu komandu:

```bash
composer require flightphp/core
```

VAI arī varat failus [lejupielādēt šeit](https://github.com/flightphp/core/archive/master.zip) tieši un izpauzēt tos savā tīmekļa katalogā.

## Konfigurējiet savu tīmekļa serveri

### Iebūvētais PHP attīstības serveris

Šis ir pa tālu vienkāršākais veids, kā sākt darbu. Jūs varat izmantot iebūvēto serveri, lai palaistu savu lietotni un pat izmantotu SQLite datu bāzi (pilnībā atbalstīts sqlite3 jūsu sistēmā) un neprasītu pilnīgi neko! Vienkārši izpildiet šo komandu, kad PHP ir instalēts:

```bash
php -S localhost:8000
```

Tad atveriet pārlūkprogrammu un dodieties uz `http://localhost:8000`.

Ja jūs vēlaties padarīt savas projekta dokumentu saknes mapes citu direktoriju (Piem.: jūsu projekts ir `~/mansprojekts`, bet jūsu dokumentu sakne ir `~/mansprojekts/public/`), tad varat izpildīt šo komandu, kad atrodaties `~/mansprojekts` direktorijā:

```bash
php -S localhost:8000 -t public/
```

Tad atveriet pārlūkprogrammu un dodieties uz `http://localhost:8000`.

### Apache

Pārliecinieties, ka Apache jau ir instalēts jūsu sistēmā. Ja nē, meklējiet, kā instalēt Apache savā sistēmā.

Attiecībā uz Apache rediģējiet savu `.htaccess` failu ar šādiem ierakstiem:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Piezīme**: Ja jums ir jāizmanto Flight apakšdirektorijā, pievienojiet rindu
> `RewriteBase /apaksmappe/` tieši pēc `RewriteEngine On`.

> **Piezīme**: Ja vēlaties aizsargāt visus servera failus, piem., datu bāzes vai env failus.
> Ievietojiet šo savā `.htaccess` failā:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Pārliecinieties, ka Nginx jau ir instalēts jūsu sistēmā. Ja nē, meklējiet, kā instalēt Nginx savā sistēmā.

Attiecībā uz Nginx pievienojiet šo savā servera norādē:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Izveidojiet savu `index.php` failu

```php
<?php

// Ja izmantojat Composer, pieprasiet autoloāderi.
require 'vendor/autoload.php';
// ja nelietojat Composer, ielādējiet framework tieši
// require 'flight/Flight.php';

// Pēc tam definējiet maršrutu un piešķiriet funkciju, kas apstrādā pieprasījumu.
Flight::route('/', function () {
  echo 'sveika pasaule!';
});

// Beigās startējiet framework.
Flight::start();
```

## PHP instalēšana

Ja jums jau ir instalēts `php` jūsu sistēmā, droši turpiniet šīs norādes un pārietiet uz [lejupielādes sadaļu](#download-the-files)

Protams! Šeit ir norādes, kā instalēt PHP uz macOS, Windows 10/11, Ubuntu un Rocky Linux. Arī iekļauti būs detalizēti ieteikumi par dažādu PHP versiju instalēšanu.

### **macOS**

#### **PHP instalēšana izmantojot Homebrew**

1. **Instalējiet Homebrew** (ja vēl nav instalēts):
   - Atveriet termināli un izpildiet:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Instalējiet PHP**:
   - Instalējiet jaunāko versiju:
     ```bash
     brew install php
     ```
   - Lai instalētu konkrētu versiju, piemēram, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Pārslēdzieties starp PHP versijām**:
   - Atslēdziet pašreizējo versiju un pievienojiet vēlamo versiju:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - Pārbaudiet instalēto versiju:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **PHP manuālā instalācija**

1. **Lejupielādējiet PHP**:
   - Apmeklējiet [PHP Windows versijas](https://windows.php.net/download/) un lejupielādējiet jaunāko vai konkrētu versiju (piem., 7.4, 8.0) kā zip failu bez pavedieniem.

2. **Izpakošana PHP**:
   - Izpako lejupielādēto zip failu uz `C:\php`.

3. **Pievienojiet PHP sistēmas PATH**:
   - Dodies uz **Sistēmas īpašības** > **Vides mainīgie**.
   - Sadaļā **Sistēmas mainīgie** atradīt **Ceļš** un noklikšķiniet uz **Rediģēt**.
   - Pievienojiet ceļu `C:\php` (vai kur izpakojāt PHP).
   - Noklikšķiniet uz **Labi**, lai aizvērtu visus logus.

4. **Konfigurējiet PHP**:
   - Kopējiet `php.ini-development` uz `php.ini`.
   - Rediģējiet `php.ini`, lai konfigurētu PHP pēc nepieciešamības (piem., iestatiet `extension_dir`, aktivizējiet paplašinājumus).

5. **Pārbaudiet PHP instalāciju**:
   - Atveriet komandrindu un izpildiet:
     ```cmd
     php -v
     ```

#### **Vairāku PHP versiju instalēšana**

1. **Atkārtojiet iepriekšminētos soļus** katrai versijai, ievietojot katru atsevišķā mapē (piem., `C:\php7`, `C:\php8`).

2. **Pārslēdzieties starp versijām**, pielāgojot sistēmas PATH mainīgo, lai norādītu uz vēlamo versijas direktoriju.

### **Ubuntu (20.04, 22.04, utt.)**

#### **PHP instalēšana, izmantojot apt**

1. **Atjauniniet pakotņu sarakstus**:
   - Atveriet termināli un izpildiet:
     ```bash
     sudo apt update
     ```

2. **Instalējiet PHP**:
   - Uzstādiet jaunāko PHP versiju:
     ```bash
     sudo apt install php
     ```
   - Lai instalētu konkrētu versiju, piemēram, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Instalējiet papildu moduļus** (nav obligāti):
   - Piemēram, lai instalētu MySQL atbalstu:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Pārslēdzieties starp PHP versijām**:
   - Lietojiet `update-alternatives`:
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
   - Atveriet termināli un izpildiet:
     ```bash
     sudo dnf install epel-release
     ```

2. **Uzstādiet Remi repozitoriju**:
   - Izpildiet:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **Instalējiet PHP**:
   - Lai instalētu noklusējuma versiju:
     ```bash
     sudo dnf install php
     ```
   - Lai instalētu konkrētu versiju, piemēram, PHP 7.4:
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

- Lai izstrādes vidēs būtu svarīgi konfigurēt PHP iestatījumus atbilstoši jūsu projekta prasībām. 
- Nomainot PHP versijas, pārliecinieties, ka visi attiecīgie PHP paplašinājumi ir instalēti attiecīgajai versijai, kuru plānojat izmantot.
- Pēc PHP versiju maiņas vai konfigurāciju atjaunošanas, restartējiet savu tīmekļa serveri (Apache, Nginx, u.c.), lai piemērotu izmaiņas.