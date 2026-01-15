# Firebase JWT - JSON Web Token 인증

JWT (JSON Web Tokens)는 애플리케이션과 클라이언트 간의 클레임을 표현하는 컴팩트하고 URL 안전한 방법입니다. 서버 측 세션 저장소가 필요 없는 stateless API 인증에 완벽합니다! 이 가이드는 [Firebase JWT](https://github.com/firebase/php-jwt)를 Flight와 통합하여 안전한 토큰 기반 인증을 구현하는 방법을 보여줍니다.

전체 문서와 세부 사항은 [Github 저장소](https://github.com/firebase/php-jwt)를 방문하세요.

## JWT란 무엇인가?

JSON Web Token은 세 부분으로 구성된 문자열입니다:
1. **헤더**: 토큰에 대한 메타데이터 (알고리즘, 유형)
2. **페이로드**: 데이터 (사용자 ID, 역할, 만료 등)
3. **서명**: 진위성을 확인하기 위한 암호화 서명

예제 JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...` (무의미해 보이지만 구조화된 데이터입니다!)

### JWT를 사용하는 이유는?

- **Stateless**: 서버 측 세션 저장소가 필요 없음—마이크로서비스와 API에 완벽
- **확장 가능**: 세션 affinity 요구 사항이 없어 로드 밸런서와 잘 작동
- **크로스 도메인**: 다른 도메인과 서비스 간에 사용할 수 있음
- **모바일 친화적**: 쿠키가 잘 작동하지 않는 모바일 앱에 훌륭
- **표준화**: 산업 표준 접근 방식 (RFC 7519)

## 설치

Composer를 통해 설치하세요:

```bash
composer require firebase/php-jwt
```

## 기본 사용법

JWT 생성 및 검증의 간단한 예제입니다:

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// 비밀 키 (이것을 안전하게 보관하세요!)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// 토큰 생성
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // 발급 시각
    'exp' => time() + 3600        // 1시간 후 만료
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// 토큰 검증 및 디코딩
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Invalid token: " . $e->getMessage();
}
```

## Flight를 위한 JWT 미들웨어 (권장 접근 방식)

Flight에서 JWT를 사용하는 가장 일반적이고 유용한 방법은 API 경로를 보호하는 **미들웨어**입니다. 완전하고 프로덕션 준비된 예제입니다:

### 단계 1: JWT 미들웨어 클래스 생성

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
        // 비밀 키를 app/config/config.php에 저장하세요, 하드코딩하지 마세요!
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Authorization 헤더 존재 여부 확인
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'No authorization token provided'], 401);
        }

        // "Bearer <token>" 형식에서 토큰 추출
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Invalid authorization format. Use: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // 토큰 디코딩 및 검증
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // 라우트 핸들러에서 사용하기 위해 사용자 데이터를 요청에 저장
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

### 단계 2: 구성에 JWT 비밀 키 등록

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php 또는 index.php
// 구성 파일을 앱에 노출하려면 이 줄을 추가하세요
$app->set('config', $config);
```

> **보안 주의**: 비밀 키를 하드코딩하지 마세요! 프로덕션에서는 환경 변수를 사용하세요.

### 단계 3: 미들웨어로 경로 보호

```php
// 단일 경로 보호
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // 미들웨어에서 설정
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware(JwtMiddleware::class);

// 전체 경로 그룹 보호 (더 일반적!)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // 이 그룹의 모든 경로가 보호됩니다!
```

미들웨어에 대한 자세한 내용은 [미들웨어 문서](/learn/middleware)를 참조하세요.

## 일반적인 사용 사례

### 1. 로그인 엔드포인트 (토큰 생성)

인증 성공 후 JWT를 생성하는 경로를 만드세요:

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // 자격 증명 검증 (예제 - 자체 로직 사용!)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Invalid credentials'], 401);
    }

    // JWT 생성
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // 1시간 만료
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // 데이터베이스 조회 및 비밀번호 검증은 여기에
    // 예제:
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

### 2. 토큰 갱신 흐름

장기 세션을 위한 리프레시 토큰 시스템을 구현하세요:

```php
Flight::route('POST /api/login', function() {
    // ... 자격 증명 검증 ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // 단기 액세스 토큰 (15분)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // 장기 리프레시 토큰 (7일)
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
        
        // 리프레시 토큰인지 확인
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Invalid token type'], 401);
        }
        
        // 새 액세스 토큰 생성
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

### 3. 역할 기반 액세스 제어

미들웨어를 확장하여 사용자 역할을 확인하세요:

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // JwtMiddleware가 이미 실행되어 사용자 데이터를 설정했다고 가정
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Authentication required'], 401);
        }
        
        // 필요한 역할이 있는지 확인
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Insufficient permissions'], 403);
        }
    }
}

