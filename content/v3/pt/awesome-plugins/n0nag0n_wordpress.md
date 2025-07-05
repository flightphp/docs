# Integração com WordPress: n0nag0n/wordpress-integration-for-flight-framework

Quer usar o Flight PHP dentro do seu site WordPress? Este plugin torna isso muito fácil! Com `n0nag0n/wordpress-integration-for-flight-framework`, você pode executar um aplicativo Flight completo ao lado da sua instalação WordPress — perfeito para criar APIs personalizadas, microservices ou até aplicativos completos sem sair do conforto do WordPress.

---

## O que Ele Faz?

- **Integra o Flight PHP ao WordPress de forma perfeita**
- Roteia solicitações para o Flight ou WordPress com base em padrões de URL
- Organize seu código com controladores, modelos e visualizações (MVC)
- Configure facilmente a estrutura de pastas recomendada do Flight
- Use a conexão de banco de dados do WordPress ou a sua própria
- Ajuste como o Flight e o WordPress interagem
- Interface de administração simples para configuração

## Instalação

1. Faça o upload da pasta `flight-integration` para o diretório `/wp-content/plugins/`.
2. Ative o plugin no painel do WordPress (menu Plugins).
3. Vá para **Configurações > Flight Framework** para configurar o plugin.
4. Defina o caminho do vendor para a instalação do Flight (ou use o Composer para instalar o Flight).
5. Configure o caminho da pasta do seu app e crie a estrutura de pastas (o plugin pode ajudar com isso!).
6. Comece a construir seu aplicativo Flight!

## Exemplos de Uso

### Exemplo Básico de Rota
No seu arquivo `app/config/routes.php`:

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### Exemplo de Controlador

Crie um controlador em `app/controllers/ApiController.php`:

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // Você pode usar funções do WordPress dentro do Flight!
        $users = get_users();
        $result = [];
        foreach($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            ];
        }
        Flight::json($result);
    }
}
```

Em seguida, no seu `routes.php`:

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## FAQ

**P: Preciso conhecer o Flight para usar este plugin?**  
R: Sim, isso é para desenvolvedores que desejam usar o Flight dentro do WordPress. Conhecimento básico sobre roteamento e manipulação de solicitações do Flight é recomendado.

**P: Isso vai deixar meu site WordPress mais lento?**  
R: Não! O plugin processa apenas as solicitações que correspondem às rotas do Flight. Todas as outras solicitações vão para o WordPress como de costume.

**P: Posso usar funções do WordPress no meu app Flight?**  
R: Absolutamente! Você tem acesso total a todas as funções, hooks e variáveis globais do WordPress dentro das rotas e controladores do Flight.

**P: Como crio rotas personalizadas?**  
R: Defina suas rotas no arquivo `config/routes.php` na pasta do seu app. Consulte o arquivo de exemplo criado pelo gerador de estrutura de pastas para exemplos.

## Registro de Alterações

**1.0.0**  
Lançamento inicial.

---

Para mais informações, consulte o [GitHub repo](https://github.com/n0nag0n/wordpress-integration-for-flight-framework).