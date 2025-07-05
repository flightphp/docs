# Integrasi WordPress: n0nag0n/wordpress-integration-for-flight-framework

Ingin menggunakan Flight PHP di dalam situs WordPress Anda? Plugin ini membuatnya sangat mudah! Dengan `n0nag0n/wordpress-integration-for-flight-framework`, Anda dapat menjalankan aplikasi Flight penuh di samping instalasi WordPress Anda—sempurna untuk membangun API khusus, microservices, atau bahkan aplikasi lengkap tanpa meninggalkan kenyamanan WordPress.

---

## Apa yang Dilakukannya?

- **Mengintegrasikan Flight PHP dengan WordPress tanpa hambatan**
- Arahkan permintaan ke Flight atau WordPress berdasarkan pola URL
- Organisasi kode Anda dengan controllers, models, dan views (MVC)
- Mudah menyiapkan struktur folder Flight yang direkomendasikan
- Gunakan koneksi database WordPress atau milik Anda sendiri
- Sesuaikan bagaimana Flight dan WordPress berinteraksi
- Antarmuka admin sederhana untuk konfigurasi

## Instalasi

1. Unggah folder `flight-integration` ke direktori `/wp-content/plugins/` Anda.
2. Aktifkan plugin di admin WordPress (menu Plugins).
3. Buka **Settings > Flight Framework** untuk mengonfigurasi plugin.
4. Atur jalur vendor ke instalasi Flight Anda (atau gunakan Composer untuk menginstal Flight).
5. Konfigurasi jalur folder app Anda dan buat struktur folder (plugin dapat membantu dengan ini!).
6. Mulai bangun aplikasi Flight Anda!

## Contoh Penggunaan

### Contoh Rute Dasar
Di file `app/config/routes.php` Anda:

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### Contoh Controller

Buat controller di `app/controllers/ApiController.php`:

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // Anda dapat menggunakan fungsi WordPress di dalam Flight!
        $users = get_users();
        $result = [];
        foreach($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            ];
        }
        Flight::json($result);
    }
}
```

Kemudian, di `routes.php` Anda:

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## FAQ

**T: Apakah saya perlu mengetahui Flight untuk menggunakan plugin ini?**  
J: Ya, ini untuk pengembang yang ingin menggunakan Flight dalam WordPress. Pengetahuan dasar tentang routing dan penanganan permintaan Flight direkomendasikan.

**T: Apakah ini akan memperlambat situs WordPress saya?**  
J: Tidak! Plugin hanya memproses permintaan yang sesuai dengan rute Flight. Semua permintaan lainnya akan ke WordPress seperti biasa.

**T: Bisakah saya menggunakan fungsi WordPress di aplikasi Flight saya?**  
J: Tentu saja! Anda memiliki akses penuh ke semua fungsi WordPress, hooks, dan globals dari dalam rute dan controllers Flight.

**T: Bagaimana cara membuat rute khusus?**  
J: Tentukan rute Anda di file `config/routes.php` di folder app Anda. Lihat file sampel yang dibuat oleh generator struktur folder untuk contoh.

## Changelog

**1.0.0**  
Rilis awal.

---

Untuk informasi lebih lanjut, periksa [repo GitHub](https://github.com/n0nag0n/wordpress-integration-for-flight-framework).