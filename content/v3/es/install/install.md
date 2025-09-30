# Instrucciones de Instalación

Hay algunos prerrequisitos básicos antes de que puedas instalar Flight. Específicamente, necesitarás:

1. [Instalar PHP en tu sistema](#instalando-php)
2. [Instalar Composer](https://getcomposer.org) para la mejor experiencia de desarrollo.

## Instalación Básica

Si estás usando [Composer](https://getcomposer.org), puedes ejecutar el siguiente
comando:

```bash
composer require flightphp/core
```

Esto solo colocará los archivos principales de Flight en tu sistema. Necesitarás definir la estructura del proyecto, [diseño](/learn/templates), [dependencias](/learn/dependency-injection-container), [configuraciones](/learn/configuration), [carga automática](/learn/autoloading), etc. Este método asegura que no se instalen otras dependencias además de Flight.

También puedes [descargar los archivos](https://github.com/flightphp/core/archive/master.zip)
directamente y extraerlos a tu directorio web.

## Instalación Recomendada

Se recomienda altamente comenzar con la aplicación [flightphp/skeleton](https://github.com/flightphp/skeleton) para cualquier proyecto nuevo. La instalación es muy sencilla.

```bash
composer create-project flightphp/skeleton my-project/
```

Esto configurará la estructura de tu proyecto, configurará la carga automática con espacios de nombres, configurará una configuración y proporcionará otras herramientas como [Tracy](/awesome-plugins/tracy), [Extensiones de Tracy](/awesome-plugins/tracy-extensions) y [Runway](/awesome-plugins/runway).

## Configura tu Servidor Web

### Servidor de Desarrollo PHP Integrado

Esta es, con mucho, la forma más simple de comenzar. Puedes usar el servidor integrado para ejecutar tu aplicación e incluso usar SQLite para una base de datos (siempre y cuando sqlite3 esté instalado en tu sistema) y no requerir mucho más. Solo ejecuta el siguiente comando una vez que PHP esté instalado:

```bash
php -S localhost:8000
# o con la aplicación skeleton
composer start
```

Luego abre tu navegador e ingresa a `http://localhost:8000`.

Si quieres hacer que la raíz de documentos de tu proyecto sea un directorio diferente (Ej: tu proyecto es `~/myproject`, pero tu raíz de documentos es `~/myproject/public/`), puedes ejecutar el siguiente comando una vez que estés en el directorio `~/myproject`:

```bash
php -S localhost:8000 -t public/
# con la aplicación skeleton, esto ya está configurado
composer start
```

Luego abre tu navegador e ingresa a `http://localhost:8000`.

### Apache

Asegúrate de que Apache ya esté instalado en tu sistema. Si no, busca en Google cómo instalar Apache en tu sistema.

Para Apache, edita tu archivo `.htaccess` con lo siguiente:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Nota**: Si necesitas usar flight en un subdirectorio, agrega la línea
> `RewriteBase /subdir/` justo después de `RewriteEngine On`.

> **Nota**: Si quieres proteger todos los archivos del servidor, como un archivo db o env.
> Pon esto en tu archivo `.htaccess`:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Asegúrate de que Nginx ya esté instalado en tu sistema. Si no, busca en Google cómo instalar Nginx en tu sistema.

Para Nginx, agrega lo siguiente a tu declaración de servidor:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Crea tu archivo `index.php`

Si estás haciendo una instalación básica, necesitarás algo de código para comenzar.

```php
<?php

// Si estás usando Composer, requiere el cargador automático.
require 'vendor/autoload.php';
// si no estás usando Composer, carga el framework directamente
// require 'flight/Flight.php';

// Luego define una ruta y asigna una función para manejar la solicitud.
Flight::route('/', function () {
  echo 'hello world!';
});

// Finalmente, inicia el framework.
Flight::start();
```

Con la aplicación skeleton, esto ya está configurado y manejado en tu archivo `app/config/routes.php`. Los servicios se configuran en `app/config/services.php`.

## Instalando PHP

Si ya tienes `php` instalado en tu sistema, salta estas instrucciones y ve a [la sección de descarga](#download-the-files).

### **macOS**

#### **Instalando PHP usando Homebrew**

1. **Instala Homebrew** (si no está ya instalado):
   - Abre Terminal y ejecuta:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Instala PHP**:
   - Instala la versión más reciente:
     ```bash
     brew install php
     ```
   - Para instalar una versión específica, por ejemplo, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Cambia entre versiones de PHP**:
   - Desvincula la versión actual y vincula la versión deseada:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - Verifica la versión instalada:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **Instalando PHP manualmente**

1. **Descarga PHP**:
   - Visita [PHP for Windows](https://windows.php.net/download/) y descarga la versión más reciente o una específica (p.ej., 7.4, 8.0) como un archivo zip no seguro para hilos.

2. **Extrae PHP**:
   - Extrae el archivo zip descargado a `C:\php`.

3. **Agrega PHP al PATH del sistema**:
   - Ve a **Propiedades del Sistema** > **Variables de Entorno**.
   - Bajo **Variables del sistema**, encuentra **Path** y haz clic en **Editar**.
   - Agrega la ruta `C:\php` (o dondequiera que hayas extraído PHP).
   - Haz clic en **Aceptar** para cerrar todas las ventanas.

4. **Configura PHP**:
   - Copia `php.ini-development` a `php.ini`.
   - Edita `php.ini` para configurar PHP según sea necesario (p.ej., estableciendo `extension_dir`, habilitando extensiones).

5. **Verifica la instalación de PHP**:
   - Abre el Símbolo del sistema y ejecuta:
     ```cmd
     php -v
     ```

#### **Instalando Múltiples Versiones de PHP**

1. **Repite los pasos anteriores** para cada versión, colocándolas en un directorio separado (p.ej., `C:\php7`, `C:\php8`).

2. **Cambia entre versiones** ajustando la variable PATH del sistema para apuntar al directorio de la versión deseada.

### **Ubuntu (20.04, 22.04, etc.)**

#### **Instalando PHP usando apt**

1. **Actualiza las listas de paquetes**:
   - Abre Terminal y ejecuta:
     ```bash
     sudo apt update
     ```

2. **Instala PHP**:
   - Instala la versión más reciente de PHP:
     ```bash
     sudo apt install php
     ```
   - Para instalar una versión específica, por ejemplo, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Instala módulos adicionales** (opcional):
   - Por ejemplo, para instalar soporte para MySQL:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Cambia entre versiones de PHP**:
   - Usa `update-alternatives`:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Verifica la versión instalada**:
   - Ejecuta:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **Instalando PHP usando yum/dnf**

1. **Habilita el repositorio EPEL**:
   - Abre Terminal y ejecuta:
     ```bash
     sudo dnf install epel-release
     ```

2. **Instala el repositorio de Remi**:
   - Ejecuta:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **Instala PHP**:
   - Para instalar la versión predeterminada:
     ```bash
     sudo dnf install php
     ```
   - Para instalar una versión específica, por ejemplo, PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Cambia entre versiones de PHP**:
   - Usa el comando de módulo `dnf`:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Verifica la versión instalada**:
   - Ejecuta:
     ```bash
     php -v
     ```

### **Notas Generales**

- Para entornos de desarrollo, es importante configurar las opciones de PHP según los requisitos de tu proyecto. 
- Al cambiar versiones de PHP, asegúrate de que todas las extensiones relevantes de PHP estén instaladas para la versión específica que pretendes usar.
- Reinicia tu servidor web (Apache, Nginx, etc.) después de cambiar versiones de PHP o actualizar configuraciones para aplicar los cambios.