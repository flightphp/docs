# Instalação

## Baixar os arquivos

Certifique-se de ter o PHP instalado em seu sistema. Caso contrário, clique [aqui](#installing-php) para obter instruções sobre como instalá-lo em seu sistema.

Se estiver usando [Composer](https://getcomposer.org), você pode executar o seguinte comando:

```bash
composer require flightphp/core
```

OU você pode [baixar os arquivos](https://github.com/flightphp/core/archive/master.zip) diretamente e extrair para o diretório da web.

## Configurar seu Servidor Web

### Servidor de Desenvolvimento PHP Integrado

Esta é de longe a maneira mais simples de começar. Você pode usar o servidor integrado para executar sua aplicação e até mesmo usar SQLite para um banco de dados (desde que o sqlite3 esteja instalado em seu sistema) e não exigir muito! Basta executar o seguinte comando uma vez que o PHP estiver instalado:

```bash
php -S localhost:8000
```

Em seguida, abra seu navegador e vá para `http://localhost:8000`.

Se quiser definir o diretório raiz do seu projeto como um diretório diferente (Por ex: seu projeto é `~/myproject`, mas sua raiz do documento é `~/myproject/public/`), você pode executar o seguinte comando uma vez que estiver no diretório `~/myproject`:

```bash
php -S localhost:8000 -t public/
```

Então, abra seu navegador e vá para `http://localhost:8000`.

### Apache

Certifique-se de que o Apache já esteja instalado em seu sistema. Caso contrário, pesquise como instalar o Apache em seu sistema.

Para o Apache, edite seu arquivo `.htaccess` com o seguinte:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Nota**: Se precisar usar o flight em um subdiretório, adicione a linha
> `RewriteBase /subdir/` logo após `RewriteEngine On`.

> **Nota**: Se desejar proteger todos os arquivos do servidor, como um arquivo db ou env.
> Coloque isso em seu arquivo `.htaccess`:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Certifique-se de que o Nginx já esteja instalado em seu sistema. Caso contrário, pesquise como instalar o Nginx em seu sistema.

Para o Nginx, adicione o seguinte à declaração do seu servidor:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Crie seu arquivo `index.php`

```php
<?php

// Se estiver usando o Composer, requer o autoloader.
require 'vendor/autoload.php';
// se não estiver usando o Composer, carregue o framework diretamente
// require 'flight/Flight.php';

// Em seguida, defina uma rota e atribua uma função para lidar com a solicitação.
Flight::route('/', function () {
  echo 'olá mundo!';
});

// Por fim, inicie o framework.
Flight::start();
```

## Instalando o PHP

Se você já tem o `php` instalado em seu sistema, vá em frente e pule estas instruções e vá para [a seção de download](#download-the-files)

Claro! Aqui estão as instruções para instalar o PHP no macOS, Windows 10/11, Ubuntu e Rocky Linux. Também incluirei detalhes sobre como instalar diferentes versões do PHP.

### **macOS**

#### **Instalando o PHP usando o Homebrew**

1. **Instalar o Homebrew** (caso ainda não esteja instalado):
   - Abra o Terminal e execute:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Instalar o PHP**:
   - Instalar a versão mais recente:
     ```bash
     brew install php
     ```
   - Para instalar uma versão específica, por exemplo, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Alternar entre versões do PHP**:
   - Desvincule a versão atual e vincule a versão desejada:
     ```bash
     brew unlink php
     brew link --overwrite --force php@8.1
     ```
   - Verifique a versão instalada:
     ```bash
     php -v
     ```

### **Windows 10/11**

#### **Instalando o PHP manualmente**

1. **Baixar o PHP**:
   - Visite [PHP for Windows](https://windows.php.net/download/) e baixe a versão mais recente ou uma versão específica (por ex., 7.4, 8.0) como um arquivo zip não seguro para threads.

2. **Extrair o PHP**:
   - Extraia o arquivo zip baixado para `C:\php`.

3. **Adicionar o PHP ao PATH do sistema**:
   - Vá para **Propriedades do Sistema** > **Variáveis de Ambiente**.
   - Em **Variáveis do Sistema**, encontre **Path** e clique em **Editar**.
   - Adicione o caminho `C:\php` (ou onde você extraiu o PHP).
   - Clique em **OK** para fechar todas as janelas.

4. **Configurar o PHP**:
   - Copie `php.ini-development` para `php.ini`.
   - Edite `php.ini` para configurar o PHP conforme necessário (por ex., definindo `extension_dir`, habilitando extensões).

5. **Verificar a instalação do PHP**:
   - Abra o Prompt de Comando e execute:
     ```cmd
     php -v
     ```

#### **Instalando Múltiplas Versões do PHP**

1. **Repita os passos acima** para cada versão, colocando cada uma em um diretório separado (por ex., `C:\php7`, `C:\php8`).

2. **Alternar entre as versões** ajustando a variável PATH do sistema para apontar para o diretório da versão desejada.

### **Ubuntu (20.04, 22.04, etc.)**

#### **Instalando o PHP usando apt**

1. **Atualizar listas de pacotes**:
   - Abra o Terminal e execute:
     ```bash
     sudo apt update
     ```

2. **Instalar o PHP**:
   - Instalar a versão mais recente do PHP:
     ```bash
     sudo apt install php
     ```
   - Para instalar uma versão específica, por exemplo, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Instalar módulos adicionais** (opcional):
   - Por exemplo, para instalar suporte ao MySQL:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Alternar entre as versões do PHP**:
   - Usar `update-alternatives`:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Verificar a versão instalada**:
   - Execute:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **Instalando o PHP usando yum/dnf**

1. **Ativar o repositório EPEL**:
   - Abra o Terminal e execute:
     ```bash
     sudo dnf install epel-release
     ```

2. **Instalar o repositório do Remi**:
   - Execute:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **Instalar o PHP**:
   - Para instalar a versão padrão:
     ```bash
     sudo dnf install php
     ```
   - Para instalar uma versão específica, por exemplo, PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Alternar entre as versões do PHP**:
   - Usar o comando de módulo `dnf`:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Verificar a versão instalada**:
   - Execute:
     ```bash
     php -v
     ```

### **Notas Gerais**

- Para ambientes de desenvolvimento, é importante configurar as configurações do PHP conforme os requisitos do seu projeto.
- Ao alternar entre as versões do PHP, assegure-se de que todas as extensões relevantes do PHP estejam instaladas para a versão específica que pretende usar.
- Reinicie seu servidor web (Apache, Nginx, etc.) após alternar entre as versões do PHP ou atualizar as configurações para aplicar as alterações.