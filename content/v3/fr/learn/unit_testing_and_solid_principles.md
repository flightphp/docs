> _Cet article a été publié à l'origine sur [Airpair](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) en 2015. Tout le crédit revient à Airpair et à Brian Fenton, qui a rédigé cet article à l'origine, bien que le site web ne soit plus disponible et que l'article n'existe plus que dans la [Wayback Machine](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing). Cet article a été ajouté au site à des fins d'apprentissage et d'éducation pour la communauté PHP dans son ensemble._

1 Configuration et installation
-----------------------------

### 1.1 Garder à jour

Disons-le dès le début : un nombre déprimant d'installations PHP en production ne sont pas à jour ou ne le restent pas. Que ce soit dû à des restrictions d'hébergement partagé, à des paramètres par défaut que personne ne modifie, ou à un manque de temps/budget pour les tests de mise à niveau, les binaires PHP ont tendance à être laissés de côté. Une pratique exemplaire claire qui mérite plus d'attention est d'utiliser toujours une version actuelle de PHP (5.6.x au moment de cet article). De plus, il est important de planifier des mises à niveau régulières de PHP lui-même ainsi que de toute extension ou bibliothèque de fournisseurs que vous utilisez. Les mises à niveau vous apportent de nouvelles fonctionnalités de langage, une vitesse améliorée, une utilisation de mémoire réduite et des mises à jour de sécurité. Plus vous mettez à niveau fréquemment, moins le processus est douloureux.

### 1.2 Définir des paramètres par défaut sensés

PHP fait un travail décent pour définir de bons paramètres par défaut avec ses fichiers _php.ini.development_ et _php.ini.production_, mais nous pouvons faire mieux. Par exemple, ils ne définissent pas de fuseau horaire pour nous. Cela a du sens du point de vue de la distribution, mais sans cela, PHP générera une erreur E_WARNING chaque fois que nous appelons une fonction liée à la date/heure. Voici quelques paramètres recommandés :

