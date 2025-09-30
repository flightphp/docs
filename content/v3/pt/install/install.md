# Instruções de Instalação

Existem alguns pré-requisitos básicos antes de você poder instalar o Flight. Especificamente, você precisará de:

1. [Instalar PHP no seu sistema](#installing-php)
2. [Instalar Composer](https://getcomposer.org) para a melhor experiência de desenvolvimento.

## Instalação Básica

Se você estiver usando [Composer](https://getcomposer.org), você pode executar o seguinte
comando:

```bash
composer require flightphp/core
```

Isso colocará apenas os arquivos principais do Flight no seu sistema. Você precisará definir a estrutura do projeto, [layout](/learn/templates), [dependências](/learn/dependency-injection-container), [configurações](/learn/configuration), [carregamento automático](/learn/autoloading), etc. Esse método garante que nenhuma outra dependência além do Flight seja instalada.

Você também pode [baixar os arquivos](https://github.com/flightphp/core/archive/master.zip)
diretamente e extrai-los para o seu diretório web.

## Instalação Recomendada

É altamente recomendado começar com o aplicativo [flightphp/skeleton](https://github.com/flightphp/skeleton) para qualquer novo projeto. A instalação é muito simples.

```bash
composer create-project flightphp/skeleton my-project/
```

Isso configurará a estrutura do seu projeto, configurará o carregamento automático com namespaces, configurará uma configuração e fornecerá outras ferramentas como [Tracy](/awesome-plugins/tracy), [Extensões do Tracy](/awesome-plugins/tracy-extensions) e [Runway](/awesome-plugins/runway)

## Configurar seu Servidor Web

### Servidor de Desenvolvimento PHP Integrado

Essa é, de longe, a maneira mais simples de começar. Você pode usar o servidor integrado para executar sua aplicação e até usar SQLite para um banco de dados (desde que o sqlite3 esteja instalado no seu sistema) e não precisar de muito mais nada! Basta executar o seguinte comando uma vez que o PHP esteja instalado:

```bash
php -S localhost:8000
# ou com o aplicativo skeleton
composer start
```

Em seguida, abra seu navegador e vá para `http://localhost:8000`.

Se você quiser tornar o diretório raiz do documento do seu projeto um diretório diferente (Ex: seu projeto é `~/myproject`, mas seu diretório raiz do documento é `~/myproject/public/`), você pode executar o seguinte comando uma vez que estiver no diretório `~/myproject`:

```bash
php -S localhost:8000 -t public/
# com o aplicativo skeleton, isso já está configurado
composer start
```

Em seguida, abra seu navegador e vá para `http://localhost:8000`.

### Apache

Certifique-se de que o Apache já esteja instalado no seu sistema. Se não estiver, pesquise no Google como instalar o Apache no seu sistema.

Para o Apache, edite seu arquivo `.htaccess` com o seguinte:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Nota**: Se você precisar usar o flight em um subdiretório, adicione a linha
> `RewriteBase /subdir/` logo após `RewriteEngine On`.

> **Nota**: Se você quiser proteger todos os arquivos do servidor, como um arquivo de banco de dados ou env.
> Coloque isso no seu arquivo `.htaccess`:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Certifique-se de que o Nginx já esteja instalado no seu sistema. Se não estiver, pesquise no Google como instalar o Nginx no seu sistema.

Para o Nginx, adicione o seguinte à sua declaração de servidor:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Criar seu arquivo `index.php`

Se você estiver fazendo uma instalação básica, você precisará de algum código para começar.

```php
<?php

// Se você estiver usando Composer, exija o autoloader.
require 'vendor/autoload.php';
// se você não estiver usando Composer, carregue o framework diretamente
// require 'flight/Flight.php';

// Em seguida, defina uma rota e atribua uma função para lidar com a solicitação.
Flight::route('/', function () {
  echo 'hello world!';
});

// Finalmente, inicie o framework.
Flight::start();
```

Com o aplicativo skeleton, isso já está configurado e tratado no seu arquivo `app/config/routes.php`. Os serviços são configurados em `app/config/services.php`

## Instalando PHP

Se você já tiver o `php` instalado no seu sistema, vá em frente e pule essas instruções e vá para [a seção de download](#download-the-files)

### **macOS**

#### **Instalando PHP usando Homebrew**

1. **Instale o Homebrew** (se ainda não estiver instalado):
   - Abra o Terminal e execute:
     ```bash
     /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
     ```

2. **Instale o PHP**:
   - Instale a versão mais recente:
     ```bash
     brew install php
     ```
   - Para instalar uma versão específica, por exemplo, PHP 8.1:
     ```bash
     brew tap shivammathur/php
     brew install shivammathur/php/php@8.1
     ```

3. **Alterne entre versões do PHP**:
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

#### **Instalando PHP manualmente**

1. **Baixe o PHP**:
   - Visite [PHP for Windows](https://windows.php.net/download/) e baixe a versão mais recente ou uma versão específica (ex.: 7.4, 8.0) como um arquivo zip não thread-safe.

2. **Extraia o PHP**:
   - Extraia o arquivo zip baixado para `C:\php`.

3. **Adicione o PHP ao PATH do sistema**:
   - Vá para **Propriedades do Sistema** > **Variáveis de Ambiente**.
   - Em **Variáveis do sistema**, encontre **Path** e clique em **Editar**.
   - Adicione o caminho `C:\php` (ou onde você extraiu o PHP).
   - Clique em **OK** para fechar todas as janelas.

4. **Configure o PHP**:
   - Copie `php.ini-development` para `php.ini`.
   - Edite `php.ini` para configurar o PHP conforme necessário (ex.: definindo `extension_dir`, habilitando extensões).

5. **Verifique a instalação do PHP**:
   - Abra o Prompt de Comando e execute:
     ```cmd
     php -v
     ```

#### **Instalando Múltiplas Versões do PHP**

1. **Repita os passos acima** para cada versão, colocando cada uma em um diretório separado (ex.: `C:\php7`, `C:\php8`).

2. **Alterne entre versões** ajustando a variável PATH do sistema para apontar para o diretório da versão desejada.

### **Ubuntu (20.04, 22.04, etc.)**

#### **Instalando PHP usando apt**

1. **Atualize as listas de pacotes**:
   - Abra o Terminal e execute:
     ```bash
     sudo apt update
     ```

2. **Instale o PHP**:
   - Instale a versão mais recente do PHP:
     ```bash
     sudo apt install php
     ```
   - Para instalar uma versão específica, por exemplo, PHP 8.1:
     ```bash
     sudo apt install php8.1
     ```

3. **Instale módulos adicionais** (opcional):
   - Por exemplo, para instalar suporte ao MySQL:
     ```bash
     sudo apt install php8.1-mysql
     ```

4. **Alterne entre versões do PHP**:
   - Use `update-alternatives`:
     ```bash
     sudo update-alternatives --set php /usr/bin/php8.1
     ```

5. **Verifique a versão instalada**:
   - Execute:
     ```bash
     php -v
     ```

### **Rocky Linux**

#### **Instalando PHP usando yum/dnf**

1. **Habilite o repositório EPEL**:
   - Abra o Terminal e execute:
     ```bash
     sudo dnf install epel-release
     ```

2. **Instale o repositório Remi's**:
   - Execute:
     ```bash
     sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
     sudo dnf module reset php
     ```

3. **Instale o PHP**:
   - Para instalar a versão padrão:
     ```bash
     sudo dnf install php
     ```
   - Para instalar uma versão específica, por exemplo, PHP 7.4:
     ```bash
     sudo dnf module install php:remi-7.4
     ```

4. **Alterne entre versões do PHP**:
   - Use o comando de módulo `dnf`:
     ```bash
     sudo dnf module reset php
     sudo dnf module enable php:remi-8.0
     sudo dnf install php
     ```

5. **Verifique a versão instalada**:
   - Execute:
     ```bash
     php -v
     ```

### **Notas Gerais**

- Para ambientes de desenvolvimento, é importante configurar as configurações do PHP de acordo com os requisitos do seu projeto. 
- Ao alternar versões do PHP, certifique-se de que todas as extensões relevantes do PHP estejam instaladas para a versão específica que você pretende usar.
- Reinicie seu servidor web (Apache, Nginx, etc.) após alternar versões do PHP ou atualizar configurações para aplicar as alterações.