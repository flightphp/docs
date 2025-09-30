# Gestionnaire de Fichiers Téléversés

## Aperçu

La classe `UploadedFile` dans Flight facilite et sécurise la gestion des téléversements de fichiers dans votre application. Elle encapsule les détails du processus de téléversement de fichiers de PHP, vous offrant une façon simple et orientée objet d'accéder aux informations sur les fichiers et de déplacer les fichiers téléversés.

## Compréhension

Lorsque un utilisateur téléverse un fichier via un formulaire, PHP stocke les informations sur le fichier dans le superglobal `$_FILES`. Dans Flight, vous interagissez rarement directement avec `$_FILES`. Au lieu de cela, l'objet `Request` de Flight (accessible via `Flight::request()`) fournit une méthode `getUploadedFiles()` qui retourne un tableau d'objets `UploadedFile`, rendant la gestion des fichiers beaucoup plus pratique et robuste.

La classe `UploadedFile` fournit des méthodes pour :
- Obtenir le nom de fichier original, le type MIME, la taille et l'emplacement temporaire
- Vérifier les erreurs de téléversement
- Déplacer le fichier téléversé vers un emplacement permanent

Cette classe vous aide à éviter les pièges courants avec les téléversements de fichiers, comme la gestion des erreurs ou le déplacement sécurisé des fichiers.

## Utilisation de Base

### Accès aux Fichiers Téléversés depuis une Requête

La façon recommandée d'accéder aux fichiers téléversés est via l'objet de requête :

```php
Flight::route('POST /upload', function() {
    // Pour un champ de formulaire nommé <input type="file" name="myFile">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    $file = $uploadedFiles['myFile'];

    // Maintenant vous pouvez utiliser les méthodes de UploadedFile
    if ($file->getError() === UPLOAD_ERR_OK) {
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
        echo "Fichier téléversé avec succès !";
    } else {
        echo "Échec du téléversement : " . $file->getError();
    }
});
```

### Gestion de Téléversements Multiples de Fichiers

Si votre formulaire utilise `name="myFiles[]"` pour des téléversements multiples, vous obtiendrez un tableau d'objets `UploadedFile` :

```php
Flight::route('POST /upload', function() {
    // Pour un champ de formulaire nommé <input type="file" name="myFiles[]">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    foreach ($uploadedFiles['myFiles'] as $file) {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
            echo "Téléversé : " . $file->getClientFilename() . "<br>";
        } else {
            echo "Échec du téléversement : " . $file->getClientFilename() . "<br>";
        }
    }
});
```

### Création Manuelle d'une Instance UploadedFile

Généralement, vous ne créerez pas un `UploadedFile` manuellement, mais vous le pouvez si nécessaire :

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

### Accès aux Informations sur le Fichier

Vous pouvez facilement obtenir les détails sur le fichier téléversé :

```php
echo $file->getClientFilename();   // Nom de fichier original depuis l'ordinateur de l'utilisateur
echo $file->getClientMediaType();  // Type MIME (par ex., image/png)
echo $file->getSize();             // Taille du fichier en octets
echo $file->getTempName();         // Chemin temporaire du fichier sur le serveur
echo $file->getError();            // Code d'erreur de téléversement (0 signifie pas d'erreur)
```

### Déplacement du Fichier Téléversé

Après avoir validé le fichier, déplacez-le vers un emplacement permanent :

```php
try {
  $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
  echo "Fichier téléversé avec succès !";
} catch (Exception $e) {
  echo "Échec du téléversement : " . $e->getMessage();
}
```

La méthode `moveTo()` lancera une exception si quelque chose se passe mal (comme une erreur de téléversement ou un problème de permissions).

### Gestion des Erreurs de Téléversement

S'il y a eu un problème pendant le téléversement, vous pouvez obtenir un message d'erreur lisible par un humain :

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
  // Vous pouvez utiliser le code d'erreur ou capturer l'exception de moveTo()
  echo "Il y a eu une erreur lors du téléversement du fichier.";
}
```

## Voir Aussi

- [Requests](/learn/requests) - Apprenez comment accéder aux fichiers téléversés depuis les requêtes HTTP et voyez plus d'exemples de téléversement de fichiers.
- [Configuration](/learn/configuration) - Comment configurer les limites de téléversement et les répertoires dans PHP.
- [Extending](/learn/extending) - Comment personnaliser ou étendre les classes principales de Flight.

## Dépannage

- Vérifiez toujours `$file->getError()` avant de déplacer le fichier.
- Assurez-vous que votre répertoire de téléversement est accessible en écriture par le serveur web.
- Si `moveTo()` échoue, vérifiez le message d'exception pour les détails.
- Les paramètres `upload_max_filesize` et `post_max_size` de PHP peuvent limiter les téléversements de fichiers.
- Pour les téléversements multiples de fichiers, bouclez toujours à travers le tableau d'objets `UploadedFile`.

## Journal des Modifications

- v3.12.0 - Ajout de la classe `UploadedFile` à l'objet de requête pour une gestion plus facile des fichiers.