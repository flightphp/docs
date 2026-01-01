# Firebase JWT - JSON Web Token autentifikācija Flight

JWT (JSON Web Tokens) ir kompakts, URL-drošs veids, kā pārstāvēt pretenzijas starp jūsu lietojumprogrammu un klientu. Tie ir ideāli piemēroti bezstāvokļa API autentifikācijai — nav vajadzīga servera puses sesiju uzglabāšana! Šis ceļvedis parāda, kā integrēt [Firebase JWT](https://github.com/firebase/php-jwt) ar Flight drošai, tokenu balstītai autentifikācijai.

Apmeklējiet [Github krātuvi](https://github.com/firebase/php-jwt), lai iegūtu pilnu dokumentāciju un detaļas.

## Kas ir JWT?

JSON Web Token ir virkne, kas satur trīs daļas:
1. **Virsnieks**: Metadati par tokenu (algoritms, tips)
2. **Ladējums**: Jūsu dati (lietotāja ID, lomas, termiņš utt.)
3. **Paraksts**: Kriptogrāfiskais paraksts autentiskuma pārbaudei

Piemērs JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...` (izskatās pēc muļķībām, bet tas ir strukturēti dati!)

### Kāpēc izmantot JWT?

- **Bezstāvoklis**: Nav vajadzīga servera puses sesiju uzglabāšana — ideāli piemērots mikroservisiem un API
- **Mērogojams**: Labi darbojas ar slodzes līdzsvarotājjiem, jo nav sesiju saistības prasības
- **Pārrobežu**: Var izmantot dažādās domēnos un servisos
- **Mobilajām ierīcēm draudzīgs**: Lieliski piemērots mobilajām lietojumprogrammām, kur sīkfaili var nedarboties labi
- **Standartizēts**: Nozares standarta pieeja (RFC 7519)

## Instalēšana

Instalējiet, izmantojot Composer:

```bash
composer require firebase/php-jwt
```

## Pamata izmantošana

Šeit ir ātrs piemērs tokena izveidei un pārbaudei:

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Jūsu slepenā atslēga (TURĒT TO DROŠU!)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// Izveidojiet tokenu
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // Izsniegts
    'exp' => time() + 3600        // Beidzas pēc 1 stundas
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// Pārbaudiet un atšifrējiet tokenu
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Nederīgs tokens: " . $e->getMessage();
}
```

## JWT starpprogrammatūra Flight (Ieteicamais pieeja)

Visizplatītākais un noderīgākais veids, kā izmantot JWT ar Flight, ir kā **starpprogrammatūru**, lai aizsargātu jūsu API maršrutus. Šeit ir pilnīgs, ražošanai gatavs piemērs:

### 1. solis: Izveidojiet JWT starpprogrammatūras klasi

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
        // Uzglabājiet savu slepeno atslēgu app/config/config.php, nevis cietkodus!
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Pārbaudiet, vai pastāv autorizācijas virsraksts
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'Nav nodrošināts autorizācijas tokens'], 401);
        }

        // Izvilciet tokenu no "Bearer <token>" formāta
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Nederīgs autorizācijas formāts. Izmantojiet: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // Atšifrējiet un pārbaudiet tokenu
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // Uzglabājiet lietotāja datus pieprasījumā, lai izmantotu maršruta apstrādātājos
            $this->app->request()->data->user = $decoded;
            
        } catch (ExpiredException $e) {
            $this->app->jsonHalt(['error' => 'Tokens ir beidzies'], 401);
        } catch (SignatureInvalidException $e) {
            $this->app->jsonHalt(['error' => 'Nederīgs tokena paraksts'], 401);
        } catch (Exception $e) {
            $this->app->jsonHalt(['error' => 'Nederīgs tokens: ' . $e->getMessage()], 401);
        }
    }
}
```

### 2. solis: Reģistrējiet JWT slepeno atslēgu savā konfigurācijā

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php vai index.php
// pārliecinieties, ka pievienojat šo rindu, ja vēlaties pakļaut konfigurāciju lietojumprogrammai
$app->set('config', $config);
```

> **Drošības piezīme**: Nekad necietkodējiet savu slepeno atslēgu! Izmantojiet vides mainīgos ražošanā.

### 3. solis: Aizsargājiet savus maršrutus ar starpprogrammatūru

