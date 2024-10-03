# Installation

## Download the files

Make sure you have PHP installed on your system. If not, click [here](#installing-php) for instructions on how to install it for your system.

If you're using [Composer](https://getcomposer.org), you can run the following
command:

```bash
composer require flightphp/core
```

OR you can [download the files](https://github.com/flightphp/core/archive/master.zip)
 directly and extract them to your web directory.

## Configure your Web Server

### Built-in PHP Development Server

This is by far the simplest way to get up and running. You can use the built-in server to run your application and even use SQLite for a database (as long as sqlite3 is installed on your system) and not require much of anything! Just run the following command once PHP is installed:

```bash
php -S localhost:8000
```

Then open your browser and go to `http://localhost:8000`.

If you want to make the document root of your project a different directory (Ex: your project is `~/myproject`, but your document root is `~/myproject/public/`), you can run the following command once your in the `~/myproject` directory:

```bash
php -S localhost:8000 -t public/
```

Then open your browser and go to `http://localhost:8000`.

### Apache

Make sure Apache is already installed on your system. If not, google how to install Apache on your system.

For Apache, edit your `.htaccess` file with the following:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Note**: If you need to use flight in a subdirectory add the line
> `RewriteBase /subdir/` just after `RewriteEngine On`.

> **Note**: If you want to protect all server files, like a db or env file.
> Put this in your `.htaccess` file:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Make sure Nginx is already installed on your system. If not, google how to Nginx Apache on your system.

For Nginx, add the following to your server declaration:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Create your `index.php` file

```php
<?php

// If you're using Composer, require the autoloader.
require 'vendor/autoload.php';
// if you're not using Composer, load the framework directly
// require 'flight/Flight.php';

// Then define a route and assign a function to handle the request.
Flight::route('/', function () {
  echo 'hello world!';
});

// Finally, start the framework.
Flight::start();
```

## Installing PHP

If you already have `php` installed on your system, go ahead and skip these instructions and move to [the download section](#download-the-files)

### **macOS**

#### **Installing PHP using Homebrew**

1. **Install Homebrew** (if not already installed):
   - Open Terminal and run:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Install PHP**:
   - Install the latest version:
     ```bash
     brew install php
     ```
   - To install a specific version, for example, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Switch between PHP versions**:
   - Unlink the current version and link the desired version:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - Verify the installed version:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **Installing PHP manually**

1. **Download PHP**:
   - Visit [PHP for Windows](https://windows.php.net/download/) and download the latest or a specific version (e.g., 7.4, 8.0) as a non-thread-safe zip file.

2. **Extract PHP**:
   - Extract the downloaded zip file to `C:\php`.

3. **Add PHP to the system PATH**:
   - Go to **System Properties** > **Environment Variables**.
   - Under **System variables**, find **Path** and click **Edit**.
   - Add the path `C:\php` (or wherever you extracted PHP).
   - Click **OK** to close all windows.

4. **Configure PHP**:
   - Copy `php.ini-development` to `php.ini`.
   - Edit `php.ini` to configure PHP as needed (e.g., setting `extension_dir`, enabling extensions).

5. **Verify PHP installation**:
   - Open Command Prompt and run:
     ```cmd
     php -v
     ```

#### **Installing Multiple Versions of PHP**

1. **Repeat the above steps** for each version, placing each in a separate directory (e.g., `C:\php7`, `C:\php8`).

2. **Switch between versions** by adjusting the system PATH variable to point to the desired version directory.

### **Ubuntu (20.04, 22.04, etc.)**

#### **Installing PHP using apt**

1. **Update package lists**:
   - Open Terminal and run:
     ```bash
     sudo apt update
     ```

2. **Install PHP**:
   - Install the latest PHP version:
     ```bash
     sudo apt install php
     ```
   - To install a specific version, for example, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Install additional modules** (optional):
   - For example, to install MySQL support:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Switch between PHP versions**:
   - Use `update-alternatives`:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Verify the installed version**:
   - Run:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **Installing PHP using yum/dnf**

1. **Enable the EPEL repository**:
   - Open Terminal and run:
     ```bash
     sudo dnf install epel-release
     ```

2. **Install Remi's repository**:
   - Run:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **Install PHP**:
   - To install the default version:
     ```bash
     sudo dnf install php
     ```
   - To install a specific version, for example, PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Switch between PHP versions**:
   - Use the `dnf` module command:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Verify the installed version**:
   - Run:
     ```bash
     php -v
     ```

### **General Notes**

- For development environments, it's important to configure PHP settings as per your project requirements. 
- When switching PHP versions, ensure all relevant PHP extensions are installed for the specific version you intend to use.
- Restart your web server (Apache, Nginx, etc.) after switching PHP versions or updating configurations to apply changes.