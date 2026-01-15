# Firebase JWT - JSON Web Token 认证

JWT（JSON Web Tokens）是一种紧凑的、URL 安全的表示应用程序与客户端之间声明的方式。它们非常适合无状态 API 认证——无需服务器端会话存储！本指南将向您展示如何将 [Firebase JWT](https://github.com/firebase/php-jwt) 与 Flight 集成，实现安全的基于令牌的认证。

访问 [Github 仓库](https://github.com/firebase/php-jwt) 以获取完整文档和详细信息。

## 什么是 JWT？

JSON Web Token 是一个包含三个部分的字符串：
1. **Header**：令牌的元数据（算法、类型）
2. **Payload**：您的数据（用户 ID、角色、过期时间等）
3. **Signature**：用于验证真实性的加密签名

示例 JWT：`eyJ0eXAiOiJKV1QiLCJhbGc...`（看起来像乱码，但它是结构化数据！）

### 为什么使用 JWT？

- **无状态**：无需服务器端会话存储——非常适合微服务和 API
- **可扩展**：与负载均衡器配合良好，因为没有会话亲和性要求
- **跨域**：可在不同域和服务之间使用
- **移动友好**：适合移动应用，Cookie 可能无法正常工作
- **标准化**：行业标准方法（RFC 7519）

## 安装

通过 Composer 安装：

```bash
composer require firebase/php-jwt
```

## 基本用法

这是一个创建和验证 JWT 的快速示例：

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// 您的密钥（请保持安全！）
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// 创建令牌
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // 签发时间
    'exp' => time() + 3600        // 1 小时后过期
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// 验证并解码令牌
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Invalid token: " . $e->getMessage();
}
```

## Flight 的 JWT 中间件（推荐方法）

在 Flight 中使用 JWT 最常见且最有用的方式是作为 **middleware** 来保护您的 API 路由。这是一个完整的、生产就绪的示例：

### 步骤 1：创建 JWT 中间件类

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
        // 在 app/config/config.php 中存储您的密钥，不要硬编码！
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // 检查 Authorization 头是否存在
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'No authorization token provided'], 401);
        }

        // 从 "Bearer <token>" 格式中提取令牌
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Invalid authorization format. Use: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // 解码并验证令牌
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // 将用户数据存储在请求中，以便路由处理器使用
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

### 步骤 2：在您的配置中注册 JWT 密钥

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php 或 index.php
// 如果您想将配置暴露给应用，请确保添加此行
$app->set('config', $config);
```

> **安全注意**：切勿硬编码您的密钥！在生产环境中使用环境变量。

### 步骤 3：使用中间件保护您的路由

```php
// 保护单个路由
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // 由中间件设置
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware(JwtMiddleware::class);

// 保护一组路由（更常见！）
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // 该组中的所有路由均受保护！
```

有关中间件的更多详细信息，请参阅 [middleware documentation](/learn/middleware)。

## 常见用例

### 1. 登录端点（令牌生成）

创建一个在认证成功后生成 JWT 的路由：

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // 验证凭据（示例 - 使用您自己的逻辑！）
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Invalid credentials'], 401);
    }

    // 生成 JWT
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // 1 小时过期
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // 在这里执行数据库查找和密码验证
    // 示例：
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

### 2. 令牌刷新流程

实现一个刷新令牌系统，用于长生命周期会话：

```php
Flight::route('POST /api/login', function() {
    // ... 验证凭据 ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // 短期访问令牌（15 分钟）
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // 长期刷新令牌（7 天）
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
        
        // 验证这是一个刷新令牌
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Invalid token type'], 401);
        }
        
        // 生成新的访问令牌
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

### 3. 基于角色的访问控制

扩展您的中间件以检查用户角色：

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // 假设 JwtMiddleware 已运行并设置了用户数据
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Authentication required'], 401);
        }
        
        // 检查用户是否具有所需角色
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Insufficient permissions'], 403);
        }
    }
}

// 用法：仅限管理员路由
Flight::route('DELETE /api/users/@id', function($id) {
    // 删除用户逻辑
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. 公共 API 按用户速率限制

使用 JWT 在无需会话的情况下跟踪和限制用户速率：

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // 确保在 app/config/services.php 中设置缓存服务
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 每小时 100 个请求
            Flight::jsonHalt(['error' => 'Rate limit exceeded'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## 安全最佳实践

### 1. 使用强密钥

```php
// 生成安全的密钥（运行一次，保存到 .env 文件）
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // 将此存储到您的 .env 文件中！
```

### 2. 将密钥存储在环境变量中

```php
// 切勿将密钥提交到版本控制！
// 使用 .env 文件和像 vlucas/phpdotenv 这样的库