```php
// Aizsargājiet vienu maršrutu
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // Iestatīts ar starpprogrammatūru
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware( JwtMiddleware::class);

// Aizsargājiet veselu maršrutu grupu (biežāk!)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // Visi maršruti šajā grupā ir aizsargāti!
```

Lai iegūtu vairāk detaļu par starpprogrammatūru, skatiet [starpprogrammatūras dokumentāciju](/learn/middleware).

## Izplatīti izmantošanas gadījumi

### 1. Pieteikšanās galapunkts (Tokena ģenerēšana)

Izveidojiet maršrutu, kas ģenerē JWT pēc veiksmīgas autentifikācijas:

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // Validējiet akreditācijas datus (piemērs — izmantojiet savu loģiku!)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Nederīgas akreditācijas'], 401);
    }

    // Ģenerējiet JWT
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // 1 stundas termiņš
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // Jūsu datubāzes meklēšana un paroles pārbaude šeit
    // Piemērs:
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

### 2. Tokena atjaunošanas plūsma

Īstenojiet atjaunošanas tokena sistēmu ilgstošām sesijām:

```php
Flight::route('POST /api/login', function() {
    // ... validējiet akreditācijas datus ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // Īstermiņa piekļuves tokens (15 minūtes)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // Ilgtermiņa atjaunošanas tokens (7 dienas)
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
        
        // Pārbaudiet, vai tas ir atjaunošanas tokens
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Nederīgs tokena tips'], 401);
        }
        
        // Ģenerējiet jaunu piekļuves tokenu
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
        Flight::jsonHalt(['error' => 'Nederīgs atjaunošanas tokens'], 401);
    }
});
```

### 3. Lomu balstīta piekļuves kontrole

Paplašiniet savu starpprogrammatūru, lai pārbaudītu lietotāja lomas:

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // Pieņemiet, ka JwtMiddleware jau darbojās un iestatīja lietotāja datus
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Nepieciešama autentifikācija'], 401);
        }
        
        // Pārbaudiet, vai lietotājam ir nepieciešamā loma
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Nepietiekamas atļaujas'], 403);
        }
    }
}

