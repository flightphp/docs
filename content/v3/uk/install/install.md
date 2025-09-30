# Інструкції з встановлення

Є деякі базові передумови перед тим, як ви зможете встановити Flight. Зокрема, вам потрібно буде:

1. [Встановити PHP на вашу систему](#installing-php)
2. [Встановити Composer](https://getcomposer.org) для найкращого досвіду розробника.

## Базове встановлення

Якщо ви використовуєте [Composer](https://getcomposer.org), ви можете виконати таку команду:

```bash
composer require flightphp/core
```

Це встановить лише основні файли Flight на вашу систему. Вам потрібно буде визначити структуру проекту, [макет](/learn/templates), [залежності](/learn/dependency-injection-container), [конфігурації](/learn/configuration), [автозавантаження](/learn/autoloading) тощо. Цей метод забезпечує, що не встановлюються інші залежності, крім Flight.

Ви також можете [завантажити файли](https://github.com/flightphp/core/archive/master.zip)
 безпосередньо та розпакувати їх до вашої веб-каталоги.

## Рекомендоване встановлення

Високо рекомендується починати з додатку [flightphp/skeleton](https://github.com/flightphp/skeleton) для будь-яких нових проектів. Встановлення дуже просте.

```bash
composer create-project flightphp/skeleton my-project/
```

Це налаштує структуру вашого проекту, налаштує автозавантаження з просторами імен, налаштує конфігурацію та надасть інші інструменти, такі як [Tracy](/awesome-plugins/tracy), [Tracy Extensions](/awesome-plugins/tracy-extensions) та [Runway](/awesome-plugins/runway)

## Налаштування вашого веб-сервера

### Вбудований сервер розробки PHP

Це найпростіший спосіб запустити все. Ви можете використовувати вбудований сервер для запуску вашого додатку та навіть використовувати SQLite для бази даних (за умови, що sqlite3 встановлено на вашій системі) і не вимагати нічого особливого! Просто виконайте таку команду після встановлення PHP:

```bash
php -S localhost:8000
# або з додатком skeleton
composer start
```

Потім відкрийте браузер та перейдіть до `http://localhost:8000`.

Якщо ви хочете зробити корінь документів вашого проекту іншою директорією (Наприклад: ваш проект `~/myproject`, але корінь документів `~/myproject/public/`), ви можете виконати таку команду, перебуваючи в директорії `~/myproject`:

```bash
php -S localhost:8000 -t public/
# з додатком skeleton це вже налаштовано
composer start
```

Потім відкрийте браузер та перейдіть до `http://localhost:8000`.

### Apache

Переконайтеся, що Apache вже встановлено на вашій системі. Якщо ні, погугліть, як встановити Apache на вашу систему.

Для Apache відредагуйте ваш файл `.htaccess` з наступним:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Примітка**: Якщо вам потрібно використовувати flight в піддиректорії, додайте рядок
> `RewriteBase /subdir/` відразу після `RewriteEngine On`.

> **Примітка**: Якщо ви хочете захистити всі серверні файли, наприклад, файл бази даних або env.
> Помістіть це у ваш файл `.htaccess`:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Переконайтеся, що Nginx вже встановлено на вашій системі. Якщо ні, погугліть, як встановити Nginx на вашу систему.

Для Nginx додайте наступне до вашого оголошення сервера:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Створення вашого файлу `index.php`

Якщо ви виконуєте базове встановлення, вам потрібно буде мати деякий код для початку.

```php
<?php

// If you're using Composer, require the autoloader.
// Якщо ви використовуєте Composer, підключіть автозавантажувач.
require 'vendor/autoload.php';
// if you're not using Composer, load the framework directly
// якщо ви не використовуєте Composer, завантажте фреймворк безпосередньо
// require 'flight/Flight.php';

// Then define a route and assign a function to handle the request.
// Потім визначте маршрут та призначте функцію для обробки запиту.
Flight::route('/', function () {
  echo 'hello world!';
});

// Finally, start the framework.
// Нарешті, запустіть фреймворк.
Flight::start();
```

З додатком skeleton це вже налаштовано та обробляється у вашому файлі `app/config/routes.php`. Сервіси налаштовуються в `app/config/services.php`

## Встановлення PHP

Якщо у вас вже встановлено `php` на системі, пропустіть ці інструкції та перейдіть до [розділу завантаження](#download-the-files)

### **macOS**

#### **Встановлення PHP за допомогою Homebrew**

1. **Встановіть Homebrew** (якщо ще не встановлено):
   - Відкрийте Термінал та виконайте:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Встановіть PHP**:
   - Встановіть останню версію:
     ```bash
     brew install php
     ```
   - Щоб встановити конкретну версію, наприклад, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Перемикання між версіями PHP**:
   - Від’єднайте поточну версію та підключіть бажану:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - Перевірте встановлену версію:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **Встановлення PHP вручну**

1. **Завантажте PHP**:
   - Відвідайте [PHP for Windows](https://windows.php.net/download/) та завантажте останню або конкретну версію (наприклад, 7.4, 8.0) як zip-файл non-thread-safe.

2. **Розпакуйте PHP**:
   - Розпакуйте завантажений zip-файл до `C:\php`.

3. **Додайте PHP до системного PATH**:
   - Перейдіть до **Властивостей системи** > **Змінні середовища**.
   - Під **Системні змінні**, знайдіть **Path** та натисніть **Редагувати**.
   - Додайте шлях `C:\php` (або де ви розпакували PHP).
   - Натисніть **OK**, щоб закрити всі вікна.

4. **Налаштуйте PHP**:
   - Скопіюйте `php.ini-development` до `php.ini`.
   - Відредагуйте `php.ini`, щоб налаштувати PHP за потреби (наприклад, встановлення `extension_dir`, увімкнення розширень).

5. **Перевірте встановлення PHP**:
   - Відкрийте Командний рядок та виконайте:
     ```cmd
     php -v
     ```

#### **Встановлення кількох версій PHP**

1. **Повторіть наведені вище кроки** для кожної версії, розміщуючи кожну в окремій директорії (наприклад, `C:\php7`, `C:\php8`).

2. **Перемикання між версіями** шляхом коригування системної змінної PATH, щоб вказати на директорію бажаній версії.

### **Ubuntu (20.04, 22.04 тощо)**

#### **Встановлення PHP за допомогою apt**

1. **Оновіть списки пакетів**:
   - Відкрийте Термінал та виконайте:
     ```bash
     sudo apt update
     ```

2. **Встановіть PHP**:
   - Встановіть останню версію PHP:
     ```bash
     sudo apt install php
     ```
   - Щоб встановити конкретну версію, наприклад, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Встановіть додаткові модулі** (опціонально):
   - Наприклад, щоб встановити підтримку MySQL:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Перемикання між версіями PHP**:
   - Використовуйте `update-alternatives`:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Перевірте встановлену версію**:
   - Виконайте:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **Встановлення PHP за допомогою yum/dnf**

1. **Увімкніть репозиторій EPEL**:
   - Відкрийте Термінал та виконайте:
     ```bash
     sudo dnf install epel-release
     ```

2. **Встановіть репозиторій Remi's**:
   - Виконайте:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **Встановіть PHP**:
   - Щоб встановити версію за замовчуванням:
     ```bash
     sudo dnf install php
     ```
   - Щоб встановити конкретну версію, наприклад, PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Перемикання між версіями PHP**:
   - Використовуйте команду модуля `dnf`:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Перевірте встановлену версію**:
   - Виконайте:
     ```bash
     php -v
     ```

### **Загальні примітки**

- Для середовищ розробки важливо налаштувати параметри PHP відповідно до вимог вашого проекту. 
- При перемиканні версій PHP переконайтеся, що всі релевантні розширення PHP встановлено для конкретної версії, яку ви плануєте використовувати.
- Перезапустіть ваш веб-сервер (Apache, Nginx тощо) після перемикання версій PHP або оновлення конфігурацій, щоб застосувати зміни.