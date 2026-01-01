# Firebase JWT - Autenticação com JSON Web Token para Flight

JWT (JSON Web Tokens) são uma forma compacta e segura para URLs de representar reivindicações entre sua aplicação e um cliente. Eles são perfeitos para autenticação de API stateless — sem necessidade de armazenamento de sessão no lado do servidor! Este guia mostra como integrar [Firebase JWT](https://github.com/firebase/php-jwt) com Flight para autenticação segura baseada em tokens.

Visite o [repositório no Github](https://github.com/firebase/php-jwt) para documentação completa e detalhes.

## O que é JWT?

Um JSON Web Token é uma string que contém três partes:
1. **Header**: Metadados sobre o token (algoritmo, tipo)
2. **Payload**: Seus dados (ID do usuário, papéis, expiração, etc.)
3. **Signature**: Assinatura criptográfica para verificar a autenticidade

Exemplo de JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...` (parece gibberish, mas é dados estruturados!)

### Por que Usar JWT?

- **Stateless**: Não é necessário armazenamento de sessão no lado do servidor — perfeito para microsserviços e APIs
- **Escalável**: Funciona bem com balanceadores de carga, pois não há requisito de afinidade de sessão
- **Cross-Domain**: Pode ser usado em diferentes domínios e serviços
- **Amigável para Mobile**: Ótimo para apps móveis onde cookies podem não funcionar bem
- **Padronizado**: Abordagem padrão da indústria (RFC 7519)

## Instalação

Instale via Composer:

```bash
composer require firebase/php-jwt
```

## Uso Básico

Aqui está um exemplo rápido de criação e verificação de um JWT:

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Sua chave secreta (MANTENHA ISSO SEGURO!)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// Crie um token
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // Emitido em
    'exp' => time() + 3600        // Expira em 1 hora
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// Verifique e decodifique um token
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Invalid token: " . $e->getMessage();
}
```

## Middleware JWT para Flight (Abordagem Recomendada)

A forma mais comum e útil de usar JWT com Flight é como **middleware** para proteger suas rotas de API. Aqui está um exemplo completo, pronto para produção:

### Passo 1: Crie uma Classe de Middleware JWT

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
        // Armazene sua chave secreta em app/config/config.php, NÃO hardcoded!
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Verifique se o header de autorização existe
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'No authorization token provided'], 401);
        }

        // Extraia o token do formato "Bearer <token>"
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Invalid authorization format. Use: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // Decodifique e verifique o token
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // Armazene os dados do usuário na requisição para uso em manipuladores de rota
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

### Passo 2: Registre a Chave Secreta JWT na Sua Configuração

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php or index.php
// certifique-se de adicionar esta linha se quiser expor a configuração para o app
$app->set('config', $config);
```

> **Nota de Segurança**: Nunca hardcode sua chave secreta! Use variáveis de ambiente em produção.

### Passo 3: Proteja Suas Rotas com Middleware

```php
// Proteja uma rota única
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // Definido pelo middleware
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware( JwtMiddleware::class);

// Proteja um grupo inteiro de rotas (mais comum!)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // Todas as rotas neste grupo estão protegidas!
```

Para mais detalhes sobre middleware, veja a [documentação de middleware](/learn/middleware).

## Casos de Uso Comuns

### 1. Endpoint de Login (Geração de Token)

Crie uma rota que gera um JWT após autenticação bem-sucedida:

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // Valide credenciais (exemplo - use sua própria lógica!)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Invalid credentials'], 401);
    }

    // Gere JWT
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // 1 hora de expiração
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // Sua consulta ao banco de dados e verificação de senha aqui
    // Exemplo:
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

### 2. Fluxo de Atualização de Token

Implemente um sistema de token de atualização para sessões de longa duração:

```php
Flight::route('POST /api/login', function() {
    // ... valide credenciais ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // Token de acesso de curta duração (15 minutos)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // Token de atualização de longa duração (7 dias)
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
        
        // Verifique se é um token de atualização
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Invalid token type'], 401);
        }
        
        // Gere novo token de acesso
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

### 3. Controle de Acesso Baseado em Papéis

Estenda seu middleware para verificar papéis de usuário:

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // Assuma que JwtMiddleware já executou e definiu os dados do usuário
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Authentication required'], 401);
        }
        
        // Verifique se o usuário tem o papel necessário
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Insufficient permissions'], 403);
        }
    }
}

