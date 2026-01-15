# Firebase JWT - Authentification par JSON Web Token

JWT (JSON Web Tokens) sont une méthode compacte et sécurisée pour les URL permettant de représenter des revendications entre votre application et un client. Ils sont parfaits pour l'authentification API sans état — pas besoin de stockage de sessions côté serveur ! Ce guide vous montre comment intégrer [Firebase JWT](https://github.com/firebase/php-jwt) avec Flight pour une authentification sécurisée basée sur des tokens.

Visitez le [dépôt Github](https://github.com/firebase/php-jwt) pour la documentation complète et les détails.

## Qu'est-ce que JWT ?

Un JSON Web Token est une chaîne qui contient trois parties :
1. **En-tête** : Métadonnées sur le token (algorithme, type)
2. **Charge utile** : Vos données (ID utilisateur, rôles, expiration, etc.)
3. **Signature** : Signature cryptographique pour vérifier l'authenticité

Exemple de JWT : `eyJ0eXAiOiJKV1QiLCJhbGc...` (ça ressemble à du charabia, mais c'est des données structurées !)

### Pourquoi utiliser JWT ?

- **Sans état** : Pas besoin de stockage de sessions côté serveur — parfait pour les microservices et les API
- **Évolutif** : Fonctionne bien avec les équilibreurs de charge car il n'y a pas de besoin d'affinité de session
- **Multi-domaines** : Peut être utilisé à travers différents domaines et services
- **Adapté au mobile** : Idéal pour les applications mobiles où les cookies ne fonctionnent pas bien
- **Standardisé** : Approche standard de l'industrie (RFC 7519)

## Installation

Installez via Composer :

```bash
composer require firebase/php-jwt
```

## Utilisation de base

Voici un exemple rapide de création et de vérification d'un JWT :

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Votre clé secrète (GARDEZ-LA SÉCURISÉE !)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// Créer un token
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // Émis à
    'exp' => time() + 3600        // Expire dans 1 heure
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// Vérifier et décoder un token
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Invalid token: " . $e->getMessage();
}
```

## Middleware JWT pour Flight (Approche recommandée)

La manière la plus courante et utile d'utiliser JWT avec Flight est en tant que **middleware** pour protéger vos routes API. Voici un exemple complet, prêt pour la production :

### Étape 1 : Créer une classe Middleware JWT

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
        // Stockez votre clé secrète dans app/config/config.php, PAS en dur !
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Vérifier si l'en-tête Authorization existe
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'No authorization token provided'], 401);
        }

        // Extraire le token du format "Bearer <token>"
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Invalid authorization format. Use: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // Décoder et vérifier le token
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // Stocker les données utilisateur dans la requête pour utilisation dans les gestionnaires de routes
            $this->app->request()->data->user = $decoded;
            
        } catch (ExpiredException $e) {
            $this->app->jsonHalt(['error' => 'Token has expired'], 401);
        } catch (SignatureInvalidException $e) {
            $this->app->jsonHalt(['error' => 'Invalid token signature'], 401);
        } catch (Exception $e) {
            $this->app->jsonHalt(['error' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }
}
```

### Étape 2 : Enregistrer la clé secrète JWT dans votre configuration

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php ou index.php
// assurez-vous d'ajouter cette ligne si vous voulez exposer la config à l'app
$app->set('config', $config);
```

> **Note de sécurité** : Ne mettez jamais votre clé secrète en dur ! Utilisez des variables d'environnement en production.

### Étape 3 : Protéger vos routes avec le middleware

```php
// Protéger une seule route
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // Défini par le middleware
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware(JwtMiddleware::class);

// Protéger un groupe entier de routes (plus courant !)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // Toutes les routes de ce groupe sont protégées !
```

Pour plus de détails sur le middleware, consultez la [documentation du middleware](/learn/middleware).

## Cas d'utilisation courants

### 1. Endpoint de connexion (Génération de token)

Créez une route qui génère un JWT après une authentification réussie :

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // Valider les identifiants (exemple - utilisez votre propre logique !)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Invalid credentials'], 401);
    }

    // Générer JWT
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // Expiration dans 1 heure
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // Votre recherche en base de données et vérification de mot de passe ici
    // Exemple :
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

### 2. Flux de rafraîchissement de token

Implémentez un système de token de rafraîchissement pour les sessions longues :

```php
Flight::route('POST /api/login', function() {
    // ... valider les identifiants ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // Token d'accès à vie courte (15 minutes)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // Token de rafraîchissement à vie longue (7 jours)
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
        
        // Vérifier que c'est un token de rafraîchissement
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Invalid token type'], 401);
        }
        
        // Générer un nouveau token d'accès
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
        Flight::jsonHalt(['error' => 'Invalid refresh token'], 401);
    }
});
```

### 3. Contrôle d'accès basé sur les rôles

Étendez votre middleware pour vérifier les rôles des utilisateurs :

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // Suppose que JwtMiddleware a déjà exécuté et défini les données utilisateur
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Authentication required'], 401);
        }
        
        // Vérifier si l'utilisateur a le rôle requis
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Insufficient permissions'], 403);
        }
    }
}

// Utilisation : Route admin uniquement
Flight::route('DELETE /api/users/@id', function($id) {
    // Logique de suppression d'utilisateur
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. API publique avec limitation de taux par utilisateur

Utilisez JWT pour suivre et limiter les taux des utilisateurs sans sessions :

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // Assurez-vous d'avoir configuré un service de cache dans app/config/services.php
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 100 requêtes par heure
            Flight::jsonHalt(['error' => 'Rate limit exceeded'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## Meilleures pratiques de sécurité

### 1. Utiliser des clés secrètes fortes

```php
// Générer une clé secrète sécurisée (exécutez une fois, sauvegardez dans le fichier .env)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // Stockez ceci dans votre fichier .env !
```

### 2. Stocker les secrets dans les variables d'environnement

```php
// Ne commitez jamais les secrets dans le contrôle de version !
// Utilisez un fichier .env et une bibliothèque comme vlucas/phpdotenv

