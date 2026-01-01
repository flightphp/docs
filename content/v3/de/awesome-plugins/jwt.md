# Firebase JWT - JSON Web Token Authentifizierung für Flight

JWT (JSON Web Tokens) sind eine kompakte, URL-sichere Methode, um Ansprüche zwischen Ihrer Anwendung und einem Client darzustellen. Sie eignen sich perfekt für stateless API-Authentifizierung – kein Bedarf an serverseitiger Sitzungsspeicherung! Diese Anleitung zeigt Ihnen, wie Sie [Firebase JWT](https://github.com/firebase/php-jwt) mit Flight für sichere, tokenbasierte Authentifizierung integrieren.

Besuchen Sie das [Github-Repository](https://github.com/firebase/php-jwt) für die vollständige Dokumentation und Details.

## Was ist JWT?

Ein JSON Web Token ist eine Zeichenkette, die aus drei Teilen besteht:
1. **Header**: Metadaten über das Token (Algorithmus, Typ)
2. **Payload**: Ihre Daten (Benutzer-ID, Rollen, Ablauf usw.)
3. **Signature**: Kryptografische Signatur zur Überprüfung der Authentizität

Beispiel-JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...` (sieht aus wie Kauderwelsch, ist aber strukturierte Daten!)

### Warum JWT verwenden?

- **Stateless**: Keine serverseitige Sitzungsspeicherung erforderlich – perfekt für Microservices und APIs
- **Skalierbar**: Funktioniert hervorragend mit Load Balancern, da keine Sitzungsaffinität erforderlich ist
- **Cross-Domain**: Kann über verschiedene Domains und Dienste hinweg verwendet werden
- **Mobile-freundlich**: Ideal für Mobile-Apps, wo Cookies möglicherweise nicht gut funktionieren
- **Standardisiert**: Branchenstandard-Ansatz (RFC 7519)

## Installation

Installieren Sie es über Composer:

```bash
composer require firebase/php-jwt
```

## Grundlegende Verwendung

Hier ist ein schnelles Beispiel für die Erstellung und Überprüfung eines JWT:

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Ihr geheimer Schlüssel (HALTEN SIE DAS SICHER!)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// Erstellen eines Tokens
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // Issued at
    'exp' => time() + 3600        // Läuft in 1 Stunde ab
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// Überprüfen und Dekodieren eines Tokens
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Ungültiges Token: " . $e->getMessage();
}
```

## JWT-Middleware für Flight (Empfohlener Ansatz)

Der gängigste und nützlichste Weg, JWT mit Flight zu verwenden, ist als **Middleware**, um Ihre API-Routen zu schützen. Hier ist ein vollständiges, produktionsreifes Beispiel:

### Schritt 1: Erstellen einer JWT-Middleware-Klasse

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
        // Speichern Sie Ihren geheimen Schlüssel in app/config/config.php, NICHT hartcodiert!
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Überprüfen, ob der Authorization-Header vorhanden ist
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'Kein Autorisierungstoken bereitgestellt'], 401);
        }

        // Token aus dem "Bearer <token>"-Format extrahieren
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Ungültiges Autorisierungsformat. Verwenden Sie: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // Das Token dekodieren und überprüfen
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // Benutzerdaten in der Anfrage für die Verwendung in Routen-Handlern speichern
            $this->app->request()->data->user = $decoded;
            
        } catch (ExpiredException $e) {
            $this->app->jsonHalt(['error' => 'Token ist abgelaufen'], 401);
        } catch (SignatureInvalidException $e) {
            $this->app->jsonHalt(['error' => 'Ungültige Token-Signatur'], 401);
        } catch (Exception $e) {
            $this->app->jsonHalt(['error' => 'Ungültiges Token: ' . $e->getMessage()], 401);
        }
    }
}
```

### Schritt 2: JWT-Geheimnis in Ihrer Konfiguration registrieren

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php oder index.php
// Stellen Sie sicher, dass Sie diese Zeile hinzufügen, wenn Sie die Konfiguration der App zugänglich machen möchten
$app->set('config', $config);
```

> **Sicherheitshinweis**: Kodieren Sie Ihren geheimen Schlüssel niemals hart! Verwenden Sie Umgebungsvariablen in der Produktion.