// Uso: Rota apenas para admin
Flight::route('DELETE /api/users/@id', function($id) {
    // Lógica de exclusão de usuário
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. API Pública com Limitação de Taxa por Usuário

Use JWT para rastrear e limitar a taxa de usuários sem sessões:

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // Certifique-se de configurar um serviço de cache em app/config/services.php
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 100 requisições por hora
            Flight::jsonHalt(['error' => 'Rate limit exceeded'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## Melhores Práticas de Segurança

### 1. Use Chaves Secretas Fortes

```php
// Gere uma chave secreta segura (execute uma vez, salve no arquivo .env)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // Armazene isso no seu arquivo .env!
```

### 2. Armazene Segredos em Variáveis de Ambiente

```php
// Nunca commite segredos no controle de versão!
// Use um arquivo .env e biblioteca como vlucas/phpdotenv

// Arquivo .env:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// Você também pode usar o arquivo app/config/config.php para armazenar seus segredos
// apenas certifique-se de que o arquivo de configuração não seja commitado no controle de versão
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// Na sua app:
$secretKey = getenv('JWT_SECRET');
```

### 3. Defina Tempos de Expiração Apropriados

```php
// Boa prática: tokens de acesso de curta duração
'exp' => time() + (15 * 60)  // 15 minutos

// Para tokens de atualização: expiração mais longa
'exp' => time() + (7 * 24 * 60 * 60)  // 7 dias
```

### 4. Use HTTPS em Produção

JWTs devem **sempre** ser transmitidos via HTTPS. Nunca envie tokens via HTTP simples em produção!

### 5. Valide Reivindicações do Token

Sempre valide as reivindicações que você se importa:

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// Verificação de expiração é tratada automaticamente pela biblioteca
// Mas você pode adicionar validações personalizadas:
if ($decoded->iat > time()) {
    throw new Exception('Token used before it was issued');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token not yet valid');
}
```

### 6. Considere Blacklisting de Tokens para Logout

Para segurança extra, mantenha uma lista negra de tokens invalidados:

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // Extraia a expiração do token
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // Armazene no cache/redis até a expiração
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Successfully logged out']);
});

// Adicione ao seu JwtMiddleware:
public function before(array $params) {
    // ... extraia JWT ...
    
    // Verifique a lista negra
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Token has been revoked'], 401);
    }
    
    // ... verifique o token ...
}
```

## Algoritmos e Tipos de Chave

Firebase JWT suporta múltiplos algoritmos:

### Algoritmos Simétricos (HMAC)
- **HS256** (Recomendado para a maioria dos apps): Usa uma única chave secreta
- **HS384**, **HS512**: Variantes mais fortes

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Algoritmos Assimétricos (RSA/ECDSA)
- **RS256**, **RS384**, **RS512**: Usa pares de chaves pública/privada
- **ES256**, **ES384**, **ES512**: Variantes de curva elíptica

```php
// Gere chaves: openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// Codifique com chave privada
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// Decodifique com chave pública
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **Quando usar RSA**: Use RSA quando precisar distribuir a chave pública para verificação (ex.: microsserviços, integrações de terceiros). Para uma única aplicação, HS256 é mais simples e suficiente.

## Solução de Problemas

### Erro "Token expirado"
A reivindicação `exp` do seu token está no passado. Emita um novo token ou implemente atualização de token.

### "Falha na verificação de assinatura"
- Você está usando uma chave secreta diferente para decodificar do que usou para codificar
- O token foi adulterado
- Desvio de relógio entre servidores (adicione um buffer de leeway)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // Permita 60 segundos de desvio de relógio
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Token Não Enviado nas Requisições
Certifique-se de que seu cliente está enviando o header `Authorization`:

```javascript
// Exemplo em JavaScript
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## Métodos

A biblioteca Firebase JWT fornece estes métodos principais:

- `JWT::encode(array $payload, string $key, string $alg)`: Cria um JWT a partir de um payload
- `JWT::decode(string $jwt, Key $key)`: Decodifica e verifica um JWT
- `JWT::urlsafeB64Encode(string $input)`: Codificação Base64 URL-safe
- `JWT::urlsafeB64Decode(string $input)`: Decodificação Base64 URL-safe
- `JWT::$leeway`: Propriedade estática para definir leeway de tempo para validação (em segundos)

## Por que Usar Esta Biblioteca?

- **Padrão da Indústria**: Firebase JWT é a biblioteca JWT mais popular e amplamente confiável para PHP
- **Manutenção Ativa**: Mantida pela equipe do Google/Firebase
- **Focada em Segurança**: Atualizações regulares e patches de segurança
- **API Simples**: Fácil de entender e implementar
- **Bem Documentada**: Documentação extensa e suporte da comunidade
- **Flexível**: Suporta múltiplos algoritmos e opções configuráveis

## Veja Também

- [Repositório Github do Firebase JWT](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - Debug e decodificação de JWTs
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - Especificação oficial de JWT
- [Documentação de Middleware do Flight](/learn/middleware)
- [Plugin de Sessão do Flight](/awesome-plugins/session) - Para autenticação baseada em sessões tradicionais

## Licença

A biblioteca Firebase JWT é licenciada sob a Licença BSD 3-Clause. Veja o [repositório no Github](https://github.com/firebase/php-jwt) para detalhes.