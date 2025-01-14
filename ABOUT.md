# Test CircularX

## Mise en place

### Symfony
Afin de gagner du temps sur la mise en place du projet, j'ai décider d'utiliser un environement clef en main avec Symfony via le projet Docker mentionné dans la Documentation de Symfony : https://symfony.com/doc/current/setup/docker.html#complete-docker-environment

En peu de temps j'ai ainsi un projet Symfony 6.1 fonctionnel dans un environnement Dockerisé sous PHP8 et utilisant une base de donnée PostgreSQL.

Après avoir démarré le projet via Docker Compose (`docker-compose up`), j'ai accès à la page d'accueil de Symphony dans mon navigateur : `https://localhost`

### Outil de test
J'ai ensuite installé le package composer `symfony/test-pack` afin de mettre en place des tests me permettant de travailler avec la méthode TDD.

```shell
dcomp require symfony/test-pack
```

Je pourrais par la suite exécuter les tests avec la commande
```shell
dcrun php php bin/phpunit
```

_Note : j'utilise différents alias pour simplifier les commandes dans avec Docker Compose_ :
- `dcrun` pour utiliser un service docker compose à la volée (`docker-compose run --rm`)
- `dcomp` pour utiliser composer (`docker-compose run --rm php php -d memory_limit=-1 /usr/bin/composer`)

### API
J'ai choisi d'utiliser API platform afin de faciliter l'implémentation d'une API au sein de ce projet symfony

```shell
dcomp require api
```

Une fois l'installation terminée, une interface utilisateur pour l'API est disponible à cet url : `https://localhost/api`

## Modèles de donnée
La seconde étape a consisté à étudier la demande métier afin d'en dégager le modèle de données suivant.

Je vais avoir besoin des entités suivantes :

- **Takeover** (une commande de reprise)
  - id
  - user_id

- **Product** (un modèle de téléphone)
  - id
  - brand_id
  - name
  - price

- **Brand** (marque de téléphone)
  - id
  - name

- **ProductTakeover** (produits rattachés a une reprise)
  - product_id
  - takeover_id
  - price

- **User** (client qui passe une commande)
  - id
  - email

## Endpoints API
Dernière étape avant de me mettre à coder : définir les différents endpoints attendus pour cet execrice.

### Créer des commandes de reprise
- méthode : `POST`
- url : `/api/takeovers`
- body params :

```json
{
  userId: string,
  products: [
    {
      productId: string,
      price: number
    }
  ]
}
```
- réponse : 
  - code : `200`
    - format : `json`
    - contenu : _Takeover_
- Cas d'erreur :
  - `422` (Unprocessable Entity) : Erreur de validation
    

### Consulter le prix total et l’email client de la commande de reprise
- méthode : `GET`
- url : `/api/takeovers/{id}`
- query params : aucun
- réponse :
  - code : `200`
  - format : `json`
  - contenu : objet _Takeover_
- Cas d'erreur :
  - `404` (Not Found) : La reprise demandée n'existe pas


### ~~Consulter les produits attachés à une commande~~
[EDIT] Je n'ai finalement pas mis en place ce endpoint (qui n'est pas géré par defaut par API PLatform) car les produits sont consultables directement depuis une commande
- méthode : `GET`
- url : `/api/takeovers/{id}/products`
- query params : aucun
- réponse :
  - code : `200`
  - format : `json`
  - contenu : tableau d'objet _Products_
- Cas d'erreur :
  - `404` (Not Found) : La reprise demandée n'existe pas

### Lister les produits pouvant être repris
- méthode : `GET`
- url : `/api/products`
- query params : aucun
- réponse :
  - code : `200`
  - format : `json`
  - contenu : tableau d'objet _Products_
- Cas d'erreur : aucun

### Lister les commandes de reprise existantes comportant au moins un produit donné ou une marque donnée
- méthode : `GET`
- url : `/api/takeovers`
- query params :
  - productId (optionnel) : string
  - brandId (optionnel) : string
- réponse :
  - code : `200`
  - format : `json`
  - contenu : tableau d'objet _Takeover_
- Cas d'erreur : aucun

## Réalisation

Pour info, je n'avais jamais utilisé Symfony 6 jusqu'a présent, je n'avais utilisé que la version 5. Je n'ai pas vu de gros changements avec ce que j'ai l'habitude de faire, à part l'utilisation des attributes au lieu des annotations

### Tests
Je voulais initialement travailler en TDD, mais, n'ayant jamais utilisé API Platform auparavant ni le maker-bundle, je ne me rendai pas compte à quel point cela "mâche" le travail. Et là ou je comptais créer mes entités, mes repositories et mes controllers, le maker-bundle a généré automatiquement les entités et les repositories. Quant aux controlleurs d'API que je comptais faire, nul besoin car API Platform se base sur les entités et les attributes pour fonctionner.

Sans avoir fait une ligne de code, j'avais une API fonctionelle.
A défaut de faire du TDD donc, j'ai entrepris de mettre en place des tests fonctionnels concernant l'API, a savoir requeter des endpoints de l'API et vérifier que l'on obtient bien le résultat attendu.

J'ai donc mis en place les fixtures, une base de donnée de test SQLite et ai commencé a écrire les tests.

J'ai utilisé les ApiTestCase fournis par API Platform. J'ai commencé par des tests sur les Users afin d'avoir un cas simple de requête GET, et j'ai été surpris par le format, en effet l'API utilise par défaut le format json-ld, que je ne connaissais pas, j'ai du m'adapter a cette manière de communiquer avec l'API dans l'écriture de mes tests.

