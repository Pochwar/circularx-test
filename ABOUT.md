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