// Lietojums: Tikai administratora maršruts
Flight::route('DELETE /api/users/@id', function($id) {
    // Dzēst lietotāja loģiku
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. Publiska API ar ātruma ierobežošanu pēc lietotāja

Izmantojiet JWT, lai izsekotu un ierobežotu ātrumu lietotājiem bez sesijām:

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // Pārliecinieties, ka iestatāt kešatmiņas servisu app/config/services.php
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 100 pieprasījumi stundā
            Flight::jsonHalt(['error' => 'Ātruma ierobežojums pārsniegts'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## Drošības labākās prakses

### 1. Izmantojiet spēcīgas slepenās atslēgas

```php
// Ģenerējiet drošu slepeno atslēgu (palaižiet vienreiz, saglabājiet .env failā)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // Uzglabājiet to savā .env failā!
```

### 2. Uzglabājiet noslēpumus vides mainīgajos

```php
// Nekad neapņemiet noslēpumus versiju kontrolē!
// Izmantojiet .env failu un bibliotēku, piemēram, vlucas/phpdotenv

// .env fails:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// Varat arī izmantot app/config/config.php failu, lai uzglabātu savus noslēpumus
// tikai pārliecinieties, ka konfigurācijas fails nav apņemts versiju kontrolē
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// Savā lietojumprogrammā:
$secretKey = getenv('JWT_SECRET');
```

### 3. Iestatiet piemērotus termiņu laikus

```php
// Labā prakse: īstermiņa piekļuves tokeni
'exp' => time() + (15 * 60)  // 15 minūtes

// Atjaunošanas tokeniem: ilgāks termiņš
'exp' => time() + (7 * 24 * 60 * 60)  // 7 dienas
```

### 4. Izmantojiet HTTPS ražošanā

JWT vienmēr jāpārsūta pār HTTPS. Nekad nesūtiet tokenus pār vienkāršu HTTP ražošanā!

### 5. Validējiet tokena pretenzijas

Vienmēr validējiet pretenzijas, kas jums rūp:

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// Pārbaudiet termiņu, ko bibliotēka automātiski apstrādā
// Bet varat pievienot pielāgotas validācijas:
if ($decoded->iat > time()) {
    throw new Exception('Tokens izmantots pirms izsniegšanas');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Tokens vēl nav derīgs');
}
```

### 6. Apsveriet tokena melno sarakstu izrakstīšanai

Papildu drošībai uzturiet nederīgu tokenu melno sarakstu:

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // Izvilciet tokena termiņu
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // Uzglabājiet kešatmiņā/redis līdz termiņam
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Veiksmīgi izrakstījies']);
});

// Pievienojiet savai JwtMiddleware:
public function before(array $params) {
    // ... izvilciet JWT ...
    
    // Pārbaudiet melno sarakstu
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Tokens ir atcelts'], 401);
    }
    
    // ... pārbaudiet tokenu ...
}
```

## Algoritmi un atslēgu tipi

Firebase JWT atbalsta vairākus algoritmus:

### Simetriskie algoritmi (HMAC)
- **HS256** (Ieteicams lielākajai daļai lietojumprogrammu): Izmanto vienu slepeno atslēgu
- **HS384**, **HS512**: Spēcīgākas variācijas

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Asimetriskie algoritmi (RSA/ECDSA)
- **RS256**, **RS384**, **RS512**: Izmanto publisko/privāto atslēgu pārus
- **ES256**, **ES384**, **ES512**: Elipses līknes variācijas

```php
// Ģenerējiet atslēgas: openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// Kodējiet ar privāto atslēgu
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// Atšifrējiet ar publisko atslēgu
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **Kad izmantot RSA**: Izmantojiet RSA, kad nepieciešams izplatīt publisko atslēgu pārbaudei (piem., mikroservisi, trešo pušu integrācijas). Vienai lietojumprogrammai HS256 ir vienkāršāks un pietiekams.

## Traucējummeklēšana

### "Beidzies tokens" kļūda
Jūsu tokena `exp` pretenzija ir pagātnē. Izsniedziet jaunu tokenu vai īstenojiet tokena atjaunošanu.

### "Paraksta pārbaude neizdevās"
- Jūs izmantojat citu slepeno atslēgu atšifrēšanai nekā kodēšanai
- Tokens ir modificēts
- Laika nobīde starp serveriem (pievienojiet atlaidzes buferi)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // Ļauj 60 sekunžu laika nobīdi
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Tokens netiek nosūtīts pieprasījumos
Pārliecinieties, ka jūsu klients nosūta `Authorization` virsrakstu:

```javascript
// JavaScript piemērs
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## Metodes

Firebase JWT bibliotēka nodrošina šīs pamata metodes:

- `JWT::encode(array $payload, string $key, string $alg)`: Izveido JWT no ladējuma
- `JWT::decode(string $jwt, Key $key)`: Atšifrē un pārbauda JWT
- `JWT::urlsafeB64Encode(string $input)`: Base64 URL-droša kodēšana
- `JWT::urlsafeB64Decode(string $input)`: Base64 URL-droša atšifrēšana
- `JWT::$leeway`: Statiska īpašība, lai iestatītu laika atlaidzi validācijai (sekundēs)

## Kāpēc izmantot šo bibliotēku?

- **Nozares standarts**: Firebase JWT ir populārākā un plaši uzticamā JWT bibliotēka PHP
- **Aktīva uzturēšana**: Uztur Google/Firebase komanda
- **Uz drošību orientēta**: Regulāras atjauninājumi un drošības labojumi
- **Vienkārša API**: Viegli saprotama un īstenojama
- **Labu dokumentēšana**: Plaša dokumentācija un kopienas atbalsts
- **Elastīga**: Atbalsta vairākus algoritmus un konfigurējamās opcijas

## Skatīt arī

- [Firebase JWT Github krātuve](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - Kļūdu labošana un JWT atšifrēšana
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - Oficiālā JWT specifikācija
- [Flight starpprogrammatūras dokumentācija](/learn/middleware)
- [Flight sesijas spraudnis](/awesome-plugins/session) - Tradicionālai sesiju balstītai autentifikācijai

## Licence

Firebase JWT bibliotēka ir licencēta saskaņā ar BSD 3-Klauzulas licenci. Skatiet [Github krātuvi](https://github.com/firebase/php-jwt) detaļām.