[EDIT] J'ai fini par configurer l'API en format json, etant plus à l'aise avec ce format et par manque de temps pour bien apréhender json-ld.

Un point sur lequel j'ai bloqué : le test des cas d'erreurs. en effet, dans l'interface web d'API platform, j'ai bien une erreur 422 quand je fourni un email eronné ou déjà dans la base, mais dans mes tests, impossible de tester ça car la couche Symfony renvoie une exception ClientException. Après avoir passé du temps à chercher comment résoudre ça, je me suis résolu à catcher l'exception et tester le message d'erreur.


### Import CSV
J'ai d'abord pensé utiliser une librairie pour faire cela, mais en cherchant de la doc, j'ai trouvé un tutoriel expliquant comment importer et lire un fichier csv. Le code etant relativement simple, j'ai décider de faire l'implémentation sans librairie.

J'ai créé la commande `app:import-product-csv` qui prend pour seul argument le nom du fichier csv à importer, qui doit se trouver dans le répertoire `/import`.

Je fais quelques vérifications, comme l'existance de la marque, le fait que le produit n'existe pas déjà en base de données et que le prix soit positif (l'assertion sur l'entité ne fonctionne pas ici).

Un fichier d'exemple se trouve dans le répertoire `/import`, il est possible de tester directement la commande :
```shell
bin/console app:import-product-csv products.csv
```

### Github actions
Je n'avais jamais utilisé Github actions avant ce projet. Je connais Gitlab CI et j'ai déjà mis en place un procedure de déploiement continu sur un petit projet perso, ainsi que des tests front avec Cypress. J'avais donc une compréhension de ce qu'est Github Actions.

En cherchant comment mettre cela en place sur le projet, je me rend compte qu'une CI est déjà en place (inclus dans le repo sur lequel je me suis basé) pour faire le build de l'application.

J'ai donc cherché comment ajouter les tests PHPUnit a cette CI et j'ai trouvé une action pour PHPUNit pour Symfony dans le marketplace de github : https://github.com/marketplace/actions/phpunit-for-symfony

J'ai ainsi ajouté un step pour les tests dans le workflow existant. Mais cela ne fonctionnait pas car la version de PHP dans le container n'est pas la bone (7.4 au lieu de 8.1). Après avoir passé du temps a chercher comment changer la version de PHP, en vain, j'ai fini par tester une autre manière de faire avec ce tuto : https://antonshell.me/en/post/github-actions-symfony.

Après un peu de temps a tester et adapter, j'ai fini par faire fonctionner les test dans Github Actions.

### Conclusion - reflexions
Faire cet exercice sur un temps relativement court (3 soirées) était assez stimulant.
L'exercice est intéressant car il est relativement bien cadré tout en laissant des choix à faire.

Ainsi, le choix d'utiliser un projet Symfony déjà Dockerisé m'a fait gagner du temps, tout en me sortant de ma zone de confort sur certains aspects, en effet, je ne connaissait pas Symfony 6 (même si peu de changement par rapport a Symfony 5) ni PostgreSQL (peu de changements perçus de ma part  comparé a MySQL).

De la même manière, APIPlatform est un choix que j'ai fais assez rapidement, toujours dans l'optique de gagner du temps vu le délai. J'ai l'avais utilisé une fois il y a longtemps pour un petit projet, autant dire que je ne le maitrise pas du tout.

J'ai d'abord été agréablement surpris par la rapidité avec la quelle j'ai pu mettre en place un API fonctionnelle juste en créant mes entités. Puis j'ai regretté mon choix quand j'ai commencé a me confronté a des problématiques que j'aurais su résoudre très facilement dans un autre contexte. En effet, j'ai concu plusieurs API en PHP avec Laravel ou Symfony, et j'ai toujours fait ça "à la main", avec un controller qui prend en charge les requêtes, fait appel à un manager pour la couche métier, qui fait appel à un repository pour persister les entités.

Ainsi j'organise mes fichiers et je sais qui gère quoi. Avec API Platform, tout est géré par le package et ici, pas de Controller d'API, pas de manager, juste des attributes a mettre dans les entités pour faire ce dont on a besoin.

Cependant, la documentation d'API Platform est bien faite, et j'ai su faire tout ce dont j'avais besoin pour cet exercice, ou presque !

En effet, la validation avec les constraints de Symfony n'a pas fonctionné sur une propriété, le `price` de l'entité pivot `TakeoverProduct` et je pense que c'ets justement, car c'est une entité pivot. Par manque de temps, j'ai mis en place une solution de secours.

Pour finir sur API Platform, j'ai d'abord essayé d'utiliser le format par defaut, json-ld, que je ne connaissais pas, mais cela m'a ralenti dans la mise en place des tests. N'ayant pas le temps d'approfondir ma découverte de ce format, j'ai fini par trouver comment mettre un format json, que je maitrise mieux. J'aimerai tout de même prendre le temps de découvrir json-ld.

J'ai, sauf erreur de ma part, réalisé toutes les demandes de l'exercice (sauf l'import au format json, je n'ai fait que les fichiers csv) en essayant de trouver le bon équilibre entre rapidité et qualité.

Si j'avais eu plus de temps, j'aurais mis en place plus de tests (beaucoup de cas d'usages ne sont pas testés), j'aurais passé plus de temps a bien mettre en place un système de validation, j'aurais utilisé des builders de test plutot que des fixtures (plus lisible et plus maintenable) et j'aurais utilisé des outils de qualité de code comme PHP Cs-Fixer et PHP Stan.
