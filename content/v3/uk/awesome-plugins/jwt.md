# Firebase JWT - Аутентифікація JSON Web Token для Flight

JWT (JSON Web Tokens) — це компактний, безпечний для URL спосіб представлення тверджень між вашим додатком і клієнтом. Вони ідеальні для безстанційної аутентифікації API — немає потреби в зберіганні сесій на сервері! Цей посібник показує, як інтегрувати [Firebase JWT](https://github.com/firebase/php-jwt) з Flight для безпечної аутентифікації на основі токенів.

Відвідайте [репозиторій на Github](https://github.com/firebase/php-jwt) для повної документації та деталей.

## Що таке JWT?

JSON Web Token — це рядок, що містить три частини:
1. **Заголовок**: Метадані про токен (алгоритм, тип)
2. **Навантаження**: Ваші дані (ID користувача, ролі, термін дії тощо)
3. **Підпис**: Криптографічний підпис для перевірки автентичності

Приклад JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...` (виглядає як нісенітниця, але це структуровані дані!)

### Чому використовувати JWT?

- **Безстанційний**: Не потрібно зберігати сесії на сервері — ідеально для мікросервісів і API
- **Масштабованість**: Добре працює з балансувальниками навантаження, оскільки немає вимоги до афінності сесій
- **Крос-доменний**: Можна використовувати між різними доменами та сервісами
- **Дружній до мобільних**: Чудово для мобільних додатків, де куки можуть не працювати добре
- **Стандартизований**: Стандарт галузі (RFC 7519)

## Встановлення

Встановіть через Composer:

```bash
composer require firebase/php-jwt
```

## Основне використання

Ось швидкий приклад створення та перевірки JWT:

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Ваш секретний ключ (ЗБЕРІГАЙТЕ ЦЕ БЕЗПЕЧНО!)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// Створення токена
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // Виданий о
    'exp' => time() + 3600        // Термін дії 1 година
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// Перевірка та декодування токена
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Invalid token: " . $e->getMessage();
}
```

## Middleware JWT для Flight (Рекомендований підхід)

Найпоширеніший і корисний спосіб використання JWT з Flight — як **middleware** для захисту маршрутів API. Ось повний, готовий до виробництва приклад:

### Крок 1: Створення класу JWT Middleware

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
        // Зберігайте свій секретний ключ у app/config/config.php, НЕ жорстко закодований!
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Перевірка існування заголовка Authorization
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'No authorization token provided'], 401);
        }

        // Витягнення токена з формату "Bearer <token>"
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Invalid authorization format. Use: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // Декодування та перевірка токена
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // Збереження даних користувача в запиті для використання в обробниках маршрутів
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

### Крок 2: Реєстрація JWT Secret у вашій конфігурації

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php або index.php
// переконайтеся, що додали цей рядок, якщо хочете надати доступ до конфігурації додатку
$app->set('config', $config);
```

> **Примітка щодо безпеки**: Ніколи не жорстко кодуйте свій секретний ключ! Використовуйте змінні середовища в продакшені.

### Крок 3: Захист ваших маршрутів за допомогою Middleware

```php
// Захист одного маршруту
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // Встановлено middleware
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware( JwtMiddleware::class);

// Захист цілої групи маршрутів (поширеніше!)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // Усі маршрути в цій групі захищені!
```

Для отримання детальнішої інформації про middleware дивіться [документацію middleware](/learn/middleware).

## Поширені випадки використання

### 1. Endpoint входу (Генерація токена)

Створіть маршрут, який генерує JWT після успішної аутентифікації:

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // Перевірка облікових даних (приклад — використовуйте свою логіку!)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Invalid credentials'], 401);
    }

    // Генерація JWT
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // Термін дії 1 година
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // Ваш пошук у базі даних та перевірка пароля тут
    // Приклад:
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

### 2. Потік оновлення токена

Реалізуйте систему токенів оновлення для довготривалих сесій:

```php
Flight::route('POST /api/login', function() {
    // ... перевірка облікових даних ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // Короткочасний токен доступу (15 хвилин)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // Довготривалий токен оновлення (7 днів)
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
        
        // Перевірка, що це токен оновлення
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Invalid token type'], 401);
        }
        
        // Генерація нового токена доступу
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

### 3. Контроль доступу на основі ролей

Розширте свій middleware для перевірки ролей користувача:

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // Припустимо, що JwtMiddleware вже запустився та встановив дані користувача
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Authentication required'], 401);
        }
        
        // Перевірка, чи має користувач необхідну роль
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Insufficient permissions'], 403);
        }
    }
}

