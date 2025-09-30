# Manipulador de Arquivo Enviado

## Visão Geral

A classe `UploadedFile` no Flight facilita e torna seguro o manuseio de uploads de arquivos em sua aplicação. Ela encapsula os detalhes do processo de upload de arquivos do PHP, fornecendo uma maneira simples e orientada a objetos para acessar informações do arquivo e mover arquivos enviados.

## Compreendendo

Quando um usuário envia um arquivo via formulário, o PHP armazena informações sobre o arquivo na superglobal `$_FILES`. No Flight, você raramente interage diretamente com `$_FILES`. Em vez disso, o objeto `Request` do Flight (acessível via `Flight::request()`) fornece um método `getUploadedFiles()` que retorna um array de objetos `UploadedFile`, tornando o manuseio de arquivos muito mais conveniente e robusto.

A classe `UploadedFile` fornece métodos para:
- Obter o nome original do arquivo, tipo MIME, tamanho e localização temporária
- Verificar erros de upload
- Mover o arquivo enviado para uma localização permanente

Essa classe ajuda você a evitar armadilhas comuns com uploads de arquivos, como lidar com erros ou mover arquivos de forma segura.

## Uso Básico

### Acessando Arquivos Enviados de uma Requisição

A maneira recomendada de acessar arquivos enviados é através do objeto de requisição:

```php
Flight::route('POST /upload', function() {
    // Para um campo de formulário nomeado <input type="file" name="myFile">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    $file = $uploadedFiles['myFile'];

    // Agora você pode usar os métodos do UploadedFile
    if ($file->getError() === UPLOAD_ERR_OK) {
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
        echo "Arquivo enviado com sucesso!";
    } else {
        echo "Falha no envio: " . $file->getError();
    }
});
```

### Manipulando Múltiplos Uploads de Arquivos

Se o seu formulário usa `name="myFiles[]"` para múltiplos uploads, você obterá um array de objetos `UploadedFile`:

```php
Flight::route('POST /upload', function() {
    // Para um campo de formulário nomeado <input type="file" name="myFiles[]">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    foreach ($uploadedFiles['myFiles'] as $file) {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
            echo "Enviado: " . $file->getClientFilename() . "<br>";
        } else {
            echo "Falha no envio: " . $file->getClientFilename() . "<br>";
        }
    }
});
```

### Criando uma Instância de UploadedFile Manualmente

Normalmente, você não criará um `UploadedFile` manualmente, mas pode fazer isso se necessário:

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

### Acessando Informações do Arquivo

Você pode facilmente obter detalhes sobre o arquivo enviado:

```php
echo $file->getClientFilename();   // Nome original do arquivo do computador do usuário
echo $file->getClientMediaType();  // Tipo MIME (ex.: image/png)
echo $file->getSize();             // Tamanho do arquivo em bytes
echo $file->getTempName();         // Caminho temporário do arquivo no servidor
echo $file->getError();            // Código de erro de upload (0 significa sem erro)
```

### Movendo o Arquivo Enviado

Após validar o arquivo, mova-o para uma localização permanente:

```php
try {
  $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
  echo "Arquivo enviado com sucesso!";
} catch (Exception $e) {
  echo "Falha no envio: " . $e->getMessage();
}
```

O método `moveTo()` lançará uma exceção se algo der errado (como um erro de upload ou problema de permissão).

### Manipulando Erros de Upload

Se houver um problema durante o upload, você pode obter uma mensagem de erro legível por humanos:

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
  // Você pode usar o código de erro ou capturar a exceção de moveTo()
  echo "Houve um erro ao enviar o arquivo.";
}
```

## Veja Também

- [Requests](/learn/requests) - Aprenda como acessar arquivos enviados de requisições HTTP e veja mais exemplos de upload de arquivos.
- [Configuration](/learn/configuration) - Como configurar limites de upload e diretórios no PHP.
- [Extending](/learn/extending) - Como personalizar ou estender as classes principais do Flight.

## Solução de Problemas

- Sempre verifique `$file->getError()` antes de mover o arquivo.
- Certifique-se de que o diretório de upload é gravável pelo servidor web.
- Se `moveTo()` falhar, verifique a mensagem de exceção para detalhes.
- As configurações `upload_max_filesize` e `post_max_size` do PHP podem limitar uploads de arquivos.
- Para múltiplos uploads de arquivos, sempre itere pelo array de objetos `UploadedFile`.

## Changelog

- v3.12.0 - Adicionada a classe `UploadedFile` ao objeto de requisição para um manuseio de arquivos mais fácil.