### Schritt 3: Ihre Routen mit Middleware schützen

```php
// Eine einzelne Route schützen
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // Von Middleware gesetzt
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware( JwtMiddleware::class);

// Eine gesamte Gruppe von Routen schützen (häufiger!)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // Alle Routen in dieser Gruppe sind geschützt!
```

Für weitere Details zu Middleware siehe die [Middleware-Dokumentation](/learn/middleware).

## Häufige Anwendungsfälle

### 1. Login-Endpunkt (Token-Generierung)

Erstellen Sie eine Route, die nach erfolgreicher Authentifizierung ein JWT generiert:

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // Anmeldeinformationen validieren (Beispiel – verwenden Sie Ihre eigene Logik!)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Ungültige Anmeldeinformationen'], 401);
    }

    // JWT generieren
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // 1 Stunde Ablauf
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // Ihre Datenbankabfrage und Passwortüberprüfung hier
    // Beispiel:
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

### 2. Token-Refresh-Flow

Implementieren Sie ein Refresh-Token-System für langfristige Sitzungen:

```php
Flight::route('POST /api/login', function() {
    // ... Anmeldeinformationen validieren ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // Kurzlebiges Access-Token (15 Minuten)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // Langlebiges Refresh-Token (7 Tage)
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
        
        // Überprüfen, ob es sich um ein Refresh-Token handelt
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Ungültiger Token-Typ'], 401);
        }
        
        // Neues Access-Token generieren
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
        Flight::jsonHalt(['error' => 'Ungültiges Refresh-Token'], 401);
    }
});
```

### 3. Rollengestützte Zugriffssteuerung

Erweitern Sie Ihre Middleware, um Benutzerrollen zu überprüfen:

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // Annahme: JwtMiddleware hat bereits ausgeführt und Benutzerdaten gesetzt
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Authentifizierung erforderlich'], 401);
        }
        
        // Überprüfen, ob der Benutzer die erforderliche Rolle hat
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Unzureichende Berechtigungen'], 403);
        }
    }
}

