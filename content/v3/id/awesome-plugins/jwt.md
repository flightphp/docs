# Firebase JWT - Autentikasi JSON Web Token

JWT (JSON Web Tokens) adalah cara yang ringkas dan aman untuk URL untuk merepresentasikan klaim antara aplikasi Anda dan klien. Mereka sempurna untuk autentikasi API tanpa state—tidak perlu penyimpanan sesi di sisi server! Panduan ini menunjukkan cara mengintegrasikan [Firebase JWT](https://github.com/firebase/php-jwt) dengan Flight untuk autentikasi berbasis token yang aman.

Kunjungi [repositori Github](https://github.com/firebase/php-jwt) untuk dokumentasi lengkap dan detail.

## Apa itu JWT?

JSON Web Token adalah string yang berisi tiga bagian:
1. **Header**: Metadata tentang token (algoritma, tipe)
2. **Payload**: Data Anda (ID pengguna, peran, kedaluwarsa, dll.)
3. **Signature**: Tanda tangan kriptografis untuk memverifikasi keaslian

Contoh JWT: `eyJ0eXAiOiJKV1QiLCJhbGc...` (terlihat seperti omong kosong, tapi itu data terstruktur!)

### Mengapa Menggunakan JWT?

- **Tanpa State**: Tidak perlu penyimpanan sesi di sisi server—sempurna untuk microservices dan API
- **Skalabel**: Bekerja dengan baik dengan load balancer karena tidak ada persyaratan affinity sesi
- **Cross-Domain**: Dapat digunakan di berbagai domain dan layanan
- **Ramah Mobile**: Bagus untuk aplikasi mobile di mana cookie mungkin tidak bekerja dengan baik
- **Standar**: Pendekatan standar industri (RFC 7519)

## Instalasi

Instal melalui Composer:

```bash
composer require firebase/php-jwt
```

## Penggunaan Dasar

Berikut contoh cepat untuk membuat dan memverifikasi JWT:

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Kunci rahasia Anda (JAGA INI AMAN!)
$secretKey = 'your-256-bit-secret-key-here-keep-it-safe';

// Buat token
$payload = [
    'user_id' => 123,
    'username' => 'johndoe',
    'role' => 'admin',
    'iat' => time(),              // Diterbitkan pada
    'exp' => time() + 3600        // Kedaluwarsa dalam 1 jam
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');
echo "Token: " . $jwt;

// Verifikasi dan dekode token
try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    echo "User ID: " . $decoded->user_id;
} catch (Exception $e) {
    echo "Token tidak valid: " . $e->getMessage();
}
```

## Middleware JWT untuk Flight (Pendekatan yang Direkomendasikan)

Cara paling umum dan berguna untuk menggunakan JWT dengan Flight adalah sebagai **middleware** untuk melindungi rute API Anda. Berikut contoh lengkap yang siap produksi:

### Langkah 1: Buat Kelas Middleware JWT

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
        // Simpan kunci rahasia Anda di app/config/config.php, BUKAN hardcoded!
        $this->secretKey = $app->get('config')['jwt_secret'];
    }

    public function before(array $params) {
        $authHeader = $this->app->request()->getHeader('Authorization');

        // Periksa apakah header Authorization ada
        if (empty($authHeader)) {
            $this->app->jsonHalt(['error' => 'Tidak ada token otorisasi yang disediakan'], 401);
        }

        // Ekstrak token dari format "Bearer <token>"
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->app->jsonHalt(['error' => 'Format otorisasi tidak valid. Gunakan: Bearer <token>'], 401);
        }

        $jwt = $matches[1];

        try {
            // Dekode dan verifikasi token
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // Simpan data pengguna di request untuk digunakan di handler rute
            $this->app->request()->data->user = $decoded;
            
        } catch (ExpiredException $e) {
            $this->app->jsonHalt(['error' => 'Token telah kedaluwarsa'], 401);
        } catch (SignatureInvalidException $e) {
            $this->app->jsonHalt(['error' => 'Tanda tangan token tidak valid'], 401);
        } catch (Exception $e) {
            $this->app->jsonHalt(['error' => 'Token tidak valid: ' . $e->getMessage()], 401);
        }
    }
}
```

### Langkah 2: Daftarkan Kunci Rahasia JWT di Konfigurasi Anda

```php
// app/config/config.php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-fallback-secret-for-development'
];

// app/config/bootstrap.php atau index.php
// pastikan untuk menambahkan baris ini jika Anda ingin mengekspos konfigurasi ke aplikasi
$app->set('config', $config);
```

> **Catatan Keamanan**: Jangan pernah hardcoded kunci rahasia Anda! Gunakan variabel lingkungan di produksi.

### Langkah 3: Lindungi Rute Anda dengan Middleware

```php
// Lindungi satu rute
Flight::route('GET /api/user/profile', function() {
    $user = Flight::request()->data->user; // Ditetapkan oleh middleware
    Flight::json([
        'user_id' => $user->user_id,
        'username' => $user->username,
        'role' => $user->role
    ]);
})->addMiddleware(JwtMiddleware::class);

// Lindungi seluruh grup rute (lebih umum!)
Flight::group('/api', function() {
    Flight::route('GET /users', function() { /* ... */ });
    Flight::route('GET /posts', function() { /* ... */ });
    Flight::route('POST /posts', function() { /* ... */ });
    Flight::route('DELETE /posts/@id', function($id) { /* ... */ });
}, [ JwtMiddleware::class ]); // Semua rute di grup ini dilindungi!
```

Untuk detail lebih lanjut tentang middleware, lihat [dokumentasi middleware](/learn/middleware).

## Kasus Penggunaan Umum

### 1. Endpoint Login (Pembuatan Token)

Buat rute yang menghasilkan JWT setelah autentikasi berhasil:

```php
Flight::route('POST /api/login', function() {
    $data = Flight::request()->data;
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    // Validasi kredensial (contoh - gunakan logika Anda sendiri!)
    $user = validateUserCredentials($username, $password);
    
    if (!$user) {
        Flight::jsonHalt(['error' => 'Kredensial tidak valid'], 401);
    }

    // Hasilkan JWT
    $secretKey = Flight::get('config')['jwt_secret'];
    $payload = [
        'user_id' => $user->id,
        'username' => $user->username,
        'role' => $user->role,
        'iat' => time(),
        'exp' => time() + (60 * 60) // Kedaluwarsa 1 jam
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'expires_in' => 3600
    ]);
});

function validateUserCredentials($username, $password) {
    // Pencarian database dan verifikasi kata sandi Anda di sini
    // Contoh:
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

### 2. Alur Pembaruan Token

Implementasikan sistem token pembaruan untuk sesi yang panjang:

```php
Flight::route('POST /api/login', function() {
    // ... validasi kredensial ...

    $secretKey = Flight::get('config')['jwt_secret'];
    $refreshSecret = Flight::get('config')['jwt_refresh_secret'];
    
    // Token akses jangka pendek (15 menit)
    $accessToken = JWT::encode([
        'user_id' => $user->id,
        'type' => 'access',
        'iat' => time(),
        'exp' => time() + (15 * 60)
    ], $secretKey, 'HS256');
    
    // Token pembaruan jangka panjang (7 hari)
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
        
        // Verifikasi ini adalah token pembaruan
        if ($decoded->type !== 'refresh') {
            Flight::jsonHalt(['error' => 'Tipe token tidak valid'], 401);
        }
        
        // Hasilkan token akses baru
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
        Flight::jsonHalt(['error' => 'Token pembaruan tidak valid'], 401);
    }
});
```

### 3. Kontrol Akses Berdasarkan Peran

Perluas middleware Anda untuk memeriksa peran pengguna:

```php
class JwtRoleMiddleware {
    
    protected Engine $app;
    protected array $allowedRoles;
    
    public function __construct(Engine $app, array $allowedRoles = []) {
        $this->app = $app;
        $this->allowedRoles = $allowedRoles;
    }
    
    public function before(array $params) {
        // Asumsikan JwtMiddleware sudah berjalan dan menetapkan data pengguna
        $user = $this->app->request()->data->user ?? null;
        
        if (!$user) {
            $this->app->jsonHalt(['error' => 'Autentikasi diperlukan'], 401);
        }
        
        // Periksa apakah pengguna memiliki peran yang diperlukan
        if (!empty($this->allowedRoles) && !in_array($user->role, $this->allowedRoles)) {
            $this->app->jsonHalt(['error' => 'Izin tidak mencukupi'], 403);
        }
    }
}

// Penggunaan: Rute hanya untuk admin
Flight::route('DELETE /api/users/@id', function($id) {
    // Logika hapus pengguna
})->addMiddleware([
    JwtMiddleware::class,
    new JwtRoleMiddleware(Flight::app(), ['admin'])
]);
```

### 4. API Publik dengan Batasan Tingkat Berdasarkan Pengguna

Gunakan JWT untuk melacak dan membatasi tingkat pengguna tanpa sesi:

```php
class RateLimitMiddleware {
    
    public function before(array $params) {
        $user = Flight::request()->data->user ?? null;
        $userId = $user ? $user->user_id : Flight::request()->ip;
        
        $cacheKey = "rate_limit:$userId";
        // Pastikan Anda menyiapkan layanan cache di app/config/services.php
        $requests = Flight::cache()->get($cacheKey, 0);
        
        if ($requests >= 100) { // 100 permintaan per jam
            Flight::jsonHalt(['error' => 'Batas tingkat terlampaui'], 429);
        }
        
        Flight::cache()->set($cacheKey, $requests + 1, 3600);
    }
}
```

## Praktik Terbaik Keamanan

### 1. Gunakan Kunci Rahasia yang Kuat

```php
// Hasilkan kunci rahasia yang aman (jalankan sekali, simpan ke file .env)
$secretKey = base64_encode(random_bytes(32));
echo $secretKey; // Simpan ini di file .env Anda!
```

### 2. Simpan Rahasia di Variabel Lingkungan

```php
// Jangan pernah commit rahasia ke kontrol versi!
// Gunakan file .env dan library seperti vlucas/phpdotenv

// File .env:
// JWT_SECRET=your-base64-encoded-secret-here
// JWT_REFRESH_SECRET=another-base64-encoded-secret-here

// Anda juga dapat menggunakan file app/config/config.php untuk menyimpan rahasia Anda
// pastikan file konfigurasi tidak di-commit ke kontrol versi
// return [
//     'jwt_secret' => 'your-base64-encoded-secret-here',
//     'jwt_refresh_secret' => 'another-base64-encoded-secret-here',
// ];

// Di aplikasi Anda:
$secretKey = getenv('JWT_SECRET');
```

### 3. Tetapkan Waktu Kedaluwarsa yang Sesuai

```php
// Praktik baik: token akses jangka pendek
'exp' => time() + (15 * 60)  // 15 menit

// Untuk token pembaruan: kedaluwarsa lebih panjang
'exp' => time() + (7 * 24 * 60 * 60)  // 7 hari
```

### 4. Gunakan HTTPS di Produksi

JWT harus **selalu** dikirim melalui HTTPS. Jangan pernah kirim token melalui HTTP biasa di produksi!

### 5. Validasi Klaim Token

Selalu validasi klaim yang Anda pedulikan:

```php
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

// Periksa kedaluwarsa ditangani secara otomatis oleh library
// Tapi Anda dapat menambahkan validasi kustom:
if ($decoded->iat > time()) {
    throw new Exception('Token digunakan sebelum diterbitkan');
}

if (isset($decoded->nbf) && $decoded->nbf > time()) {
    throw new Exception('Token belum valid');
}
```

### 6. Pertimbangkan Daftar Hitam Token untuk Logout

Untuk keamanan ekstra, pertahankan daftar hitam token yang dibatalkan:

```php
Flight::route('POST /api/logout', function() {
    $authHeader = Flight::request()->getHeader('Authorization');
    preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
    $jwt = $matches[1];
    
    // Ekstrak kedaluwarsa token
    $decoded = Flight::request()->data->user;
    $ttl = $decoded->exp - time();
    
    // Simpan di cache/redis hingga kedaluwarsa
    Flight::cache()->set("blacklist:$jwt", true, $ttl);
    
    Flight::json(['message' => 'Berhasil logout']);
});

// Tambahkan ke JwtMiddleware Anda:
public function before(array $params) {
    // ... ekstrak JWT ...
    
    // Periksa daftar hitam
    if (Flight::cache()->get("blacklist:$jwt")) {
        $this->app->jsonHalt(['error' => 'Token telah dicabut'], 401);
    }
    
    // ... verifikasi token ...
}
```

## Algoritma dan Tipe Kunci

Firebase JWT mendukung beberapa algoritma:

### Algoritma Simetris (HMAC)
- **HS256** (Direkomendasikan untuk sebagian besar aplikasi): Menggunakan satu kunci rahasia
- **HS384**, **HS512**: Varian yang lebih kuat

```php
$jwt = JWT::encode($payload, $secretKey, 'HS256');
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Algoritma Asimetris (RSA/ECDSA)
- **RS256**, **RS384**, **RS512**: Menggunakan pasangan kunci publik/privat
- **ES256**, **ES384**, **ES512**: Varian kurva eliptik

```php
// Hasilkan kunci: openssl genrsa -out private.key 2048
// openssl rsa -in private.key -pubout -out public.key

$privateKey = file_get_contents('/path/to/private.key');
$publicKey = file_get_contents('/path/to/public.key');

// Enkode dengan kunci privat
$jwt = JWT::encode($payload, $privateKey, 'RS256');

// Dekode dengan kunci publik
$decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
```

> **Kapan menggunakan RSA**: Gunakan RSA ketika Anda perlu mendistribusikan kunci publik untuk verifikasi (misalnya, microservices, integrasi pihak ketiga). Untuk aplikasi tunggal, HS256 lebih sederhana dan cukup.

## Pemecahan Masalah

### Kesalahan "Token kedaluwarsa"
Klaim `exp` token Anda di masa lalu. Terbitkan token baru atau implementasikan pembaruan token.

### "Verifikasi tanda tangan gagal"
- Anda menggunakan kunci rahasia yang berbeda untuk dekode daripada yang digunakan untuk encode
- Token telah dimanipulasi
- Penyimpangan jam antara server (tambahkan buffer leeway)

```php
use Firebase\JWT\JWT;

JWT::$leeway = 60; // Izinkan 60 detik penyimpangan jam
$decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
```

### Token Tidak Dikirim dalam Permintaan
Pastikan klien Anda mengirim header `Authorization`:

```javascript
// Contoh JavaScript
fetch('/api/users', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

## Metode

Library Firebase JWT menyediakan metode inti ini:

- `JWT::encode(array $payload, string $key, string $alg)`: Membuat JWT dari payload
- `JWT::decode(string $jwt, Key $key)`: Mendekode dan memverifikasi JWT
- `JWT::urlsafeB64Encode(string $input)`: Encoding Base64 aman URL
- `JWT::urlsafeB64Decode(string $input)`: Decoding Base64 aman URL
- `JWT::$leeway`: Properti statis untuk menetapkan leeway waktu untuk validasi (dalam detik)

## Mengapa Menggunakan Library Ini?

- **Standar Industri**: Firebase JWT adalah library JWT paling populer dan tepercaya secara luas untuk PHP
- **Pemeliharaan Aktif**: Dipelihara oleh tim Google/Firebase
- **Fokus Keamanan**: Pembaruan rutin dan patch keamanan
- **API Sederhana**: Mudah dipahami dan diimplementasikan
- **Didokumentasikan dengan Baik**: Dokumentasi ekstensif dan dukungan komunitas
- **Fleksibel**: Mendukung beberapa algoritma dan opsi yang dapat dikonfigurasi

## Lihat Juga

- [Repositori Github Firebase JWT](https://github.com/firebase/php-jwt)
- [JWT.io](https://jwt.io/) - Debug dan dekode JWT
- [RFC 7519](https://tools.ietf.org/html/rfc7519) - Spesifikasi resmi JWT
- [Dokumentasi Middleware Flight](/learn/middleware)
- [Plugin Sesi Flight](/awesome-plugins/session) - Untuk autentikasi berbasis sesi tradisional

## Lisensi

Library Firebase JWT dilisensikan di bawah Lisensi BSD 3-Clause. Lihat [repositori Github](https://github.com/firebase/php-jwt) untuk detail.