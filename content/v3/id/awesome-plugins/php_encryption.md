# Enkripsi PHP

[defuse/php-encryption](https://github.com/defuse/php-encryption) adalah perpustakaan yang dapat digunakan untuk mengenkripsi dan mendekripsi data. Memulai dan menjalankan cukup sederhana untuk mulai mengenkripsi dan mendekripsi data. Mereka memiliki [tutorial](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md) yang sangat membantu menjelaskan dasar-dasar cara menggunakan perpustakaan serta implikasi keamanan penting terkait enkripsi.

## Instalasi

Instalasi sangat sederhana dengan composer.

```bash
composer require defuse/php-encryption
```

## Pengaturan

Kemudian Anda perlu menghasilkan kunci enkripsi.

```bash
vendor/bin/generate-defuse-key
```

 Ini akan menghasilkan kunci yang perlu Anda simpan dengan aman. Anda bisa menyimpan kunci di file `app/config/config.php` Anda di array di bagian bawah file. Meskipun itu bukan tempat yang sempurna, paling tidak itu adalah sesuatu.

## Penggunaan

Sekarang Anda memiliki perpustakaan dan kunci enkripsi, Anda dapat mulai mengenkripsi dan mendekripsi data.

```php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * Tetapkan di file bootstrap atau public/index.php Anda
 */

// Metode enkripsi
Flight::map('encrypt', function($raw_data) {
	$encryption_key = /* $config['encryption_key'] atau file_get_contents tempat Anda meletakkan kunci */;
	return Crypto::encrypt($raw_data, Key::loadFromAsciiSafeString($encryption_key));
});

// Metode dekripsi
Flight::map('decrypt', function($encrypted_data) {
	$encryption_key = /* $config['encryption_key'] atau file_get_contents tempat Anda meletakkan kunci */;
	try {
		$raw_data = Crypto::decrypt($encrypted_data, Key::loadFromAsciiSafeString($encryption_key));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// Sebuah serangan! Entah kunci yang salah dimuat, atau ciphertext telah
		// berubah sejak dibuat -- baik rusak di database atau
		// sengaja dimodifikasi oleh Eve yang mencoba melakukan serangan.

		// ... tangani kasus ini dengan cara yang sesuai untuk aplikasi Anda ...
	}
	return $raw_data;
});

Flight::route('/encrypt', function() {
	$encrypted_data = Flight::encrypt('Ini adalah rahasia');
	echo $encrypted_data;
});

Flight::route('/decrypt', function() {
	$encrypted_data = '...'; // Ambil data terenkripsi dari suatu tempat
	$decrypted_data = Flight::decrypt($encrypted_data);
	echo $decrypted_data;
});
```