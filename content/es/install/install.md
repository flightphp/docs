# Instalación

## Descargar los archivos

Asegúrate de tener PHP instalado en tu sistema. Si no lo tienes, haz clic [aquí](#instalar-php) para obtener instrucciones sobre cómo instalarlo en tu sistema.

Si estás utilizando [Composer](https://getcomposer.org), puedes ejecutar el siguiente comando:

```bash
composer require flightphp/core
```

O puedes [descargar los archivos](https://github.com/flightphp/core/archive/master.zip) directamente y extraerlos en tu directorio web.

## Configurar tu Servidor Web

### Servidor de Desarrollo PHP Integrado

Esta es la forma más sencilla de poner en marcha. Puedes utilizar el servidor integrado para ejecutar tu aplicación e incluso usar SQLite como base de datos (si sqlite3 está instalado en tu sistema) ¡y no requiere mucho más! Simplemente ejecuta el siguiente comando una vez que PHP esté instalado:

```bash
php -S localhost:8000
```

Luego abre tu navegador e ingresa a `http://localhost:8000`.

Si deseas establecer como directorio raíz de tu proyecto un directorio diferente (por ejemplo, tu proyecto es `~/myproject`, pero tu directorio raíz es `~/myproject/public/`), puedes ejecutar el siguiente comando una vez que te encuentres en el directorio `~/myproject`:

```bash
php -S localhost:8000 -t public/
```

Luego abre tu navegador e ingresa a `http://localhost:8000`.

### Apache

Asegúrate de que Apache ya esté instalado en tu sistema. Si no lo está, busca cómo instalar Apache en tu sistema.

Para Apache, edita tu archivo `.htaccess` con lo siguiente:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Nota**: Si necesitas usar flight en un subdirectorio, agrega la línea
> `RewriteBase /subdir/` justo después de `RewriteEngine On`.

> **Nota**: Si deseas proteger todos los archivos del servidor, como un archivo db o env.
> Coloca lo siguiente en tu archivo `.htaccess`:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Asegúrate de que Nginx ya esté instalado en tu sistema. Si no lo está, busca cómo instalar Nginx en tu sistema.

Para Nginx, agrega lo siguiente a la declaración de tu servidor:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Crea tu archivo `index.php`

```php
<?php

// Si estás utilizando Composer, require el autoloader.
require 'vendor/autoload.php';
// si no estás utilizando Composer, carga el framework directamente
// require 'flight/Flight.php';

// Luego define una ruta y asigna una función para manejar la solicitud.
Flight::route('/', function () {
  echo '¡hola mundo!';
});

// Finalmente, inicia el framework.
Flight::start();
```

## Instalación de PHP

Si ya tienes `php` instalado en tu sistema, puedes saltarte estas instrucciones y pasar a [la sección de descarga](#descargar-los-archivos)

¡Claro! Aquí tienes las instrucciones para instalar PHP en macOS, Windows 10/11, Ubuntu y Rocky Linux. También incluiré detalles sobre cómo instalar diferentes versiones de PHP.

### **macOS**

#### **Instalar PHP usando Homebrew**

1. **Instalar Homebrew** (si aún no está instalado):
   - Abre Terminal y ejecuta:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Instalar PHP**:
   - Instalar la última versión:
     ```bash
     brew install php
     ```
   - Para instalar una versión específica, por ejemplo, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Cambiar entre versiones de PHP**:
   - Desvincula la versión actual y enlaza la versión deseada:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - Verifica la versión instalada:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **Instalar PHP manualmente**

1. **Descargar PHP**:
   - Visita [PHP for Windows](https://windows.php.net/download/) y descarga la última versión o una específica (por ejemplo, 7.4, 8.0) como un archivo zip no seguro para subprocesos.

2. **Extraer PHP**:
   - Extrae el archivo zip descargado en `C:\php`.

3. **Agregar PHP al PATH del sistema**:
   - Ve a **Propiedades del Sistema** > **Variables de Entorno**.
   - En **Variables del sistema**, encuentra **Path** y haz clic en **Editar**.
   - Agrega la ruta `C:\php` (o donde hayas extraído PHP).
   - Haz clic en **Aceptar** para cerrar todas las ventanas.

4. **Configurar PHP**:
   - Copia `php.ini-development` a `php.ini`.
   - Edita `php.ini` para configurar PHP según sea necesario (por ejemplo, configurar `extension_dir`, habilitar extensiones).

5. **Verificar la instalación de PHP**:
   - Abre el Símbolo del sistema y ejecuta:
     ```cmd
     php -v
     ```

#### **Instalar Múltiples Versiones de PHP**

1. **Repite los pasos anteriores** para cada versión, colocando cada una en un directorio separado (por ejemplo, `C:\php7`, `C:\php8`).

2. **Cambiar entre versiones** ajustando la variable PATH del sistema para que apunte al directorio de la versión deseada.

### **Ubuntu (20.04, 22.04, etc.)**

#### **Instalar PHP usando apt**

1. **Actualizar listas de paquetes**:
   - Abre Terminal y ejecuta:
     ```bash
     sudo apt update
     ```

2. **Instalar PHP**:
   - Instalar la última versión de PHP:
     ```bash
     sudo apt install php
     ```
   - Para instalar una versión específica, por ejemplo, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Instalar módulos adicionales** (opcional):
   - Por ejemplo, para instalar soporte para MySQL:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Cambiar entre versiones de PHP**:
   - Usa `update-alternatives`:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Verificar la versión instalada**:
   - Ejecuta:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **Instalar PHP usando yum/dnf**

1. **Habilitar el repositorio EPEL**:
   - Abre Terminal y ejecuta:
     ```bash
     sudo dnf install epel-release
     ```

2. **Instalar el repositorio de Remi**:
   - Ejecuta:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **Instalar PHP**:
   - Para instalar la versión predeterminada:
     ```bash
     sudo dnf install php
     ```
   - Para instalar una versión específica, por ejemplo, PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Cambiar entre versiones de PHP**:
   - Usa el comando de módulo `dnf`:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Verificar la versión instalada**:
   - Ejecuta:
     ```bash
     php -v
     ```

### **Notas Generales**

- Para entornos de desarrollo, es importante configurar los ajustes de PHP según los requerimientos de tu proyecto.
- Al cambiar entre versiones de PHP, asegúrate de que todas las extensiones relevantes de PHP estén instaladas para la versión específica que deseas utilizar.
- Reinicia tu servidor web (Apache, Nginx, etc.) después de cambiar de versión de PHP o actualizar configuraciones para aplicar los cambios.