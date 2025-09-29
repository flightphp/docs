# Uploaded File Handler

## Overview

The `UploadedFile` class in Flight makes it easy and safe to handle file uploads in your application. It wraps the details of PHP's file upload process, giving you a simple, object-oriented way to access file information and move uploaded files.

## Understanding

When a user uploads a file via a form, PHP stores information about the file in the `$_FILES` superglobal. In Flight, you rarely interact with `$_FILES` directly. Instead, Flight's `Request` object (accessible via `Flight::request()`) provides a `getUploadedFiles()` method that returns an array of `UploadedFile` objects, making file handling much more convenient and robust.

The `UploadedFile` class provides methods to:
- Get the original filename, MIME type, size, and temporary location
- Check for upload errors
- Move the uploaded file to a permanent location

This class helps you avoid common pitfalls with file uploads, like handling errors or moving files securely.

## Basic Usage

### Accessing Uploaded Files from a Request

The recommended way to access uploaded files is through the request object:

```php
Flight::route('POST /upload', function() {
    // For a form field named <input type="file" name="myFile">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    $file = $uploadedFiles['myFile'];

    // Now you can use the UploadedFile methods
    if ($file->getError() === UPLOAD_ERR_OK) {
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
        echo "File uploaded successfully!";
    } else {
        echo "Upload failed: " . $file->getError();
    }
});
```

### Handling Multiple File Uploads

If your form uses `name="myFiles[]"` for multiple uploads, you'll get an array of `UploadedFile` objects:

```php
Flight::route('POST /upload', function() {
    // For a form field named <input type="file" name="myFiles[]">
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

### Creating an UploadedFile Instance Manually

Usually, you won't create an `UploadedFile` manually, but you can if needed:

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

### Accessing File Information

You can easily get details about the uploaded file:

```php
echo $file->getClientFilename();   // Original filename from the user's computer
echo $file->getClientMediaType();  // MIME type (e.g., image/png)
echo $file->getSize();             // File size in bytes
echo $file->getTempName();         // Temporary file path on the server
echo $file->getError();            // Upload error code (0 means no error)
```

### Moving the Uploaded File

After validating the file, move it to a permanent location:

```php
try {
  $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
  echo "File uploaded successfully!";
} catch (Exception $e) {
  echo "Upload failed: " . $e->getMessage();
}
```

The `moveTo()` method will throw an exception if something goes wrong (like an upload error or permission issue).

### Handling Upload Errors

If there was a problem during upload, you can get a human-readable error message:

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
  // You can use the error code or catch the exception from moveTo()
  echo "There was an error uploading the file.";
}
```

## See Also

- [Requests](/learn/requests) - Learn how to access uploaded files from HTTP requests and see more file upload examples.
- [Configuration](/learn/configuration) - How to configure upload limits and directories in PHP.
- [Extending](/learn/extending) - How to customize or extend Flight's core classes.

## Troubleshooting

- Always check `$file->getError()` before moving the file.
- Make sure your upload directory is writable by the web server.
- If `moveTo()` fails, check the exception message for details.
- PHP's `upload_max_filesize` and `post_max_size` settings can limit file uploads.
- For multiple file uploads, always loop through the array of `UploadedFile` objects.

## Changelog

- v3.12.0 - Added `UploadedFile` class to the request object for easier file handling.

