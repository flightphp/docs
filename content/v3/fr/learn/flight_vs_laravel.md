# Flight vs Laravel

## Qu'est-ce que Laravel ?
[Laravel](https://laravel.com) est un framework complet qui possède toutes les fonctionnalités avancées et un écosystème axé sur les développeurs impressionnant, 
mais au prix d'une performance et d'une complexité élevées. L'objectif de Laravel est que le développeur atteigne le plus haut niveau de 
productivité et que les tâches courantes soient facilitées. Laravel est un excellent choix pour les développeurs qui souhaitent construire une 
application web complète et d'entreprise. Cela implique toutefois certains compromis, notamment en termes de performance et 
de complexité. Apprendre les bases de Laravel peut être facile, mais acquérir une maîtrise du framework peut prendre du temps. 

Il existe également de nombreux modules Laravel, ce qui fait que les développeurs ont souvent l'impression que la seule façon de résoudre les problèmes est d'utiliser 
ces modules, alors qu'en réalité, il serait possible d'utiliser une autre bibliothèque ou d'écrire son propre code.

## Avantages par rapport à Flight

- Laravel dispose d'un **énorme écosystème** de développeurs et de modules qui peuvent être utilisés pour résoudre les problèmes courants.
- Laravel possède un ORM complet qui peut être utilisé pour interagir avec votre base de données.
- Laravel offre une _quantité folle_ de documentation et de tutoriels qui peuvent être utilisés pour apprendre le framework. Cela peut être positif pour plonger dans les détails ou négatif parce qu'il y a tant de choses à parcourir.
- Laravel inclut un système d'authentification intégré qui peut être utilisé pour sécuriser votre application.
- Laravel propose des podcasts, des conférences, des réunions, des vidéos et d'autres ressources qui peuvent être utilisées pour apprendre le framework.
- Laravel est conçu pour un développeur expérimenté qui cherche à construire une application web complète et d'entreprise.

## Inconvénients par rapport à Flight

- Laravel a beaucoup plus d'éléments sous le capot que Flight. Cela entraîne un **coût dramatique** en termes
  de performance. Consultez les [benchmarks TechEmpower](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  pour plus d'informations.
- Flight est conçu pour un développeur qui cherche à construire une application web légère, rapide et facile à utiliser.
- Flight privilégie la simplicité et la facilité d'utilisation.
- L'une des fonctionnalités principales de Flight est qu'il fait de son mieux pour maintenir la compatibilité descendante. Laravel provoque [beaucoup de frustrations](https://www.google.com/search?q=laravel+breaking+changes+major+version+complaints&sca_esv=6862a9c407df8d4e&sca_upv=1&ei=t72pZvDeI4ivptQP1qPMwQY&ved=0ahUKEwiwlurYuNCHAxWIl4kEHdYRM2gQ4dUDCBA&uact=5&oq=laravel+breaking+changes+major+version+complaints&gs_lp=Egxnd3Mtd2l6LXNlcnAiMWxhcmF2ZWwgYnJlYWtpbmcgY2hhbmdlcyBtYWpvciB2ZXJzaW9uIGNvbXBsYWludHMyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEdIjAJQAFgAcAF4AZABAJgBAKABAKoBALgBA8gBAJgCAaACB5gDAIgGAZAGCJIHATGgBwA&sclient=gws-wiz-serp) entre les versions majeures.
- Flight est destiné aux développeurs qui s'aventurent pour la première fois dans le monde des frameworks.
- Flight n'a aucune dépendance, alors que [Laravel a un nombre atroce de dépendances](https://github.com/laravel/framework/blob/12.x/composer.json)
- Flight peut également gérer des applications de niveau entreprise, mais il n'a pas autant de code boilerplate que Laravel. 
  Cela nécessitera également plus de discipline de la part du développeur pour garder les choses organisées et bien structurées.
- Flight donne au développeur plus de contrôle sur l'application, alors que Laravel cache une multitude de magie en arrière-plan qui peut être frustrante.