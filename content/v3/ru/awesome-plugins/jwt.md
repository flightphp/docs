# Firebase JWT - Аутентификация с использованием JSON Web Token

JWT (JSON Web Tokens) — это компактный и безопасный для URL способ представления утверждений между вашим приложением и клиентом. Они идеально подходят для аутентификации stateless API — без необходимости хранения сессий на сервере! Это руководство показывает, как интегрировать [Firebase JWT](https://github.com/firebase/php-jwt) с Flight для безопасной аутентификации на основе токенов.

Посетите [репозиторий на Github](https://github.com/firebase/php-jwt) для полной документации и деталей.

## Что такое JWT?

JSON Web Token — это строка, содержащая три части:
1. **Заголовок**: Метаданные о токене (алгоритм, тип)
2. **Полезная нагрузка**: Ваши данные (ID пользователя, роли, срок истечения и т.д.)
3. **Подпись**: Криптографическая подпись для проверки подлинности

Пример JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...` (выглядит как бессмыслица, но это структурированные данные!)

### Почему использовать JWT?

- **Stateless**: Не требуется хранение сессий на сервере — идеально для микросервисов и API
- **Масштабируемость**: Отлично работает с балансировщиками нагрузки, поскольку нет требования к affinity сессий
- **Кросс-доменная**: Может использоваться через разные домены и сервисы
- **Дружественно к мобильным**: Отлично для мобильных приложений, где куки могут не работать хорошо
- **Стандартизировано**: Стандарт отрасли (RFC 7519)

## Установка

Установите через Composer:

```bash
composer require firebase/php-jwt
```

## Базовое использование

Вот быстрый пример создания и проверки JWT:

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Ваш секретный ключ (ХРАНИТЕ ЕГО В БЕЗОПАСНОМ МЕСТЕ!)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// Создание токена
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // Выдан в
    'exp' => time() + 3600        // Истекает через 1 час
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// Проверка и декодирование токена
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Invalid token: " . $e->getMessage();
}
```

## Middleware JWT для Flight (Рекомендуемый подход)

Наиболее распространенный и полезный способ использования JWT с Flight — это как **middleware** для защиты маршрутов API. Вот полный, готовый к производству пример:

### Шаг 1: Создание класса JWT Middleware

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
        // Храните ваш секретный ключ в app/config/config.php, НЕ хардкодьте!
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Проверка наличия заголовка Authorization
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'No authorization token provided'], 401);
        }

        // Извлечение токена из формата "Bearer <token>"
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Invalid authorization format. Use: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // Декодирование и проверка токена
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // Сохранение данных пользователя в запросе для использования в обработчиках маршрутов
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

### Шаг 2: Регистрация JWT секрета в вашей конфигурации

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php или index.php
// убедитесь, что добавили эту строку, если хотите предоставить конфигурацию приложению
$app->set('config', $config);
```

> **Примечание по безопасности**: Никогда не хардкодьте ваш секретный ключ! Используйте переменные окружения в производстве.

### Шаг 3: Защита ваших маршрутов с помощью Middleware

```php
// Защита одного маршрута
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // Установлено middleware
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware(JwtMiddleware::class);

// Защита всей группы маршрутов (более распространено!)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // Все маршруты в этой группе защищены!
```

Для более подробной информации о middleware см. [документацию по middleware](/learn/middleware).

## Распространенные сценарии использования

### 1. Эндпоинт входа (Генерация токена)

Создайте маршрут, который генерирует JWT после успешной аутентификации:

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // Валидация учетных данных (пример — используйте свою логику!)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Invalid credentials'], 401);
    }

    // Генерация JWT
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // Истечение через 1 час
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // Ваш поиск в базе данных и проверка пароля здесь
    // Пример:
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

### 2. Поток обновления токена

Реализуйте систему обновления токенов для долгоживущих сессий:

```php
Flight::route('POST /api/login', function() {
    // ... валидация учетных данных ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // Короткоживущий токен доступа (15 минут)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // Долгоживущий токен обновления (7 дней)
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
        
        // Проверка, что это токен обновления
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Invalid token type'], 401);
        }
        
        // Генерация нового токена доступа
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

### 3. Контроль доступа на основе ролей

Расширьте ваше middleware для проверки ролей пользователя:

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // Предполагаем, что JwtMiddleware уже выполнился и установил данные пользователя
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Authentication required'], 401);
        }
        
        // Проверка наличия требуемой роли у пользователя
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Insufficient permissions'], 403);
        }
    }
}