// Verwendung: Nur-Admin-Route
Flight::route('DELETE /api/users/@id', function($id) {
    // Benutzer löschen Logik
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. Öffentliche API mit Rate Limiting pro Benutzer

Verwenden Sie JWT, um Benutzer ohne Sitzungen zu verfolgen und zu rate-limitieren:

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // Stellen Sie sicher, dass Sie einen Cache-Dienst in app/config/services.php einrichten
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 100 Anfragen pro Stunde
            Flight::jsonHalt(['error' => 'Rate Limit überschritten'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## Best Practices für Sicherheit

### 1. Starke geheime Schlüssel verwenden

```php
// Einen sicheren geheimen Schlüssel generieren (einmal ausführen, in .env-Datei speichern)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // Speichern Sie das in Ihrer .env-Datei!
```

### 2. Geheimnisse in Umgebungsvariablen speichern

```php
// Geheimnisse niemals in die Versionskontrolle committen!
// Verwenden Sie eine .env-Datei und eine Bibliothek wie vlucas/phpdotenv

// .env-Datei:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// Sie können auch die app/config/config.php-Datei verwenden, um Ihre Geheimnisse zu speichern
// Stellen Sie nur sicher, dass die Konfigurationsdatei nicht in die Versionskontrolle committet wird
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// In Ihrer App:
$secretKey = getenv('JWT_SECRET');
```

### 3. Geeignete Ablaufzeiten festlegen

```php
// Gute Praxis: Kurzlebige Access-Tokens
'exp' => time() + (15 * 60)  // 15 Minuten

// Für Refresh-Tokens: Längere Ablaufzeit
'exp' => time() + (7 * 24 * 60 * 60)  // 7 Tage
```

### 4. HTTPS in der Produktion verwenden

JWTs sollten **immer** über HTTPS übertragen werden. Senden Sie Tokens niemals über einfaches HTTP in der Produktion!

### 5. Token-Ansprüche validieren

Validieren Sie immer die Ansprüche, die Sie interessieren:

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// Überprüfung des Ablaufs wird automatisch von der Bibliothek gehandhabt
// Aber Sie können benutzerdefinierte Validierungen hinzufügen:
if ($decoded->iat > time()) {
    throw new Exception('Token verwendet, bevor es ausgestellt wurde');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token noch nicht gültig');
}
```

### 6. Token-Blacklisting für Logout in Betracht ziehen

Für zusätzliche Sicherheit eine Blacklist ungültiger Tokens pflegen:

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // Den Ablauf des Tokens extrahieren
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // In Cache/Redis bis zum Ablauf speichern
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Erfolgreich abgemeldet']);
});

// Zu Ihrer JwtMiddleware hinzufügen:
public function before(array $params) {
    // ... JWT extrahieren ...
    
    // Blacklist überprüfen
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Token wurde widerrufen'], 401);
    }
    
    // ... Token überprüfen ...
}
```

## Algorithmen und Schlüsseltypen

Firebase JWT unterstützt mehrere Algorithmen:

### Symmetrische Algorithmen (HMAC)
- **HS256** (Empfohlen für die meisten Apps): Verwendet einen einzelnen geheimen Schlüssel
- **HS384**, **HS512**: Stärkere Varianten

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Asymmetrische Algorithmen (RSA/ECDSA)
- **RS256**, **RS384**, **RS512**: Verwendet öffentliche/privaten Schlüsselpaare
- **ES256**, **ES384**, **ES512**: Elliptische-Kurven-Varianten

```php
// Schlüssel generieren: openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// Mit privatem Schlüssel kodieren
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// Mit öffentlichem Schlüssel dekodieren
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **Wann RSA verwenden**: Verwenden Sie RSA, wenn Sie den öffentlichen Schlüssel für die Überprüfung verteilen müssen (z. B. Microservices, Drittanbieter-Integrationen). Für eine einzelne Anwendung ist HS256 einfacher und ausreichend.

## Fehlerbehebung

### "Abgelaufenes Token"-Fehler
Der `exp`-Anspruch Ihres Tokens liegt in der Vergangenheit. Stellen Sie ein neues Token aus oder implementieren Sie Token-Refresh.

### "Signaturüberprüfung fehlgeschlagen"
- Sie verwenden einen anderen geheimen Schlüssel zum Dekodieren als zum Kodieren
- Das Token wurde manipuliert
- Uhrzeitskew zwischen Servern (fügen Sie einen Leeway-Puffer hinzu)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // 60 Sekunden Uhrzeitskew erlauben
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Token wird nicht in Anfragen gesendet
Stellen Sie sicher, dass Ihr Client den `Authorization`-Header sendet:

```javascript
// JavaScript-Beispiel
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## Methoden

Die Firebase JWT-Bibliothek stellt diese Kernmethoden bereit:

- `JWT::encode(array $payload, string $key, string $alg)`: Erstellt ein JWT aus einem Payload
- `JWT::decode(string $jwt, Key $key)`: Dekodiert und überprüft ein JWT
- `JWT::urlsafeB64Encode(string $input)`: Base64 URL-sichere Kodierung
- `JWT::urlsafeB64Decode(string $input)`: Base64 URL-sichere Dekodierung
- `JWT::$leeway`: Statische Eigenschaft zum Festlegen der Zeit-Leeway für Validierung (in Sekunden)

## Warum diese Bibliothek verwenden?

- **Branchenstandard**: Firebase JWT ist die beliebteste und am weitesten vertrauenswürdige JWT-Bibliothek für PHP
- **Aktive Wartung**: Wird vom Google/Firebase-Team gepflegt
- **Sicherheitsfokussiert**: Regelmäßige Updates und Sicherheits-Patches
- **Einfache API**: Leicht zu verstehen und zu implementieren
- **Gut dokumentiert**: Umfangreiche Dokumentation und Community-Support
- **Flexibel**: Unterstützt mehrere Algorithmen und konfigurierbare Optionen

## Siehe auch

- [Firebase JWT Github-Repository](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - JWTs debuggen und dekodieren
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - Offizielle JWT-Spezifikation
- [Flight Middleware-Dokumentation](/learn/middleware)
- [Flight Session-Plugin](/awesome-plugins/session) - Für traditionelle sitzungsbasierte Authentifizierung

## Lizenz

Die Firebase JWT-Bibliothek ist unter der BSD 3-Clause License lizenziert. Siehe das [Github-Repository](https://github.com/firebase/php-jwt) für Details.