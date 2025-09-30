# 上传文件处理器

## 概述

Flight 中的 `UploadedFile` 类使处理应用程序中的文件上传变得简单且安全。它封装了 PHP 文件上传过程的细节，为您提供一种简单、面向对象的方式来访问文件信息并移动上传的文件。

## 理解

当用户通过表单上传文件时，PHP 将文件信息存储在 `$_FILES` 超级全局变量中。在 Flight 中，您很少直接与 `$_FILES` 交互。相反，Flight 的 `Request` 对象（通过 `Flight::request()` 访问）提供了一个 `getUploadedFiles()` 方法，该方法返回一个 `UploadedFile` 对象的数组，使文件处理更加方便和健壮。

`UploadedFile` 类提供了以下方法：
- 获取原始文件名、MIME 类型、大小和临时位置
- 检查上传错误
- 将上传的文件移动到永久位置

此类帮助您避免文件上传的常见陷阱，例如处理错误或安全地移动文件。

## 基本用法

### 从请求中访问上传的文件

访问上传文件的最推荐方式是通过请求对象：

```php
Flight::route('POST /upload', function() {
    // 对于名为 <input type="file" name="myFile"> 的表单字段
    $uploadedFiles = Flight::request()->getUploadedFiles();
    $file = $uploadedFiles['myFile'];

    // 现在您可以使用 UploadedFile 方法
    if ($file->getError() === UPLOAD_ERR_OK) {
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
        echo "File uploaded successfully!";
    } else {
        echo "Upload failed: " . $file->getError();
    }
});
```

### 处理多个文件上传

如果您的表单使用 `name="myFiles[]"` 进行多个上传，您将获得一个 `UploadedFile` 对象的数组：

```php
Flight::route('POST /upload', function() {
    // 对于名为 <input type="file" name="myFiles[]"> 的表单字段
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

### 手动创建 UploadedFile 实例

通常，您不会手动创建 `UploadedFile`，但如果需要，可以这样做：

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

### 访问文件信息

您可以轻松获取上传文件的详细信息：

```php
echo $file->getClientFilename();   // 用户计算机上的原始文件名
echo $file->getClientMediaType();  // MIME 类型（例如，image/png）
echo $file->getSize();             // 文件大小（以字节为单位）
echo $file->getTempName();         // 服务器上的临时文件路径
echo $file->getError();            // 上传错误代码（0 表示无错误）
```

### 移动上传的文件

验证文件后，将其移动到永久位置：

```php
try {
  $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
  echo "File uploaded successfully!";
} catch (Exception $e) {
  echo "Upload failed: " . $e->getMessage();
}
```

`moveTo()` 方法如果出现问题（例如上传错误或权限问题）将抛出异常。

### 处理上传错误

如果上传过程中出现问题，您可以获取人类可读的错误消息：

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
  // 您可以使用错误代码或捕获 moveTo() 的异常
  echo "There was an error uploading the file.";
}
```

## 另请参阅

- [Requests](/learn/requests) - 了解如何从 HTTP 请求中访问上传的文件，并查看更多文件上传示例。
- [Configuration](/learn/configuration) - 如何在 PHP 中配置上传限制和目录。
- [Extending](/learn/extending) - 如何自定义或扩展 Flight 的核心类。

## 故障排除

- 在移动文件之前始终检查 `$file->getError()`。
- 确保您的上传目录可由 Web 服务器写入。
- 如果 `moveTo()` 失败，请检查异常消息以获取详细信息。
- PHP 的 `upload_max_filesize` 和 `post_max_size` 设置可能会限制文件上传。
- 对于多个文件上传，始终循环遍历 `UploadedFile` 对象的数组。

## 更新日志

- v3.12.0 - 将 `UploadedFile` 类添加到请求对象中，以简化文件处理。