# Cifrado en PHP

[defuse/php-encryption](https://github.com/defuse/php-encryption) es una biblioteca que se puede utilizar para cifrar y descifrar datos. Ponerse en marcha es bastante simple para comenzar a cifrar y descifrar datos. Tienen un excelente [tutorial](https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md) que ayuda a explicar los conceptos básicos sobre cómo utilizar la biblioteca, así como las importantes implicaciones de seguridad relacionadas con el cifrado.

## Instalación

La instalación es sencilla con composer.

```bash
composer require defuse/php-encryption
```

## Configuración

Luego necesitarás generar una clave de cifrado.

```bash
vendor/bin/generate-defuse-key
```

Esto generará una clave que deberás mantener segura. Podrías guardar la clave en tu archivo `app/config/config.php` en el array al final del archivo. Aunque no es el lugar perfecto, al menos es algo.

## Uso

Ahora que tienes la biblioteca y una clave de cifrado, puedes empezar a cifrar y descifrar datos.

```php
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/*
 * Establecerlo en tu archivo de inicio (bootstrap) o public/index.php
 */

// Método de cifrado
Flight::map('encrypt', function($datos_crudos) {
	$clave_cifrado = /* $config['clave_cifrado'] o un file_get_contents de dónde pusiste la clave */;
	return Crypto::encrypt($datos_crudos, Key::loadFromAsciiSafeString($clave_cifrado));
});

// Método de descifrado
Flight::map('decrypt', function($datos_cifrados) {
	$clave_cifrado = /* $config['clave_cifrado'] o un file_get_contents de dónde pusiste la clave */;
	try {
		$datos_crudos = Crypto::decrypt($datos_cifrados, Key::loadFromAsciiSafeString($clave_cifrado));
	} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		// ¡Un ataque! Se cargó la clave incorrecta o el texto cifrado
		// ha cambiado desde que fue creado, ya sea corrompido en la base de datos o modificado intencionalmente por Eve tratando de llevar a cabo un ataque.

		// ... manejar este caso de una manera adecuada para tu aplicación ...
	}
	return $datos_crudos;
});

Flight::route('/cifrar', function() {
	$datos_cifrados = Flight::encrypt('Esto es un secreto');
	echo $datos_cifrados;
});

Flight::route('/descifrar', function() {
	$datos_cifrados = '...'; // Obtener los datos cifrados de algún lugar
	$datos_descifrados = Flight::decrypt($datos_cifrados);
	echo $datos_descifrados;
});
```