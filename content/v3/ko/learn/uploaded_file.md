# 업로드된 파일 핸들러

## 개요

Flight의 `UploadedFile` 클래스는 애플리케이션에서 파일 업로드를 쉽게 그리고 안전하게 처리할 수 있게 합니다. 이는 PHP의 파일 업로드 프로세스 세부 사항을 래핑하여 파일 정보를 액세스하고 업로드된 파일을 이동하는 간단한 객체 지향 방식을 제공합니다.

## 이해

사용자가 폼을 통해 파일을 업로드하면 PHP는 파일에 대한 정보를 `$_FILES` 슈퍼글로벌에 저장합니다. Flight에서는 `$_FILES`와 직접 상호작용하는 경우가 거의 없습니다. 대신 Flight의 `Request` 객체( `Flight::request()`를 통해 액세스 가능)에 `getUploadedFiles()` 메서드가 있으며, 이는 `UploadedFile` 객체 배열을 반환하여 파일 처리를 훨씬 더 편리하고 견고하게 만듭니다.

`UploadedFile` 클래스는 다음을 수행하는 메서드를 제공합니다:
- 원본 파일 이름, MIME 유형, 크기 및 임시 위치 가져오기
- 업로드 오류 확인
- 업로드된 파일을 영구 위치로 이동

이 클래스는 파일 업로드의 일반적인 함정을 피하는 데 도움을 주며, 오류 처리나 파일을 안전하게 이동하는 등의 작업을 포함합니다.

## 기본 사용법

### 요청에서 업로드된 파일 액세스

업로드된 파일에 액세스하는 권장 방법은 요청 객체를 통하는 것입니다:

```php
Flight::route('POST /upload', function() {
    // <input type="file" name="myFile">라는 폼 필드에 대한 경우
    $uploadedFiles = Flight::request()->getUploadedFiles();
    $file = $uploadedFiles['myFile'];

    // 이제 UploadedFile 메서드를 사용할 수 있습니다
    if ($file->getError() === UPLOAD_ERR_OK) {
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
        echo "File uploaded successfully!";
    } else {
        echo "Upload failed: " . $file->getError();
    }
});
```

### 여러 파일 업로드 처리

폼이 여러 업로드를 위해 `name="myFiles[]"`를 사용하는 경우 `UploadedFile` 객체 배열을 받게 됩니다:

```php
Flight::route('POST /upload', function() {
    // <input type="file" name="myFiles[]">라는 폼 필드에 대한 경우
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

### UploadedFile 인스턴스 수동 생성

보통 `UploadedFile`을 수동으로 생성하지 않지만, 필요하다면 할 수 있습니다:

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

### 파일 정보 액세스

업로드된 파일에 대한 세부 정보를 쉽게 가져올 수 있습니다:

```php
echo $file->getClientFilename();   // 사용자의 컴퓨터에서 원본 파일 이름
echo $file->getClientMediaType();  // MIME 유형 (예: image/png)
echo $file->getSize();             // 바이트 단위 파일 크기
echo $file->getTempName();         // 서버의 임시 파일 경로
echo $file->getError();            // 업로드 오류 코드 (0은 오류 없음)
```

### 업로드된 파일 이동

파일을 검증한 후 영구 위치로 이동하세요:

```php
try {
  $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
  echo "File uploaded successfully!";
} catch (Exception $e) {
  echo "Upload failed: " . $e->getMessage();
}
```

`moveTo()` 메서드는 문제가 발생하면 예외를 발생시킵니다 (예: 업로드 오류나 권한 문제).

### 업로드 오류 처리

업로드 중 문제가 발생하면 읽기 쉬운 오류 메시지를 가져올 수 있습니다:

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
  // 오류 코드를 사용하거나 moveTo()에서 예외를 잡을 수 있습니다
  echo "There was an error uploading the file.";
}
```

## 관련 항목

- [Requests](/learn/requests) - HTTP 요청에서 업로드된 파일에 액세스하는 방법과 더 많은 파일 업로드 예제를 알아보세요.
- [Configuration](/learn/configuration) - PHP에서 업로드 제한 및 디렉토리를 구성하는 방법.
- [Extending](/learn/extending) - Flight의 핵심 클래스를 사용자 지정하거나 확장하는 방법.

## 문제 해결

- 파일을 이동하기 전에 항상 `$file->getError()`를 확인하세요.
- 업로드 디렉토리가 웹 서버에 의해 쓰기 가능하도록 하세요.
- `moveTo()`가 실패하면 예외 메시지를 확인하여 세부 정보를 확인하세요.
- PHP의 `upload_max_filesize` 및 `post_max_size` 설정이 파일 업로드를 제한할 수 있습니다.
- 여러 파일 업로드의 경우 항상 `UploadedFile` 객체 배열을 반복하세요.

## 변경 로그

- v3.12.0 - 파일 처리를 더 쉽게 하기 위해 요청 객체에 `UploadedFile` 클래스 추가.