// 사용: 관리자 전용 경로
Flight::route('DELETE /api/users/@id', function($id) {
    // 사용자 삭제 로직
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. 사용자별 속도 제한이 있는 공용 API

세션 없이 JWT를 사용하여 사용자 추적 및 속도 제한:

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // app/config/services.php에 캐시 서비스를 설정하세요
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 시간당 100 요청
            Flight::jsonHalt(['error' => 'Rate limit exceeded'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## 보안 모범 사례

### 1. 강력한 비밀 키 사용

```php
// 안전한 비밀 키 생성 (한 번 실행하고 .env 파일에 저장)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // .env 파일에 저장하세요!
```

### 2. 환경 변수에 비밀 저장

```php
// 비밀을 버전 제어에 커밋하지 마세요!
// .env 파일과 vlucas/phpdotenv 같은 라이브러리 사용

// .env 파일:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// 앱 구성 파일을 사용하여 비밀 저장할 수도 있음
// 구성 파일이 버전 제어에 커밋되지 않도록 하세요
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// 앱에서:
$secretKey = getenv('JWT_SECRET');
```

### 3. 적절한 만료 시간 설정

```php
// 좋은 관행: 단기 액세스 토큰
'exp' => time() + (15 * 60)  // 15분

// 리프레시 토큰: 더 긴 만료
'exp' => time() + (7 * 24 * 60 * 60)  // 7일
```

### 4. 프로덕션에서 HTTPS 사용

JWT는 **항상** HTTPS를 통해 전송되어야 합니다. 프로덕션에서 평문 HTTP로 토큰을 보내지 마세요!

### 5. 토큰 클레임 검증

중요한 클레임을 항상 검증하세요:

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// 만료 확인은 라이브러리에서 자동 처리
// 하지만 사용자 지정 검증 추가 가능:
if ($decoded->iat > time()) {
    throw new Exception('Token used before it was issued');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token not yet valid');
}
```

### 6. 로그아웃을 위한 토큰 블랙리스트 고려

추가 보안을 위해 무효화된 토큰의 블랙리스트를 유지하세요:

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // 토큰 만료 추출
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // 만료까지 캐시/Redis에 저장
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Successfully logged out']);
});

// JwtMiddleware에 추가:
public function before(array $params) {
    // ... JWT 추출 ...
    
    // 블랙리스트 확인
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Token has been revoked'], 401);
    }
    
    // ... 토큰 검증 ...
}
```

## 알고리즘 및 키 유형

Firebase JWT는 여러 알고리즘을 지원합니다:

### 대칭 알고리즘 (HMAC)
- **HS256** (대부분의 앱에 권장): 단일 비밀 키 사용
- **HS384**, **HS512**: 더 강력한 변형

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### 비대칭 알고리즘 (RSA/ECDSA)
- **RS256**, **RS384**, **RS512**: 공개/비공개 키 쌍 사용
- **ES256**, **ES384**, **ES512**: 타원 곡선 변형

```php
// 키 생성: openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// 비공개 키로 인코딩
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// 공개 키로 디코딩
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **RSA를 사용할 때**: 검증을 위해 공개 키를 배포해야 할 때 사용 (예: 마이크로서비스, 타사 통합). 단일 애플리케이션의 경우 HS256이 더 간단하고 충분합니다.

## 문제 해결

### "Expired token" 오류
토큰의 `exp` 클레임이 과거입니다. 새 토큰을 발급하거나 토큰 갱신을 구현하세요.

### "Signature verification failed"
- 인코딩에 사용한 비밀 키와 다른 키로 디코딩 중
- 토큰이 변조됨
- 서버 간 시계 왜곡 (leeway 버퍼 추가)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // 60초 시계 왜곡 허용
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### 요청에서 토큰이 전송되지 않음
클라이언트가 `Authorization` 헤더를 보내는지 확인하세요:

```javascript
// JavaScript 예제
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## 메서드

Firebase JWT 라이브러리는 다음 핵심 메서드를 제공합니다:

- `JWT::encode(array $payload, string $key, string $alg)`: 페이로드에서 JWT 생성
- `JWT::decode(string $jwt, Key $key)`: JWT 디코딩 및 검증
- `JWT::urlsafeB64Encode(string $input)`: Base64 URL 안전 인코딩
- `JWT::urlsafeB64Decode(string $input)`: Base64 URL 안전 디코딩
- `JWT::$leeway`: 검증을 위한 시간 여유 설정 (초 단위) 정적 속성

## 이 라이브러리를 사용하는 이유는?

- **산업 표준**: Firebase JWT는 PHP에서 가장 인기 있고 신뢰받는 JWT 라이브러리
- **활성 유지보수**: Google/Firebase 팀에서 유지보수
- **보안 중심**: 정기 업데이트와 보안 패치
- **간단한 API**: 이해하고 구현하기 쉬움
- **잘 문서화됨**: 광범위한 문서와 커뮤니티 지원
- **유연함**: 여러 알고리즘과 구성 가능한 옵션 지원

## 관련 자료

- [Firebase JWT Github 저장소](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - JWT 디버그 및 디코딩
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - 공식 JWT 사양
- [Flight 미들웨어 문서](/learn/middleware)
- [Flight 세션 플러그인](/awesome-plugins/session) - 전통적인 세션 기반 인증용

## 라이선스

Firebase JWT 라이브러리는 BSD 3-Clause License로 라이선스되었습니다. 세부 사항은 [Github 저장소](https://github.com/firebase/php-jwt)를 참조하세요.