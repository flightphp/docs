# Intégration WordPress : n0nag0n/wordpress-integration-for-flight-framework

Vous souhaitez utiliser Flight PHP dans votre site WordPress ? Ce plugin rend cela très simple ! Avec `n0nag0n/wordpress-integration-for-flight-framework`, vous pouvez exécuter une application Flight complète directement aux côtés de votre installation WordPress—parfait pour créer des API personnalisées, des microservices ou même des applications complètes sans quitter l'environnement WordPress.

---

## Que fait-il ?

- **Intègre sans effort Flight PHP avec WordPress**
- Redirige les requêtes vers Flight ou WordPress en fonction des motifs d'URL
- Organisez votre code avec des contrôleurs, des modèles et des vues (MVC)
- Configurez facilement la structure de dossiers recommandée pour Flight
- Utilisez la connexion de base de données de WordPress ou la vôtre
- Ajustez finement l'interaction entre Flight et WordPress
- Interface d'administration simple pour la configuration

## Installation

1. Téléchargez le dossier `flight-integration` dans votre répertoire `/wp-content/plugins/`.
2. Activez le plugin dans l'administration WordPress (menu Plugins).
3. Allez dans **Paramètres > Flight Framework** pour configurer le plugin.
4. Indiquez le chemin du vendeur vers votre installation Flight (ou utilisez Composer pour installer Flight).
5. Configurez le chemin de votre dossier d'application et créez la structure de dossiers (le plugin peut vous aider à cela !).
6. Commencez à construire votre application Flight !

## Exemples d'utilisation

### Exemple de route basique
Dans votre fichier `app/config/routes.php` :

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### Exemple de contrôleur

Créez un contrôleur dans `app/controllers/ApiController.php` :

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // Vous pouvez utiliser les fonctions WordPress à l'intérieur de Flight !
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

Ensuite, dans votre `routes.php` :

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## FAQ

**Q : Ai-je besoin de connaître Flight pour utiliser ce plugin ?**  
R : Oui, cela s'adresse aux développeurs qui souhaitent utiliser Flight au sein de WordPress. Une connaissance de base du routage et de la gestion des requêtes de Flight est recommandée.

**Q : Cela va-t-il ralentir mon site WordPress ?**  
R : Non ! Le plugin ne traite que les requêtes qui correspondent à vos routes Flight. Toutes les autres requêtes sont dirigées vers WordPress comme d'habitude.

**Q : Puis-je utiliser les fonctions WordPress dans mon application Flight ?**  
R : Absolument ! Vous avez un accès complet à toutes les fonctions, hooks et variables globales de WordPress au sein de vos routes et contrôleurs Flight.

**Q : Comment créer des routes personnalisées ?**  
R : Définissez vos routes dans le fichier `config/routes.php` de votre dossier d'application. Consultez le fichier exemple créé par le générateur de structure de dossiers pour des exemples.

## Journal des modifications

**1.0.0**  
Version initiale.

---

Pour plus d'informations, consultez le [GitHub repo](https://github.com/n0nag0n/wordpress-integration-for-flight-framework).