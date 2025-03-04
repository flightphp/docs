# PHP 암호화

[defuse/php-encryption](https://github.com/defuse/php-encryption)은 데이터를 암호화하고 복호화하는 데 사용할 수 있는 라이브러리입니다. 시작하고 실행하는 것은 암호화하고 복호화하는 것이 상당히 간단합니다. 그들은 라이브러리 사용법과 암호화에 관한 중요한 보안 요소에 대해 설명하는 훌륭한 [튜토리얼](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md)이 있습니다.

## 설치

composer로 간단히 설치할 수 있습니다.

```bash
composer require defuse/php-encryption
```

## 설정

그런 다음 암호화 키를 생성해야 합니다.

```bash
vendor/bin/generate-defuse-key
```

이 과정에서 안전하게 보관해야 할 키가 표시됩니다. 이 키를 파일 하단에 있는 배열 안에 `app/config/config.php` 파일에 저장할 수 있습니다. 완벽한 위치는 아니지만 적어도 어딘가에 있습니다.

## 사용법

이제 라이브러리와 암호화 키가 준비되었으므로 데이터를 암호화하고 복호화할 수 있습니다.

```php
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * 부트스트랩 또는 public/index.php 파일에 설정
 */

// 암호화 메서드
Flight::map('encrypt', function($raw_data) {
	$encryption_key = /* $config['encryption_key'] 또는 키가 위치한 파일에 대한 file_get_contents */;
	return Crypto::encrypt($raw_data, Key::loadFromAsciiSafeString($encryption_key));
});

// 복호화 메서드
Flight::map('decrypt', function($encrypted_data) {
	$encryption_key = /* $config['encryption_key'] 또는 키가 위치한 파일에 대한 file_get_contents */;
	try {
		$raw_data = Crypto::decrypt($encrypted_data, Key::loadFromAsciiSafeString($encryption_key));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// 공격! 잘못된 키가 로드되었거나 암호문이 생성된 후 변경되었습니다. 데이터베이스에서 손상된 상태이거나 악의적으로 수정되어 있는 경우입니다.

		// ... 응용 프로그램에 적합한 방식으로 이 경우를 처리합니다 ...
	}
	return $raw_data;
});

Flight::route('/encrypt', function() {
	$encrypted_data = Flight::encrypt('비밀 정보입니다');
	echo $encrypted_data;
});

Flight::route('/decrypt', function() {
	$encrypted_data = '...'; // 어딘가에서 암호화된 데이터 가져오기
	$decrypted_data = Flight::decrypt($encrypted_data);
	echo $decrypted_data;
});
```