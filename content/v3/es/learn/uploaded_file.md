# Manejador de Archivos Subidos

## Resumen

La clase `UploadedFile` en Flight facilita y asegura el manejo de subidas de archivos en su aplicación. Envuelve los detalles del proceso de subida de archivos de PHP, proporcionando una forma simple y orientada a objetos para acceder a la información de los archivos y mover los archivos subidos.

## Comprensión

Cuando un usuario sube un archivo a través de un formulario, PHP almacena la información sobre el archivo en el superglobal `$_FILES`. En Flight, rara vez interactúa directamente con `$_FILES`. En su lugar, el objeto `Request` de Flight (accesible a través de `Flight::request()`) proporciona un método `getUploadedFiles()` que devuelve un array de objetos `UploadedFile`, haciendo que el manejo de archivos sea mucho más conveniente y robusto.

La clase `UploadedFile` proporciona métodos para:
- Obtener el nombre original del archivo, el tipo MIME, el tamaño y la ubicación temporal
- Verificar errores de subida
- Mover el archivo subido a una ubicación permanente

Esta clase le ayuda a evitar errores comunes con las subidas de archivos, como el manejo de errores o la movimiento de archivos de manera segura.

## Uso Básico

### Acceso a Archivos Subidos desde una Solicitud

La forma recomendada de acceder a los archivos subidos es a través del objeto de solicitud:

```php
Flight::route('POST /upload', function() {
    // Para un campo de formulario llamado <input type="file" name="myFile">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    $file = $uploadedFiles['myFile'];

    // Ahora puede usar los métodos de UploadedFile
    if ($file->getError() === UPLOAD_ERR_OK) {
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
        echo "File uploaded successfully!";
    } else {
        echo "Upload failed: " . $file->getError();
    }
});
```

### Manejo de Subidas Múltiples de Archivos

Si su formulario usa `name="myFiles[]"` para subidas múltiples, obtendrá un array de objetos `UploadedFile`:

```php
Flight::route('POST /upload', function() {
    // Para un campo de formulario llamado <input type="file" name="myFiles[]">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    foreach ($uploadedFiles['myFiles'] as $file) {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
            echo "Uploaded: " . $file->getClientFilename() . "<br>";
        } else {
            echo "Failed to upload: " . $file->getClientFilename() . "<br>";
        }
    }
});
```

### Creación Manual de una Instancia de UploadedFile

Normalmente, no creará un `UploadedFile` manualmente, pero puede hacerlo si es necesario:

```php
use flight\net\UploadedFile;

$file = new UploadedFile(
  $_FILES['myfile']['name'],
  $_FILES['myfile']['type'],
  $_FILES['myfile']['size'],
  $_FILES['myfile']['tmp_name'],
  $_FILES['myfile']['error']
);
```

### Acceso a la Información del Archivo

Puede obtener fácilmente detalles sobre el archivo subido:

```php
echo $file->getClientFilename();   // Nombre original del archivo desde la computadora del usuario
echo $file->getClientMediaType();  // Tipo MIME (por ejemplo, image/png)
echo $file->getSize();             // Tamaño del archivo en bytes
echo $file->getTempName();         // Ruta temporal del archivo en el servidor
echo $file->getError();            // Código de error de subida (0 significa sin error)
```

### Mover el Archivo Subido

Después de validar el archivo, muévelo a una ubicación permanente:

```php
try {
  $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
  echo "File uploaded successfully!";
} catch (Exception $e) {
  echo "Upload failed: " . $e->getMessage();
}
```

El método `moveTo()` lanzará una excepción si algo sale mal (como un error de subida o un problema de permisos).

### Manejo de Errores de Subida

Si hubo un problema durante la subida, puede obtener un mensaje de error legible por humanos:

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
  // Puede usar el código de error o capturar la excepción de moveTo()
  echo "There was an error uploading the file.";
}
```

## Ver También

- [Requests](/learn/requests) - Aprenda cómo acceder a archivos subidos desde solicitudes HTTP y vea más ejemplos de subida de archivos.
- [Configuration](/learn/configuration) - Cómo configurar límites de subida y directorios en PHP.
- [Extending](/learn/extending) - Cómo personalizar o extender las clases principales de Flight.

## Solución de Problemas

- Siempre verifique `$file->getError()` antes de mover el archivo.
- Asegúrese de que su directorio de subida sea escribible por el servidor web.
- Si `moveTo()` falla, verifique el mensaje de excepción para obtener detalles.
- Las configuraciones de PHP `upload_max_filesize` y `post_max_size` pueden limitar las subidas de archivos.
- Para subidas múltiples de archivos, siempre itere a través del array de objetos `UploadedFile`.

## Registro de Cambios

- v3.12.0 - Se agregó la clase `UploadedFile` al objeto de solicitud para un manejo de archivos más fácil.