// Использование: Маршрут только для админов
Flight::route('DELETE /api/users/@id', function($id) {
    // Логика удаления пользователя
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. Публичный API с ограничением скорости по пользователю

Используйте JWT для отслеживания и ограничения скорости пользователей без сессий:

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // Убедитесь, что вы настроили сервис кэша в app/config/services.php
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 100 запросов в час
            Flight::jsonHalt(['error' => 'Rate limit exceeded'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## Лучшие практики безопасности

### 1. Используйте сильные секретные ключи

```php
// Генерация безопасного секретного ключа (запустите один раз, сохраните в .env файл)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // Сохраните это в вашем .env файле!
```

### 2. Храните секреты в переменных окружения

```php
// Никогда не коммитьте секреты в контроль версий!
// Используйте .env файл и библиотеку вроде vlucas/phpdotenv

// .env файл:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// Вы также можете использовать файл app/config/config.php для хранения секретов
// просто убедитесь, что файл конфигурации не коммитится в контроль версий
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// В вашем приложении:
$secretKey = getenv('JWT_SECRET');
```

### 3. Устанавливайте подходящие сроки истечения

```php
// Хорошая практика: короткоживущие токены доступа
'exp' => time() + (15 * 60)  // 15 минут

// Для токенов обновления: более длинное истечение
'exp' => time() + (7 * 24 * 60 * 60)  // 7 дней
```

### 4. Используйте HTTPS в производстве

JWT **всегда** должны передаваться по HTTPS. Никогда не отправляйте токены по обычному HTTP в производстве!

### 5. Валидируйте утверждения токена

Всегда валидируйте утверждения, которые вас интересуют:

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// Проверка истечения обрабатывается библиотекой автоматически
// Но вы можете добавить кастомные валидации:
if ($decoded->iat > time()) {
    throw new Exception('Token used before it was issued');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token not yet valid');
}
```

### 6. Рассмотрите черный список токенов для выхода

Для дополнительной безопасности поддерживайте черный список недействительных токенов:

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // Извлечение срока истечения токена
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // Сохранение в кэше/redis до истечения
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Successfully logged out']);
});

// Добавьте в ваше JwtMiddleware:
public function before(array $params) {
    // ... извлечение JWT ...
    
    // Проверка черного списка
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Token has been revoked'], 401);
    }
    
    // ... проверка токена ...
}
```

## Алгоритмы и типы ключей

Firebase JWT поддерживает несколько алгоритмов:

### Симметричные алгоритмы (HMAC)
- **HS256** (Рекомендуется для большинства приложений): Использует один секретный ключ
- **HS384**, **HS512**: Более сильные варианты

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Асимметричные алгоритмы (RSA/ECDSA)
- **RS256**, **RS384**, **RS512**: Использует пары публичный/приватный ключ
- **ES256**, **ES384**, **ES512**: Варианты на эллиптических кривых

```php
// Генерация ключей: openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// Кодирование с приватным ключом
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// Декодирование с публичным ключом
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **Когда использовать RSA**: Используйте RSA, когда нужно распространить публичный ключ для проверки (например, микросервисы, интеграции с третьими сторонами). Для одного приложения HS256 проще и достаточно.

## Устранение неисправностей

### Ошибка "Expired token"
Утверждение `exp` вашего токена в прошлом. Выдайте новый токен или реализуйте обновление токена.

### "Signature verification failed"
- Вы используете другой секретный ключ для декодирования, чем для кодирования
- Токен был изменен
- Разница во времени между серверами (добавьте буфер leeway)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // Разрешить 60 секунд разницы во времени
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Токен не отправляется в запросах
Убедитесь, что ваш клиент отправляет заголовок `Authorization`:

```javascript
// Пример на JavaScript
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## Методы

Библиотека Firebase JWT предоставляет эти основные методы:

- `JWT::encode(array $payload, string $key, string $alg)`: Создает JWT из полезной нагрузки
- `JWT::decode(string $jwt, Key $key)`: Декодирует и проверяет JWT
- `JWT::urlsafeB64Encode(string $input)`: Base64 кодирование безопасное для URL
- `JWT::urlsafeB64Decode(string $input)`: Base64 декодирование безопасное для URL
- `JWT::$leeway`: Статическое свойство для установки временного leeway для валидации (в секундах)

## Почему использовать эту библиотеку?

- **Стандарт отрасли**: Firebase JWT — самая популярная и широко доверенная библиотека JWT для PHP
- **Активное обслуживание**: Поддерживается командой Google/Firebase
- **Фокус на безопасности**: Регулярные обновления и патчи безопасности
- **Простой API**: Легко понять и реализовать
- **Хорошо документировано**: Обширная документация и поддержка сообщества
- **Гибко**: Поддерживает несколько алгоритмов и настраиваемые опции

## См. также

- [Репозиторий Firebase JWT на Github](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - Отладка и декодирование JWT
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - Официальная спецификация JWT
- [Документация по Middleware Flight](/learn/middleware)
- [Плагин сессий Flight](/awesome-plugins/session) - Для традиционной аутентификации на основе сессий

## Лицензия

Библиотека Firebase JWT лицензирована по BSD 3-Clause License. См. [репозиторий на Github](https://github.com/firebase/php-jwt) для деталей.