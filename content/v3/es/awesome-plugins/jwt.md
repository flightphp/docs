# Firebase JWT - Autenticación con JSON Web Token para Flight

JWT (JSON Web Tokens) son una forma compacta y segura para URLs de representar reclamaciones entre tu aplicación y un cliente. ¡Son perfectos para la autenticación de API sin estado, sin necesidad de almacenamiento de sesiones en el servidor! Esta guía te muestra cómo integrar [Firebase JWT](https://github.com/firebase/php-jwt) con Flight para una autenticación segura basada en tokens.

Visita el [repositorio de Github](https://github.com/firebase/php-jwt) para documentación completa y detalles.

## ¿Qué es JWT?

Un JSON Web Token es una cadena que contiene tres partes:
1. **Header**: Metadatos sobre el token (algoritmo, tipo)
2. **Payload**: Tus datos (ID de usuario, roles, expiración, etc.)
3. **Signature**: Firma criptográfica para verificar la autenticidad

Ejemplo de JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...` (parece gibberish, ¡pero es data estructurada!)

### ¿Por qué usar JWT?

- **Sin estado**: No se necesita almacenamiento de sesiones en el servidor, perfecto para microservicios y APIs
- **Escalable**: Funciona genial con balanceadores de carga ya que no hay requisito de afinidad de sesión
- **Multi-dominio**: Se puede usar a través de diferentes dominios y servicios
- **Amigable con móviles**: Genial para apps móviles donde las cookies pueden no funcionar bien
- **Estandarizado**: Enfoque estándar de la industria (RFC 7519)

## Instalación

Instala vía Composer:

```bash
composer require firebase/php-jwt
```

## Uso básico

Aquí hay un ejemplo rápido de creación y verificación de un JWT:

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Tu clave secreta (¡MANTÉNLA SEGURA!)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// Crear un token
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // Emitido en
    'exp' => time() + 3600        // Expira en 1 hora
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// Verificar y decodificar un token
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Invalid token: " . $e->getMessage();
}
```

## Middleware JWT para Flight (Enfoque recomendado)

La forma más común y útil de usar JWT con Flight es como **middleware** para proteger tus rutas de API. Aquí hay un ejemplo completo, listo para producción:

### Paso 1: Crear una clase de middleware JWT

```php
// app/middleware/JwtMiddleware.php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use flight\Engine;

class JwtMiddleware {

    protected Engine $app;
    protected string $secretKey;

    public function __construct(Engine $app) {
        $this->app = $app;
        // Almacena tu clave secreta en app/config/config.php, ¡NO la hardcodees!
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Verificar si existe el header de Authorization
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'No se proporcionó token de autorización'], 401);
        }

        // Extraer token del formato "Bearer <token>"
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Formato de autorización inválido. Usa: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // Decodificar y verificar el token
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // Almacenar datos de usuario en la solicitud para usar en manejadores de rutas
            $this->app->request()->data->user = $decoded;
            
        } catch (ExpiredException $e) {
            $this->app->jsonHalt(['error' => 'El token ha expirado'], 401);
        } catch (SignatureInvalidException $e) {
            $this->app->jsonHalt(['error' => 'Firma de token inválida'], 401);
        } catch (Exception $e) {
            $this->app->jsonHalt(['error' => 'Token inválido: ' . $e->getMessage()], 401);
        }
    }
}
```

### Paso 2: Registrar clave secreta JWT en tu configuración

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php o index.php
// asegúrate de agregar esta línea si quieres exponer la config a la app
$app->set('config', $config);
```

> **Nota de seguridad**: ¡Nunca hardcodees tu clave secreta! Usa variables de entorno en producción.

### Paso 3: Proteger tus rutas con middleware

```php
// Proteger una ruta individual
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // Establecido por el middleware
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware( JwtMiddleware::class);

// Proteger un grupo entero de rutas (¡más común!)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // ¡Todas las rutas en este grupo están protegidas!
```

Para más detalles sobre middleware, ve la [documentación de middleware](/learn/middleware).

## Casos de uso comunes

### 1. Endpoint de login (Generación de token)

Crea una ruta que genera un JWT después de una autenticación exitosa:

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // Validar credenciales (ejemplo - usa tu propia lógica!)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Credenciales inválidas'], 401);
    }

    // Generar JWT
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // Expiración en 1 hora
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // Tu búsqueda en base de datos y verificación de contraseña aquí
    // Ejemplo:
    $db = Flight::db();
    $user = $db->fetchRow("SELECT * FROM users WHERE username = ?", [$username]);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        return (object) [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
    }
    return null;
}
```

### 2. Flujo de actualización de token

Implementa un sistema de token de actualización para sesiones de larga duración:

```php
Flight::route('POST /api/login', function() {
    // ... validar credenciales ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // Token de acceso de corta duración (15 minutos)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // Token de actualización de larga duración (7 días)
    $refreshToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'refresh',
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60)
    ], $refreshSecret, 'HS256');
    
    Flight::json([
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'expires_in' => 900
    ]);
});

Flight::route('POST /api/refresh', function() {
    $refreshToken = Flight::request()->data->refresh_token ?? '';
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    try {
        $decoded = JWT::decode($refreshToken, new Key($refreshSecret, 'HS256'));
        
        // Verificar que sea un token de actualización
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Tipo de token inválido'], 401);
        }
        
        // Generar nuevo token de acceso
        $secretKey = Flight::get('config')['jwt_secret'];
        $accessToken = JWT::encode([
            'user_id' => $decoded->user_id,
            'type' => 'access',
            'iat' => time(),
            'exp' => time() + (15 * 60)
        ], $secretKey, 'HS256');
        
        Flight::json([
            'access_token' => $accessToken,
            'expires_in' => 900
        ]);
        
    } catch (Exception $e) {
        Flight::jsonHalt(['error' => 'Token de actualización inválido'], 401);
    }
});
```

### 3. Control de acceso basado en roles

Extiende tu middleware para verificar roles de usuario:

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // Asume que JwtMiddleware ya se ejecutó y estableció los datos de usuario
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Autenticación requerida'], 401);
        }
        
        // Verificar si el usuario tiene el rol requerido
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Permisos insuficientes'], 403);
        }
    }
}

