# Integración de WordPress: n0nag0n/wordpress-integration-for-flight-framework

¿Quieres usar Flight PHP dentro de tu sitio de WordPress? ¡Este plugin lo hace muy fácil! Con `n0nag0n/wordpress-integration-for-flight-framework`, puedes ejecutar una aplicación completa de Flight junto con tu instalación de WordPress, perfecto para construir APIs personalizadas, microservicios o incluso aplicaciones completas sin salir de la comodidad de WordPress.

---

## ¿Qué hace?

- **Integra de manera perfecta Flight PHP con WordPress**
- Enruta solicitudes a Flight o WordPress según patrones de URL
- Organiza tu código con controladores, modelos y vistas (MVC)
- Configura fácilmente la estructura de carpetas recomendada de Flight
- Usa la conexión de base de datos de WordPress o la tuya propia
- Ajusta finamente cómo interactúan Flight y WordPress
- Interfaz de administración simple para la configuración

## Instalación

1. Sube la carpeta `flight-integration` a tu directorio `/wp-content/plugins/`.
2. Activa el plugin en la administración de WordPress (menú de Plugins).
3. Ve a **Settings > Flight Framework** para configurar el plugin.
4. Establece la ruta del proveedor a tu instalación de Flight (o usa Composer para instalar Flight).
5. Configura la ruta de tu carpeta de aplicación y crea la estructura de carpetas (¡el plugin puede ayudarte con esto!).
6. ¡Comienza a construir tu aplicación de Flight!

## Ejemplos de Uso

### Ejemplo Básico de Ruta
En tu archivo `app/config/routes.php`:

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### Ejemplo de Controlador

Crea un controlador en `app/controllers/ApiController.php`:

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // ¡Puedes usar funciones de WordPress dentro de Flight!
        $users = get_users();
        $result = [];
        foreach($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            ];
        }
        Flight::json($result);
    }
}
```

Luego, en tu `routes.php`:

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## Preguntas Frecuentes

**P: ¿Necesito conocer Flight para usar este plugin?**  
R: Sí, esto es para desarrolladores que quieran usar Flight dentro de WordPress. Se recomienda un conocimiento básico de enrutamiento y manejo de solicitudes de Flight.

**P: ¿Esto ralentizará mi sitio de WordPress?**  
R: ¡No! El plugin solo procesa solicitudes que coincidan con tus rutas de Flight. Todas las demás solicitudes van a WordPress como de costumbre.

**P: ¿Puedo usar funciones de WordPress en mi aplicación de Flight?**  
R: ¡Absolutamente! Tienes acceso completo a todas las funciones, hooks y globales de WordPress desde dentro de tus rutas y controladores de Flight.

**P: ¿Cómo creo rutas personalizadas?**  
R: Define tus rutas en el archivo `config/routes.php` en tu carpeta de aplicación. Consulta el archivo de muestra creado por el generador de estructura de carpetas para ejemplos.

## Registro de Cambios

**1.0.0**  
Lanzamiento inicial.

---

Para más información, consulta el [GitHub repo](https://github.com/n0nag0n/wordpress-integration-for-flight-framework).