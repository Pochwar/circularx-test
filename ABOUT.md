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
- url : `/api/takeover`
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
- url : `/api/takeover/{id}`
- query params : aucun
- réponse :
  - code : `200`
  - format : `json`
  - contenu : objet _Takeover_
- Cas d'erreur :
  - `404` (Not Found) : La reprise demandée n'existe pas


### Consulter les produits attachés à une commande
- méthode : `GET`
- url : `/api/takeover/{id}/products`
- query params : aucun
- réponse :
  - code : `200`
  - format : `json`
  - contenu : tableau d'objet _Products_
- Cas d'erreur :
  - `404` (Not Found) : La reprise demandée n'existe pas

### Lister les produits pouvant être repris
- méthode : `GET`
- url : `/api/product`
- query params : aucun
- réponse :
  - code : `200`
  - format : `json`
  - contenu : tableau d'objet _Products_
- Cas d'erreur : aucun

### Lister les commandes de reprise existantes comportant au moins un produit donné ou une marque donnée
- méthode : `GET`
- url : `/api/takeover`
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
Je n'avais jamais utilisé Github actions avant ce projet. Je connais Gitlab CI et j'ai déjà mis en place

### TODO
- finir les tests
  - Consulter le prix total et l’email client de la commande de reprise
  - Consulter les produits attachés à une commande
  - Lister les produits pouvant être repris
  - Lister les commandes de reprise existantes comportant au moins un produit donné
  - Lister les commandes de reprise existantes comportant au moins une marque donnée
  - test prix positif produit
  - test produt unique

- mettre en place GitHub Actions