// Uso: Ruta solo para admin
Flight::route('DELETE /api/users/@id', function($id) {
    // Lógica de eliminación de usuario
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. API pública con limitación de tasa por usuario

Usa JWT para rastrear y limitar la tasa de usuarios sin sesiones:

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // Asegúrate de configurar un servicio de caché en app/config/services.php
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 100 solicitudes por hora
            Flight::jsonHalt(['error' => 'Límite de tasa excedido'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## Mejores prácticas de seguridad

### 1. Usa claves secretas fuertes

```php
// Genera una clave secreta segura (ejecuta una vez, guárdala en archivo .env)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // ¡Almacena esto en tu archivo .env!
```

### 2. Almacena secretos en variables de entorno

```php
// ¡Nunca comprometas secretos en control de versiones!
// Usa un archivo .env y una librería como vlucas/phpdotenv

// Archivo .env:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// También puedes usar el archivo app/config/config.php para almacenar tus secretos
// solo asegúrate de que el archivo de config no se comprometa en control de versiones
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// En tu app:
$secretKey = getenv('JWT_SECRET');
```

### 3. Establece tiempos de expiración apropiados

```php
// Buena práctica: tokens de acceso de corta duración
'exp' => time() + (15 * 60)  // 15 minutos

// Para tokens de actualización: expiración más larga
'exp' => time() + (7 * 24 * 60 * 60)  // 7 días
```

### 4. Usa HTTPS en producción

Los JWT deben **siempre** transmitirse sobre HTTPS. ¡Nunca envíes tokens sobre HTTP plano en producción!

### 5. Valida las reclamaciones del token

Siempre valida las reclamaciones que te importan:

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// La verificación de expiración se maneja automáticamente por la librería
// Pero puedes agregar validaciones personalizadas:
if ($decoded->iat > time()) {
    throw new Exception('Token usado antes de ser emitido');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token aún no válido');
}
```

### 6. Considera lista negra de tokens para logout

Para seguridad extra, mantén una lista negra de tokens invalidados:

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // Extraer la expiración del token
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // Almacenar en caché/redis hasta la expiración
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Cierre de sesión exitoso']);
});

// Agregar a tu JwtMiddleware:
public function before(array $params) {
    // ... extraer JWT ...
    
    // Verificar lista negra
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'El token ha sido revocado'], 401);
    }
    
    // ... verificar token ...
}
```

## Algoritmos y tipos de claves

Firebase JWT soporta múltiples algoritmos:

### Algoritmos simétricos (HMAC)
- **HS256** (Recomendado para la mayoría de apps): Usa una sola clave secreta
- **HS384**, **HS512**: Variantes más fuertes

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Algoritmos asimétricos (RSA/ECDSA)
- **RS256**, **RS384**, **RS512**: Usa pares de claves pública/privada
- **ES256**, **ES384**, **ES512**: Variantes de curva elíptica

```php
// Generar claves: openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// Codificar con clave privada
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// Decodificar con clave pública
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **Cuándo usar RSA**: Usa RSA cuando necesites distribuir la clave pública para verificación (p.ej., microservicios, integraciones de terceros). Para una sola aplicación, HS256 es más simple y suficiente.

## Solución de problemas

### Error "Token expirado"
La reclamación `exp` de tu token está en el pasado. Emite un nuevo token o implementa actualización de token.

### "Falla en verificación de firma"
- Estás usando una clave secreta diferente para decodificar que la que usaste para codificar
- El token ha sido manipulado
- Desfase de reloj entre servidores (agrega un buffer de tolerancia)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // Permitir 60 segundos de desfase de reloj
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Token no se envía en solicitudes
Asegúrate de que tu cliente esté enviando el header `Authorization`:

```javascript
// Ejemplo en JavaScript
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## Métodos

La librería Firebase JWT proporciona estos métodos principales:

- `JWT::encode(array $payload, string $key, string $alg)`: Crea un JWT a partir de un payload
- `JWT::decode(string $jwt, Key $key)`: Decodifica y verifica un JWT
- `JWT::urlsafeB64Encode(string $input)`: Codificación Base64 URL-segura
- `JWT::urlsafeB64Decode(string $input)`: Decodificación Base64 URL-segura
- `JWT::$leeway`: Propiedad estática para establecer tolerancia de tiempo para validación (en segundos)

## ¿Por qué usar esta librería?

- **Estándar de la industria**: Firebase JWT es la librería JWT más popular y confiable para PHP
- **Mantenimiento activo**: Mantenida por el equipo de Google/Firebase
- **Enfocada en seguridad**: Actualizaciones regulares y parches de seguridad
- **API simple**: Fácil de entender e implementar
- **Bien documentada**: Documentación extensa y soporte de comunidad
- **Flexible**: Soporta múltiples algoritmos y opciones configurables

## Ver también

- [Repositorio de Github de Firebase JWT](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - Depura y decodifica JWTs
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - Especificación oficial de JWT
- [Documentación de middleware de Flight](/learn/middleware)
- [Plugin de sesión de Flight](/awesome-plugins/session) - Para autenticación basada en sesiones tradicionales

## Licencia

La librería Firebase JWT está licenciada bajo la Licencia BSD 3-Clause. Ve el [repositorio de Github](https://github.com/firebase/php-jwt) para detalles.