// .env 文件：
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// 您也可以使用 app/config/config.php 文件存储密钥
// 只是确保配置文件未提交到版本控制
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// 在您的应用中：
$secretKey = getenv('JWT_SECRET');
```

### 3. 设置适当的过期时间

```php
// 良好实践：短期访问令牌
'exp' => time() + (15 * 60)  // 15 分钟

// 对于刷新令牌：更长的过期时间
'exp' => time() + (7 * 24 * 60 * 60)  // 7 天
```

### 4. 在生产环境中使用 HTTPS

JWT 应 **始终** 通过 HTTPS 传输。在生产环境中切勿通过纯 HTTP 发送令牌！

### 5. 验证令牌声明

始终验证您关心的声明：

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// 过期检查由库自动处理
// 但您可以添加自定义验证：
if ($decoded->iat > time()) {
    throw new Exception('Token used before it was issued');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token not yet valid');
}
```

### 6. 考虑注销时的令牌黑名单

为增强安全性，维护无效令牌的黑名单：

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // 提取令牌的过期时间
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // 存储到缓存/Redis 中直到过期
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Successfully logged out']);
});

// 添加到您的 JwtMiddleware：
public function before(array $params) {
    // ... 提取 JWT ...
    
    // 检查黑名单
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Token has been revoked'], 401);
    }
    
    // ... 验证令牌 ...
}
```

## 算法和密钥类型

Firebase JWT 支持多种算法：

### 对称算法 (HMAC)
- **HS256**（推荐用于大多数应用）：使用单个密钥
- **HS384**、**HS512**：更强的变体

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### 非对称算法 (RSA/ECDSA)
- **RS256**、**RS384**、**RS512**：使用公钥/私钥对
- **ES256**、**ES384**、**ES512**：椭圆曲线变体

```php
// 生成密钥：openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// 使用私钥编码
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// 使用公钥解码
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **何时使用 RSA**：当您需要分发公钥进行验证时使用 RSA（例如，微服务、第三方集成）。对于单个应用，HS256 更简单且足够。

## 故障排除

### “Expired token” 错误
您的令牌的 `exp` 声明已过期。颁发新令牌或实现令牌刷新。

### “Signature verification failed”
- 您使用不同的密钥解码，而编码时使用了不同的密钥
- 令牌已被篡改
- 服务器之间时钟偏差（添加宽限缓冲）

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // 允许 60 秒的时钟偏差
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### 请求中未发送令牌
确保您的客户端发送 `Authorization` 头：

```javascript
// JavaScript 示例
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## 方法

Firebase JWT 库提供这些核心方法：

- `JWT::encode(array $payload, string $key, string $alg)`：从负载创建 JWT
- `JWT::decode(string $jwt, Key $key)`：解码并验证 JWT
- `JWT::urlsafeB64Encode(string $input)`：Base64 URL 安全编码
- `JWT::urlsafeB64Decode(string $input)`：Base64 URL 安全解码
- `JWT::$leeway`：用于验证的时间宽限静态属性（以秒为单位）

## 为什么使用此库？

- **行业标准**：Firebase JWT 是 PHP 中最流行和最受信任的 JWT 库
- **积极维护**：由 Google/Firebase 团队维护
- **安全导向**：定期更新和安全补丁
- **简单 API**：易于理解和实现
- **文档完善**：广泛的文档和社区支持
- **灵活**：支持多种算法和可配置选项

## 另请参阅

- [Firebase JWT Github 仓库](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - 调试和解码 JWT
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - 官方 JWT 规范
- [Flight 中间件文档](/learn/middleware)
- [Flight 会话插件](/awesome-plugins/session) - 用于传统基于会话的认证

## 许可证

Firebase JWT 库采用 BSD 3-Clause 许可证。有关详细信息，请参阅 [Github 仓库](https://github.com/firebase/php-jwt)。