// Fichier .env :
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// Vous pouvez aussi utiliser le fichier app/config/config.php pour stocker vos secrets
// assurez-vous simplement que le fichier de config n'est pas commité dans le contrôle de version
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// Dans votre app :
$secretKey = getenv('JWT_SECRET');
```

### 3. Définir des temps d'expiration appropriés

```php
// Bonne pratique : tokens d'accès à vie courte
'exp' => time() + (15 * 60)  // 15 minutes

// Pour les tokens de rafraîchissement : expiration plus longue
'exp' => time() + (7 * 24 * 60 * 60)  // 7 jours
```

### 4. Utiliser HTTPS en production

Les JWT doivent **toujours** être transmis via HTTPS. N'envoyez jamais de tokens via HTTP pur en production !

### 5. Valider les revendications des tokens

Validez toujours les revendications qui vous importent :

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// La vérification de l'expiration est gérée automatiquement par la bibliothèque
// Mais vous pouvez ajouter des validations personnalisées :
if ($decoded->iat > time()) {
    throw new Exception('Token used before it was issued');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token not yet valid');
}
```

### 6. Considérer la liste noire des tokens pour la déconnexion

Pour une sécurité supplémentaire, maintenez une liste noire des tokens invalidés :

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // Extraire l'expiration du token
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // Stocker dans le cache/redis jusqu'à expiration
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Successfully logged out']);
});

// Ajoutez à votre JwtMiddleware :
public function before(array $params) {
    // ... extraire JWT ...
    
    // Vérifier la liste noire
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Token has been revoked'], 401);
    }
    
    // ... vérifier le token ...
}
```

## Algorithmes et types de clés

Firebase JWT supporte plusieurs algorithmes :

### Algorithmes symétriques (HMAC)
- **HS256** (Recommandé pour la plupart des apps) : Utilise une seule clé secrète
- **HS384**, **HS512** : Variantes plus fortes

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Algorithmes asymétriques (RSA/ECDSA)
- **RS256**, **RS384**, **RS512** : Utilise des paires de clés publique/privée
- **ES256**, **ES384**, **ES512** : Variantes courbe elliptique

```php
// Générer des clés : openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// Encoder avec la clé privée
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// Décoder avec la clé publique
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **Quand utiliser RSA** : Utilisez RSA quand vous devez distribuer la clé publique pour la vérification (ex. : microservices, intégrations tierces). Pour une seule application, HS256 est plus simple et suffisant.

## Dépannage

### Erreur "Token expiré"
La revendication `exp` de votre token est dans le passé. Émettez un nouveau token ou implémentez le rafraîchissement de token.

### "Échec de vérification de signature"
- Vous utilisez une clé secrète différente pour décoder que pour encoder
- Le token a été modifié
- Décalage d'horloge entre serveurs (ajoutez un tampon de tolérance)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // Autoriser 60 secondes de décalage d'horloge
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Token non envoyé dans les requêtes
Assurez-vous que votre client envoie l'en-tête `Authorization` :

```javascript
// Exemple JavaScript
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## Méthodes

La bibliothèque Firebase JWT fournit ces méthodes principales :

- `JWT::encode(array $payload, string $key, string $alg)` : Crée un JWT à partir d'une charge utile
- `JWT::decode(string $jwt, Key $key)` : Décode et vérifie un JWT
- `JWT::urlsafeB64Encode(string $input)` : Encodage Base64 URL-safe
- `JWT::urlsafeB64Decode(string $input)` : Décodage Base64 URL-safe
- `JWT::$leeway` : Propriété statique pour définir la tolérance temporelle pour la validation (en secondes)

## Pourquoi utiliser cette bibliothèque ?

- **Standard de l'industrie** : Firebase JWT est la bibliothèque JWT la plus populaire et la plus fiable pour PHP
- **Maintenance active** : Maintenue par l'équipe Google/Firebase
- **Axée sur la sécurité** : Mises à jour régulières et correctifs de sécurité
- **API simple** : Facile à comprendre et à implémenter
- **Bien documentée** : Documentation étendue et support communautaire
- **Flexible** : Supporte plusieurs algorithmes et options configurables

## Voir aussi

- [Dépôt Github Firebase JWT](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - Déboguer et décoder les JWT
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - Spécification officielle JWT
- [Documentation du middleware Flight](/learn/middleware)
- [Plugin Session Flight](/awesome-plugins/session) - Pour l'authentification basée sur sessions traditionnelles

## Licence

La bibliothèque Firebase JWT est sous licence BSD 3-Clause. Voir le [dépôt Github](https://github.com/firebase/php-jwt) pour les détails.