# IA et Expérience Développeur avec Flight

Flight est conçu pour vous aider à construire plus rapidement, plus intelligemment et avec moins de friction – surtout quand il s'agit de travailler avec des outils alimentés par l'IA et des flux de travail de développement modernes. Cette page explique comment Flight facilite le renforcement de vos projets avec l'IA, et comment commencer avec les nouveaux assistants IA intégrés directement dans le framework et le projet squelette.

---

## Prêt pour l'IA par Défaut : Le Projet Squelette

Le starter officiel [flightphp/skeleton](https://github.com/flightphp/skeleton) inclut désormais des instructions et une configuration pour les assistants de codage IA populaires :

- **GitHub Copilot**
- **Cursor**
- **Windsurf**

Ces outils sont pré-configurés avec des instructions spécifiques au projet, afin que vous et votre équipe puissiez obtenir une aide pertinente et contextuelle pendant que vous codez. Cela signifie :

- Les assistants IA comprennent les objectifs, le style et les exigences de votre projet
- Des conseils cohérents pour tous les contributeurs
- Moins de temps passé à expliquer le contexte, plus de temps à construire

> **Pourquoi cela importe-t-il ?**
>
> Lorsque vos outils IA connaissent l'intention et les conventions de votre projet, ils peuvent vous aider à créer des structures, à refactoriser le code et à éviter les erreurs courantes – rendant vous (et votre équipe) plus productifs dès le premier jour.

---

## Nouvelles Commandes IA dans Flight Core

_v3.16.0+_

Flight core inclut désormais deux puissantes commandes CLI pour vous aider à configurer et à diriger votre projet avec l'IA :

### 1. `ai:init` — Connectez-vous à Votre Fournisseur de LLM Préféré

Cette commande vous guide pour configurer les identifiants d'un fournisseur de LLM (Modèle de Langage de Grande Taille), comme OpenAI, Grok ou Anthropic (Claude).

**Exemple :**
```bash
php runway ai:init
```
Vous serez invité à sélectionner votre fournisseur, à entrer votre clé API et à choisir un modèle. Cela rend facile la connexion de votre projet aux derniers services IA – sans configuration manuelle requise.

### 2. `ai:generate-instructions` — Instructions de Codage IA Conscientes du Projet

Cette commande vous aide à créer ou à mettre à jour des instructions spécifiques au projet pour les assistants de codage IA. Elle vous pose quelques questions simples sur votre projet (comme à quoi il sert, quelle base de données vous utilisez, la taille de l'équipe, etc.), puis utilise votre fournisseur de LLM pour générer des instructions adaptées.

Si vous avez déjà des instructions, elle les mettra à jour pour refléter les réponses que vous fournissez. Ces instructions sont automatiquement écrites dans :
- `.github/copilot-instructions.md` (pour Github Copilot)
- `.cursor/rules/project-overview.mdc` (pour Cursor)
- `.windsurfrules` (pour Windsurf)

**Exemple :**
```bash
php runway ai:generate-instructions
```

> **Pourquoi cela est-il utile ?**
>
> Avec des instructions à jour et spécifiques au projet, vos outils IA peuvent :
> - Fournir de meilleures suggestions de code
> - Comprendre les besoins uniques de votre projet
> - Aider à intégrer plus rapidement les nouveaux contributeurs
> - Réduire les frictions et la confusion au fil de l'évolution de votre projet

---

## Pas Seulement pour Construire des Applications IA

Bien que vous puissiez absolument utiliser Flight pour créer des fonctionnalités alimentées par l'IA (comme des chatbots, des API intelligentes ou des intégrations), la véritable force réside dans la manière dont Flight vous aide à travailler mieux avec les outils IA en tant que développeur. Il s'agit de :

- **Améliorer la productivité** avec un codage assisté par l'IA
- **Maintenir votre équipe alignée** avec des instructions partagées et évolutives
- **Faciliter l'intégration** pour les nouveaux contributeurs
- **Vous permettre de vous concentrer sur la construction**, et non sur la lutte contre vos outils

---

## En Apprendre Plus et Commencer

- Consultez le [Flight Skeleton](https://github.com/flightphp/skeleton) pour un starter prêt à l'emploi et adapté à l'IA
- Jetez un œil au reste de la [documentation de Flight](/learn) pour des conseils sur la création d'applications PHP rapides et modernes