// Використання: Маршрут тільки для адміністраторів
Flight::route('DELETE /api/users/@id', function($id) {
    // Логіка видалення користувача
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. Публічний API з обмеженням швидкості за користувачем

Використовуйте JWT для відстеження та обмеження швидкості користувачів без сесій:

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // Переконайтеся, що налаштували сервіс кешу в app/config/services.php
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 100 запитів на годину
            Flight::jsonHalt(['error' => 'Rate limit exceeded'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## Найкращі практики безпеки

### 1. Використовуйте сильні секретні ключі

```php
// Генерація безпечного секретного ключа (запустіть один раз, збережіть у .env файл)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // Збережіть це у вашому .env файлі!
```

### 2. Зберігайте секрети в змінних середовища

```php
// Ніколи не комітьте секрети до контролю версій!
// Використовуйте .env файл та бібліотеку, як vlucas/phpdotenv

// .env файл:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// Ви також можете використовувати файл app/config/config.php для зберігання секретів
// просто переконайтеся, що файл конфігурації не комітиться до контролю версій
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// У вашому додатку:
$secretKey = getenv('JWT_SECRET');
```

### 3. Встановіть відповідні терміни дії

```php
// Хороша практика: короткочасні токени доступу
'exp' => time() + (15 * 60)  // 15 хвилин

// Для токенів оновлення: довший термін дії
'exp' => time() + (7 * 24 * 60 * 60)  // 7 днів
```

### 4. Використовуйте HTTPS у продакшені

JWT **завжди** повинні передаватися через HTTPS. Ніколи не надсилайте токени через звичайний HTTP у продакшені!

### 5. Перевіряйте твердження токена

Завжди перевіряйте твердження, які вас цікавлять:

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// Перевірка терміну дії обробляється автоматично бібліотекою
// Але ви можете додати власні перевірки:
if ($decoded->iat > time()) {
    throw new Exception('Token used before it was issued');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token not yet valid');
}
```

### 6. Розгляньте чорний список токенів для виходу

Для додаткової безпеки підтримуйте чорний список анульованих токенів:

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // Витягнення терміну дії токена
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // Збереження в кеші/redis до терміну дії
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Successfully logged out']);
});

// Додайте до вашого JwtMiddleware:
public function before(array $params) {
    // ... витягнення JWT ...
    
    // Перевірка чорного списку
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Token has been revoked'], 401);
    }
    
    // ... перевірка токена ...
}
```

## Алгоритми та типи ключів

Firebase JWT підтримує кілька алгоритмів:

### Симетричні алгоритми (HMAC)
- **HS256** (Рекомендовано для більшості додатків): Використовує один секретний ключ
- **HS384**, **HS512**: Сильніші варіанти

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Асиметричні алгоритми (RSA/ECDSA)
- **RS256**, **RS384**, **RS512**: Використовує пари публічний/приватний ключ
- **ES256**, **ES384**, **ES512**: Варіанти на еліптичних кривих

```php
// Генерація ключів: openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// Кодування з приватним ключем
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// Декодування з публічним ключем
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **Коли використовувати RSA**: Використовуйте RSA, коли потрібно розповсюджувати публічний ключ для перевірки (наприклад, мікросервіси, інтеграції з третіми сторонами). Для одного додатка HS256 простіший і достатній.

## Вирішення проблем

### Помилка "Expired token"
Твердження `exp` вашого токена в минулому. Видайте новий токен або реалізуйте оновлення токена.

### "Signature verification failed"
- Ви використовуєте інший секретний ключ для декодування, ніж для кодування
- Токен був змінений
- Розбіжність годинників між серверами (додайте буфер leeway)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // Дозволити 60 секунд розбіжності годинників
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Токен не надсилається в запитах
Переконайтеся, що ваш клієнт надсилає заголовок `Authorization`:

```javascript
// Приклад JavaScript
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## Методи

Бібліотека Firebase JWT надає ці основні методи:

- `JWT::encode(array $payload, string $key, string $alg)`: Створює JWT з навантаження
- `JWT::decode(string $jwt, Key $key)`: Декодує та перевіряє JWT
- `JWT::urlsafeB64Encode(string $input)`: Кодування Base64 безпечне для URL
- `JWT::urlsafeB64Decode(string $input)`: Декодування Base64 безпечне для URL
- `JWT::$leeway`: Статична властивість для встановлення leeway часу для перевірки (у секундах)

## Чому використовувати цю бібліотеку?

- **Стандарт галузі**: Firebase JWT — найпопулярніша та широко довірена JWT бібліотека для PHP
- **Активне обслуговування**: Підтримується командою Google/Firebase
- **Фокус на безпеці**: Регулярні оновлення та патчі безпеки
- **Простий API**: Легко зрозуміти та реалізувати
- **Добре документована**: Розгорнута документація та підтримка спільноти
- **Гнучка**: Підтримує кілька алгоритмів та конфігурованих опцій

## Дивіться також

- [Репозиторій Firebase JWT на Github](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - Налагодження та декодування JWT
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - Офіційна специфікація JWT
- [Документація Middleware Flight](/learn/middleware)
- [Плагін Session Flight](/awesome-plugins/session) - Для традиційної аутентифікації на основі сесій

## Ліцензія

Бібліотека Firebase JWT ліцензована за BSD 3-Clause License. Дивіться [репозиторій на Github](https://github.com/firebase/php-jwt) для деталей.