# Firebase JWT - JSON Web Token Authentication for Flight

JWT (JSON Web Tokens) are a compact, URL-safe way to represent claims between your application and a client. They're perfect for stateless API authentication—no need for server-side session storage! This guide shows you how to integrate [Firebase JWT](https://github.com/firebase/php-jwt) with Flight for secure, token-based authentication.

Visit the [Github repository](https://github.com/firebase/php-jwt) for full documentation and details.

## What is JWT?

A JSON Web Token is a string that contains three parts:
1. **Header**: Metadata about the token (algorithm, type)
2. **Payload**: Your data (user ID, roles, expiration, etc.)
3. **Signature**: Cryptographic signature to verify authenticity

Example JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...` (looks like gibberish, but it's structured data!)

### Why Use JWT?

- **Stateless**: No server-side session storage needed—perfect for microservices and APIs
- **Scalable**: Works great with load balancers since there's no session affinity requirement
- **Cross-Domain**: Can be used across different domains and services
- **Mobile-Friendly**: Great for mobile apps where cookies may not work well
- **Standardized**: Industry-standard approach (RFC 7519)

## Installation

Install via Composer:

```bash
composer require firebase/php-jwt
```

## Basic Usage

Here's a quick example of creating and verifying a JWT:

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Your secret key (KEEP THIS SECURE!)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// Create a token
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // Issued at
    'exp' => time() + 3600        // Expires in 1 hour
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// Verify and decode a token
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Invalid token: " . $e->getMessage();
}
```

## JWT Middleware for Flight (Recommended Approach)

The most common and useful way to use JWT with Flight is as **middleware** to protect your API routes. Here's a complete, production-ready example:

### Step 1: Create a JWT Middleware Class

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
        // Store your secret key in app/config/config.php, NOT hardcoded!
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Check if Authorization header exists
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'No authorization token provided'], 401);
        }

        // Extract token from "Bearer <token>" format
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Invalid authorization format. Use: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // Decode and verify the token
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // Store user data in request for use in route handlers
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

### Step 2: Register JWT Secret in Your Config

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php or index.php
// make sure to add this line if you want to expose the config to the app
$app->set('config', $config);
```

> **Security Note**: Never hardcode your secret key! Use environment variables in production.

### Step 3: Protect Your Routes with Middleware

```php
// Protect a single route
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // Set by middleware
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware(JwtMiddleware::class);

// Protect an entire group of routes (more common!)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // All routes in this group are protected!
```

For more details on middleware, see the [middleware documentation](/learn/middleware).

## Common Use Cases

### 1. Login Endpoint (Token Generation)

Create a route that generates a JWT after successful authentication:

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // Validate credentials (example - use your own logic!)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Invalid credentials'], 401);
    }

    // Generate JWT
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // 1 hour expiration
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // Your database lookup and password verification here
    // Example:
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

### 2. Token Refresh Flow

Implement a refresh token system for long-lived sessions:

```php
Flight::route('POST /api/login', function() {
    // ... validate credentials ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // Short-lived access token (15 minutes)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // Long-lived refresh token (7 days)
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
        
        // Verify this is a refresh token
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Invalid token type'], 401);
        }
        
        // Generate new access token
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

### 3. Role-Based Access Control

Extend your middleware to check user roles:

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // Assume JwtMiddleware already ran and set user data
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Authentication required'], 401);
        }
        
        // Check if user has required role
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Insufficient permissions'], 403);
        }
    }
}

// Usage: Admin-only route
Flight::route('DELETE /api/users/@id', function($id) {
    // Delete user logic
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. Public API with Rate Limiting by User

Use JWT to track and rate-limit users without sessions:

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // Make sure you set up a cache service in app/config/services.php
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 100 requests per hour
            Flight::jsonHalt(['error' => 'Rate limit exceeded'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## Security Best Practices

### 1. Use Strong Secret Keys

```php
// Generate a secure secret key (run once, save to .env file)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // Store this in your .env file!
```

### 2. Store Secrets in Environment Variables

```php
// Never commit secrets to version control!
// Use a .env file and library like vlucas/phpdotenv

// .env file:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// You can also use the app/config/config.php file to store your secrets
// just make sure that the config file is not committed to version control
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// In your app:
$secretKey = getenv('JWT_SECRET');
```

### 3. Set Appropriate Expiration Times

```php
// Good practice: short-lived access tokens
'exp' => time() + (15 * 60)  // 15 minutes

// For refresh tokens: longer expiration
'exp' => time() + (7 * 24 * 60 * 60)  // 7 days
```

### 4. Use HTTPS in Production

JWTs should **always** be transmitted over HTTPS. Never send tokens over plain HTTP in production!

### 5. Validate Token Claims

Always validate the claims you care about:

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// Check expiration is handled automatically by the library
// But you can add custom validations:
if ($decoded->iat > time()) {
    throw new Exception('Token used before it was issued');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token not yet valid');
}
```

### 6. Consider Token Blacklisting for Logout

For extra security, maintain a blacklist of invalidated tokens:

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // Extract the token's expiration
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // Store in cache/redis until expiration
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Successfully logged out']);
});

// Add to your JwtMiddleware:
public function before(array $params) {
    // ... extract JWT ...
    
    // Check blacklist
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Token has been revoked'], 401);
    }
    
    // ... verify token ...
}
```

## Algorithms and Key Types

Firebase JWT supports multiple algorithms:

### Symmetric Algorithms (HMAC)
- **HS256** (Recommended for most apps): Uses a single secret key
- **HS384**, **HS512**: Stronger variants

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Asymmetric Algorithms (RSA/ECDSA)
- **RS256**, **RS384**, **RS512**: Uses public/private key pairs
- **ES256**, **ES384**, **ES512**: Elliptic curve variants

```php
// Generate keys: openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// Encode with private key
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// Decode with public key
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **When to use RSA**: Use RSA when you need to distribute the public key for verification (e.g., microservices, third-party integrations). For a single application, HS256 is simpler and sufficient.

## Troubleshooting

### "Expired token" Error
Your token's `exp` claim is in the past. Issue a new token or implement token refresh.

### "Signature verification failed"
- You're using a different secret key to decode than you used to encode
- The token has been tampered with
- Clock skew between servers (add a leeway buffer)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // Allow 60 seconds of clock skew
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Token Not Being Sent in Requests
Make sure your client is sending the `Authorization` header:

```javascript
// JavaScript example
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## Methods

The Firebase JWT library provides these core methods:

- `JWT::encode(array $payload, string $key, string $alg)`: Creates a JWT from a payload
- `JWT::decode(string $jwt, Key $key)`: Decodes and verifies a JWT
- `JWT::urlsafeB64Encode(string $input)`: Base64 URL-safe encoding
- `JWT::urlsafeB64Decode(string $input)`: Base64 URL-safe decoding
- `JWT::$leeway`: Static property to set time leeway for validation (in seconds)

## Why Use This Library?

- **Industry Standard**: Firebase JWT is the most popular and widely trusted JWT library for PHP
- **Active Maintenance**: Maintained by Google/Firebase team
- **Security Focused**: Regular updates and security patches
- **Simple API**: Easy to understand and implement
- **Well Documented**: Extensive documentation and community support
- **Flexible**: Supports multiple algorithms and configurable options

## See Also

- [Firebase JWT Github Repository](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - Debug and decode JWTs
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - Official JWT specification
- [Flight Middleware Documentation](/learn/middleware)
- [Flight Session Plugin](/awesome-plugins/session) - For traditional session-based auth

## License

The Firebase JWT library is licensed under the BSD 3-Clause License. See the [Github repository](https://github.com/firebase/php-jwt) for details.