*   date.timezone - choisissez parmi la [liste des fuseaux horaires pris en charge](http://php.net/manual/en/timezones.php)
*   session.save_path - si nous utilisons des fichiers pour les sessions et non un autre gestionnaire de sauvegarde, définissez-le sur quelque chose en dehors de _/tmp_. Laisser cela sur _/tmp_ peut être risqué dans un environnement d'hébergement partagé car _/tmp_ a généralement des permissions larges. Même avec le bit collant défini, quiconque ayant accès à lister le contenu de ce répertoire peut connaître tous vos identifiants de session actifs.
*   session.cookie_secure - une évidence, activez-le si vous servez votre code PHP via HTTPS.
*   session.cookie_httponly - définissez-le pour empêcher les cookies de session PHP d'être accessibles via JavaScript
*   Plus... utilisez un outil comme [iniscan](https://github.com/psecio/iniscan) pour tester votre configuration contre les vulnérabilités courantes

### 1.3 Extensions

Il est également une bonne idée de désactiver (ou au moins de ne pas activer) les extensions que vous n'utiliserez pas, comme les pilotes de base de données. Pour voir ce qui est activé, exécutez la commande `phpinfo()` ou allez dans une ligne de commande et exécutez ceci.

```bash
$ php -i
``` 

Les informations sont les mêmes, mais phpinfo() ajoute un formatage HTML. La version CLI est plus facile à rediriger vers grep pour trouver des informations spécifiques. Ex.

```bash
$ php -i | grep error_log
```

Une mise en garde de cette méthode : il est possible d'avoir des paramètres PHP différents s'appliquant à la version orientée web et à la version CLI.

2 Utiliser Composer
--------------

Cela peut surprendre, mais l'une des meilleures pratiques pour écrire du PHP moderne est d'en écrire moins. Bien que ce soit vrai que l'un des meilleurs moyens de bien programmer est de le faire, il y a un grand nombre de problèmes qui ont déjà été résolus dans l'espace PHP, comme le routage, les bibliothèques de validation d'entrée de base, la conversion d'unités, les couches d'abstraction de base de données, etc... Allez simplement sur [Packagist](https://www.packagist.org/) et explorez. Vous constaterez probablement que des parties significatives du problème que vous essayez de résoudre ont déjà été écrites et testées.

Bien qu'il soit tentant d'écrire tout le code vous-même (et il n'y a rien de mal à écrire votre propre framework ou bibliothèque comme une expérience d'apprentissage), vous devriez lutter contre ces sentiments de "Pas Inventé Ici" et vous épargner beaucoup de temps et de maux de tête. Suivez plutôt la doctrine de PIE - Fierement Inventé Ailleurs. De plus, si vous choisissez d'écrire votre propre élément, ne le publiez pas à moins qu'il ne fasse quelque chose de significativement différent ou meilleur que les offres existantes.

[Composer](https://www.getcomposer.org/) est un gestionnaire de paquets pour PHP, similaire à pip en Python, gem en Ruby et npm en Node. Il vous permet de définir un fichier JSON qui liste les dépendances de votre code, et il essaiera de résoudre ces exigences en téléchargeant et en installant les paquets de code nécessaires.

### 2.1 Installer Composer

Nous supposons que c'est un projet local, donc installons une instance de Composer juste pour le projet actuel. Naviguez vers votre répertoire de projet et exécutez ceci :
```bash
$ curl -sS https://getcomposer.org/installer | php
```

Gardez à l'esprit que rediriger n'importe quel téléchargement directement vers un interpréteur de script (sh, ruby, php, etc.) est un risque de sécurité, donc lisez le code d'installation et assurez-vous d'être à l'aise avec cela avant d'exécuter une commande comme celle-ci.

Pour des raisons de commodité (si vous préférez taper `composer install` plutôt que `php composer.phar install`), vous pouvez utiliser cette commande pour installer une copie unique de composer globalement :

```bash
$ mv composer.phar /usr/local/bin/composer
$ chmod +x composer
```

Vous devrez peut-être exécuter celles-ci avec `sudo` en fonction de vos permissions de fichier.

### 2.2 Utiliser Composer

Composer a deux catégories principales de dépendances qu'il peut gérer : "require" et "require-dev". Les dépendances listées comme "require" sont installées partout, mais les dépendances "require-dev" ne sont installées que lorsqu'elles sont spécifiquement demandées. Celles-ci sont généralement des outils pour lorsque le code est en développement actif, tels que [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer). La ligne ci-dessous montre un exemple de comment installer [Guzzle](http://docs.guzzlephp.org/en/latest/), une bibliothèque HTTP populaire.

```bash
$ php composer.phar require guzzle/guzzle
```

Pour installer un outil juste à des fins de développement, ajoutez le drapeau `--dev` :

```bash
$ php composer.phar require --dev 'sebastian/phpcpd'
```

Cela installe [PHP Copy-Paste Detector](https://github.com/sebastianbergmann/phpcpd), un autre outil de qualité de code en tant que dépendance de développement seulement.

### 2.3 Installer vs mettre à jour

Lorsque nous exécutons `composer install` pour la première fois, il installera les bibliothèques et leurs dépendances dont nous avons besoin, en fonction du fichier _composer.json_. Une fois cela fait, composer crée un fichier de verrouillage, appelé _composer.lock_. Ce fichier contient une liste des dépendances que composer a trouvées pour nous et leurs versions exactes, avec des hachages. Ensuite, toute fois future que nous exécutons `composer install`, il regardera dans le fichier de verrouillage et installera ces versions exactes.

`composer update` est un peu différent. Il ignorera le fichier _composer.lock_ (s'il est présent) et essaiera de trouver les versions les plus récentes de chacune des dépendances qui satisfont encore les contraintes dans _composer.json_. Il écrira ensuite un nouveau fichier _composer.lock_ une fois terminé.

### 2.4 Autoload

À la fois `composer install` et `composer update` généreront un [autoload](https://getcomposer.org/doc/04-schema.md#autoload) pour nous qui indique à PHP où trouver tous les fichiers nécessaires pour utiliser les bibliothèques que nous venons d'installer. Pour l'utiliser, ajoutez simplement cette ligne (généralement à un fichier de bootstrap qui s'exécute à chaque requête) :
```php
require 'vendor/autoload.php';
```

3 Suivre les bons principes de conception
-------------------------------

### 3.1 SOLID

SOLID est un mnémonique pour nous rappeler cinq principes clés dans une bonne conception de logiciel orienté objet.

#### 3.1.1 S - Principe de responsabilité unique

Cela stipule que les classes ne devraient avoir qu'une seule responsabilité, ou dit autrement, elles ne devraient avoir qu'une seule raison de changer. Cela correspond bien à la philosophie Unix de nombreux petits outils, en faisant une chose bien. Les classes qui ne font qu'une chose sont beaucoup plus faciles à tester et à déboguer, et elles sont moins susceptibles de vous surprendre. Vous ne voulez pas qu'un appel de méthode à une classe Validator mette à jour des enregistrements de base de données. Voici un exemple d'une violation de SRP, comme on en voit couramment dans une application basée sur le [modèle ActiveRecord](http://en.wikipedia.org/wiki/Active_record_pattern).

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
    public function save() {}
}
```
    

Donc c'est un modèle d'[entité](http://lostechies.com/jimmybogard/2008/05/21/entities-value-objects-aggregates-and-roots/) assez basique. L'une de ces choses n'appartient pas ici cependant. La seule responsabilité d'un modèle d'entité devrait être le comportement lié à l'entité qu'il représente, il ne devrait pas être responsable de sa propre persistance.

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
}
class DataStore
{
    public function save(Model $model) {}
}
```

Ceci est meilleur. Le modèle Person est de retour à une seule chose, et le comportement de sauvegarde a été déplacé vers un objet de persistance. Notez également que j'ai seulement indiqué le type sur Model, pas sur Person. Nous y reviendrons lorsque nous arriverons aux parties L et D de SOLID.

#### 3.1.2 O - Principe ouvert-fermé

Il y a un test génial pour cela qui résume assez bien ce principe : pensez à une fonctionnalité à implémenter, probablement la plus récente sur laquelle vous avez travaillé ou que vous travaillez. Pouvez-vous implémenter cette fonctionnalité dans votre base de code existante UNIQUEMENT en ajoutant de nouvelles classes et sans modifier aucune classe existante dans votre système ? Votre code de configuration et de câblage obtient un peu de passe, mais dans la plupart des systèmes, cela est étonnamment difficile. Vous devez vous appuyer beaucoup sur le dispatch polymorphe et la plupart des bases de code ne sont pas configurées pour cela. Si vous êtes intéressé, il y a une bonne conférence Google sur YouTube à propos du [polymorphisme et de l'écriture de code sans Ifs](https://www.youtube.com/watch?v=4F72VULWFvc) qui approfondit le sujet. En bonus, la conférence est donnée par [Miško Hevery](http://misko.hevery.com/), que beaucoup connaissent comme le créateur de [AngularJs](https://angularjs.org/).

#### 3.1.3 L - Principe de substitution de Liskov

Ce principe est nommé d'après [Barbara Liskov](http://en.wikipedia.org/wiki/Barbara_Liskov), et est imprimé ci-dessous :

> "Les objets dans un programme devraient être remplaçables par des instances de leurs sous-types sans altérer la correction de ce programme."

Cela a l'air bien et tout, mais c'est plus clairement illustré avec un exemple.

```php
abstract class Shape
{
    public function getHeight();
    public function setHeight($height);
    public function getLength();
    public function setLength($length);
}
```   

Ceci va représenter notre forme basique à quatre côtés. Rien de fantaisiste ici.

```php
class Square extends Shape
{
    protected $size;
    public function getHeight() {
        return $this->size;
    }
    public function setHeight($height) {
        $this->size = $height;
    }
    public function getLength() {
        return $this->size;
    }
    public function setLength($length) {
        $this->size = $length;
    }
}
```

Voici notre première forme, le Carré. Une forme assez directe, non ? Vous pouvez supposer qu'il y a un constructeur où nous définissons les dimensions, mais vous voyez ici de cette implémentation que la longueur et la hauteur seront toujours les mêmes. Les carrés sont comme ça.

```php
class Rectangle extends Shape
{
    protected $height;
    protected $length;
    public function getHeight() {
        return $this->height;
    }
    public function setHeight($height) {
        $this->height = $height;
    }
    public function getLength() {
        return $this->length;
    }
    public function setLength($length) {
        $this->length = $length;
    }
}
```

Donc ici nous avons une forme différente. Elle a toujours les mêmes signatures de méthodes, c'est toujours une forme à quatre côtés, mais si nous commençons à essayer de les utiliser à la place les unes des autres ? Maintenant, tout d'un coup, si nous changeons la hauteur de notre Shape, nous ne pouvons plus supposer que la longueur de notre forme correspondra. Nous avons violé le contrat que nous avions avec l'utilisateur lorsque nous lui avons donné notre forme Carré.

Ceci est un exemple classique d'une violation du LSP et nous avons besoin de ce type de principe pour tirer le meilleur parti d'un système de types. Même le [typage duck](http://en.wikipedia.org/wiki/Duck_typing) ne nous dira pas si le comportement sous-jacent est différent, et comme nous ne pouvons pas le savoir sans le voir se casser, il est préférable de s'assurer qu'il ne l'est pas au départ.

#### 3.1.3 I - Principe de ségrégation d'interface

Ce principe dit de favoriser de nombreuses interfaces petites et granulaires par rapport à une grande. Les interfaces devraient être basées sur le comportement plutôt que sur "c'est l'une de ces classes". Pensez aux interfaces qui viennent avec PHP. Traversable, Countable, Serializable, des choses comme ça. Elles annoncent les capacités que l'objet possède, pas ce qu'il hérite. Donc gardez vos interfaces petites. Vous ne voulez pas qu'une interface ait 30 méthodes, 3 est un objectif bien meilleur.

#### 3.1.4 D - Principe d'inversion des dépendances

Vous avez probablement entendu parler de cela dans d'autres endroits qui parlaient de [l'injection de dépendances](http://en.wikipedia.org/wiki/Dependency_injection), mais l'inversion des dépendances et l'injection de dépendances ne sont pas tout à fait la même chose. L'inversion des dépendances est vraiment juste un moyen de dire que vous devriez dépendre des abstractions dans votre système et non de ses détails. Que signifie cela pour vous au quotidien ?

> N'utilisez pas directement mysqli_query() partout dans votre code, utilisez quelque chose comme DataStore->query() à la place.

Le cœur de ce principe est en fait sur les abstractions. Il s'agit plus de dire "utilisez un adaptateur de base de données" au lieu de dépendre d'appels directs à des choses comme mysqli_query. Si vous utilisez directement mysqli_query dans la moitié de vos classes, vous liez tout directement à votre base de données. Rien pour ou contre MySQL ici, mais si vous utilisez mysqli_query, ce type de détail de bas niveau devrait être caché dans un seul endroit et cette fonctionnalité devrait ensuite être exposée via un wrapper générique.

Maintenant je sais que c'est un exemple un peu usé si vous y pensez, car le nombre de fois où vous allez complètement changer votre moteur de base de données après que votre produit soit en production est très, très faible. Je l'ai choisi parce que je pensais que les gens seraient familiers avec l'idée de leur propre code. De plus, même si vous avez une base de données à laquelle vous restez fidèle, cet objet wrapper abstrait vous permet de corriger les bogues, de changer le comportement ou d'implémenter des fonctionnalités que vous souhaitez que votre base de données choisie ait. Il rend également les tests unitaires possibles là où les appels de bas niveau ne le feraient pas.

4 Exercices d'objets
---------------------

Ceci n'est pas un plongeon complet dans ces principes, mais les deux premiers sont faciles à retenir, apportent une bonne valeur et peuvent être appliqués immédiatement à presque n'importe quelle base de code.

### 4.1 Pas plus d'un niveau d'indentation par méthode

Ceci est un moyen utile de penser à décomposer les méthodes en morceaux plus petits, laissant un code plus clair et plus auto-documenté. Plus vous avez de niveaux d'indentation, plus la méthode fait de choses et plus d'état vous devez suivre dans votre tête pendant que vous travaillez avec.

Tout de suite, je sais que les gens vont s'opposer à cela, mais ceci n'est qu'une ligne directrice/règle heuristique, pas une règle dure et rapide. Je ne m'attends pas à ce que quiconque fasse respecter les règles PHP_CodeSniffer pour cela (bien que [des gens l'aient fait](https://github.com/object-calisthenics/phpcs-calisthenics-rules)).

Parcourons rapidement un échantillon de ce à quoi cela pourrait ressembler :

```php
public function transformToCsv($data)
{
    $csvLines = array();
    $csvLines[] = implode(',', array_keys($data[0]));
    foreach ($data as $row) {
        if (!$row) {
            continue;
        }
        $csvLines[] = implode(',', $row);
    }
    return $csvLines;
}
```

Bien que ce ne soit pas un code terrible (il est techniquement correct, testable, etc.), nous pouvons faire beaucoup plus pour le rendre clair. Comment réduirions-nous les niveaux d'imbrication ici ?

Nous savons que nous devons simplifier énormément le contenu de la boucle foreach (ou l'enlever complètement), donc commençons par là.

```php
if (!$row) {
    continue;
}
```   

Cette première partie est facile. Tout ce qu'elle fait, c'est ignorer les lignes vides. Nous pouvons contourner cela en utilisant une fonction PHP intégrée avant même d'arriver à la boucle.

```php
$data = array_filter($data);
foreach ($data as $row) {
    $csvLines[] = implode(',', $row);
}
```

Nous avons maintenant notre seul niveau d'imbrication. Mais en regardant cela, tout ce que nous faisons, c'est appliquer une fonction à chaque élément d'un tableau. Nous n'avons même pas besoin de la boucle foreach pour cela.

```php
$data = array_filter($data);
$csvLines = array_map(function($row) {
    return implode(',', $row);
}, $data);
```

Maintenant nous n'avons plus d'imbrication du tout, et le code sera probablement plus rapide car nous faisons toute la boucle avec des fonctions C natives au lieu de PHP. Nous devons cependant nous livrer à un peu de tricherie pour passer la virgule à `implode`, donc on pourrait argumenter que s'arrêter à l'étape précédente est beaucoup plus compréhensible.

### 4.2 Essayer de ne pas utiliser `else`

Ceci traite vraiment de deux idées principales. La première est les déclarations de retour multiples d'une méthode. Si vous avez assez d'informations pour prendre une décision sur le résultat de la méthode, allez-y et prenez cette décision et retournez. La seconde est une idée connue sous le nom de [clauses de garde](http://c2.com/cgi/wiki?GuardClause). Celles-ci sont essentiellement des vérifications de validation combinées à des retours précoces, généralement près du sommet d'une méthode. Laissez-moi vous montrer ce que je veux dire.

```php
public function addThreeInts($first, $second, $third) {
    if (is_int($first)) {
        if (is_int($second)) {
            if (is_int($third)) {
                $sum = $first + $second + $third;
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
    return $sum;
}
```

Donc c'est assez direct, cela ajoute 3 entiers ensemble et retourne le résultat, ou `null` si l'un des paramètres n'est pas un entier. En ignorant le fait que nous pourrions combiner toutes ces vérifications sur une seule ligne avec des opérateurs ET, je pense que vous pouvez voir comment la structure if/else imbriquée rend le code plus difficile à suivre. Maintenant regardez cet exemple à la place.

```php
public function addThreeInts($first, $second, $third) {
    if (!is_int($first)) {
        return null;
    }
    if (!is_int($second)) {
        return null;
    }
    if (!is_int($third)) {
        return null;
    }
    return $first + $second + $third;
}
```   

Pour moi, cet exemple est beaucoup plus facile à suivre. Ici, nous utilisons des clauses de garde pour vérifier nos affirmations initiales sur les paramètres que nous passons et quittons immédiatement la méthode s'ils ne passent pas. Nous n'avons plus non plus la variable intermédiaire pour suivre la somme tout au long de la méthode. Dans ce cas, nous avons vérifié que nous sommes déjà sur le chemin heureux et nous pouvons simplement faire ce que nous sommes venus faire. Encore une fois, nous pourrions faire toutes ces vérifications dans un seul `if`, mais le principe devrait être clair.

5 Tests unitaires
--------------

Les tests unitaires sont la pratique d'écriture de petits tests qui vérifient le comportement dans votre code. Ils sont presque toujours écrits dans le même langage que le code (dans ce cas PHP) et sont destinés à être assez rapides pour s'exécuter à tout moment. Ils sont extrêmement précieux en tant qu'outil pour améliorer votre code. En plus des avantages évidents de s'assurer que votre code fait ce que vous pensez qu'il fait, les tests unitaires peuvent fournir un retour de conception très utile. Si un morceau de code est difficile à tester, il souligne souvent des problèmes de conception. Ils vous donnent également un filet de sécurité contre les régressions, et cela vous permet de refactoriser beaucoup plus souvent et d'évoluer vers une conception plus propre.

### 5.1 Outils

Il y a plusieurs outils de tests unitaires dans PHP, mais de loin le plus courant est [PHPUnit](https://phpunit.de/). Vous pouvez l'installer en téléchargeant un [fichier PHAR](http://php.net/manual/en/intro.phar.php) [directement](https://phar.phpunit.de/phpunit.phar), ou en l'installant avec composer. Comme nous utilisons composer pour tout le reste, nous montrerons cette méthode. De plus, comme PHPUnit n'est probablement pas destiné à être déployé en production, nous pouvons l'installer en tant que dépendance de développement avec la commande suivante :

```bash
composer require --dev phpunit/phpunit
```

### 5.2 Les tests sont une spécification

Le rôle le plus important des tests unitaires dans votre code est de fournir une spécification exécutable de ce que le code est censé faire. Même si le code de test est erroné, ou si le code a des bogues, la connaissance de ce que le système est _censé_ faire est inestimable.

### 5.3 Écrivez vos tests en premier

Si vous avez eu la chance de voir un ensemble de tests écrits avant le code et un écrit après que le code soit terminé, ils sont étonnamment différents. Les tests "après" sont beaucoup plus préoccupés par les détails d'implémentation de la classe et s'assurer qu'ils ont une bonne couverture de lignes, alors que les tests "avant" sont plus sur la vérification du comportement externe souhaité. C'est vraiment ce qui nous intéresse avec les tests unitaires de toute façon, c'est de s'assurer que la classe exhibe le bon comportement. Les tests axés sur l'implémentation rendent en fait le refactoring plus difficile car ils se cassent si les internes des classes changent, et vous venez de vous coûter les avantages de la dissimulation d'informations de la POO.

### 5.4 Ce qui fait un bon test unitaire

Les bons tests unitaires partagent beaucoup des caractéristiques suivantes :

*   Rapide - devrait s'exécuter en millisecondes.
*   Pas d'accès réseau - devrait pouvoir désactiver le sans fil/débrancher et tous les tests passent encore.
*   Accès limité au système de fichiers - cela ajoute à la vitesse et à la flexibilité si le code est déployé dans d'autres environnements.
*   Pas d'accès à la base de données - évite les activités coûteuses de configuration et de démontage.
*   Tester une chose à la fois - un test unitaire ne devrait avoir qu'une seule raison d'échouer.
*   Bien nommé - voir 5.2 ci-dessus.
*   Principalement des objets factices - les seuls "vrais" objets dans les tests unitaires devraient être l'objet que nous testons et les objets de valeur simples. Le reste devrait être une forme de [test double](https://phpunit.de/manual/current/en/test-doubles.html)

Il y a des raisons d'aller à l'encontre de certaines de celles-ci, mais en tant que lignes directrices générales, elles vous serviront bien.

### 5.5 Quand les tests sont douloureux

> Les tests unitaires vous forcent à ressentir la douleur d'une mauvaise conception dès le début - Michael Feathers

Lorsque vous écrivez des tests unitaires, vous vous forcez à utiliser réellement la classe pour accomplir des choses. Si vous écrivez des tests à la fin, ou pire encore, si vous jetez simplement le code par-dessus le mur pour que QA ou qui que ce soit écrive des tests, vous ne recevez aucun retour sur la façon dont la classe se comporte réellement. Si nous écrivons des tests, et que la classe est une vraie douleur à utiliser, nous le découvrirons pendant que nous l'écrivons, ce qui est presque le moment le moins cher pour le corriger.

Si une classe est difficile à tester, c'est un défaut de conception. Différents défauts se manifestent de différentes manières cependant. Si vous devez faire beaucoup de moqueries, votre classe a probablement trop de dépendances, ou vos méthodes font trop. Plus vous avez de configuration pour chaque test, plus il est probable que vos méthodes font trop. Si vous devez écrire des scénarios de test vraiment compliqués pour exercer un comportement, les méthodes de la classe font probablement trop. Si vous devez creuser à l'intérieur d'une foule de méthodes privées et d'état pour tester des choses, peut-être qu'une autre classe essaie de sortir. Les tests unitaires sont très bons pour exposer les "classes iceberg" où 80% de ce que la classe fait est caché dans du code protégé ou privé. J'étais autrefois un grand fan de rendre autant que possible protégé, mais maintenant j'ai réalisé que je rendais simplement mes classes individuelles responsables de trop, et la vraie solution était de diviser la classe en morceaux plus petits.

> **Écrit par Brian Fenton** - Brian Fenton est un développeur PHP depuis 8 ans dans le Midwest et la Bay Area, actuellement chez Thismoment. Il se concentre sur l'artisanat du code et les principes de conception. Blog sur www.brianfenton.us, Twitter sur @brianfenton. Quand il n'est pas occupé à être un père, il aime la nourriture, la bière, les jeux et l'apprentissage.