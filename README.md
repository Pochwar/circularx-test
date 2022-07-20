# Test CircularX

This test project is based on this repository [https://github.com/dunglas/symfony-docker](https://github.com/dunglas/symfony-docker) to have [Symfony](https://symfony.com) web framework, with full [HTTP/2](https://symfony.com/doc/current/weblink.html), HTTP/3 and HTTPS support.

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker-compose build --pull --no-cache` to build fresh images
3. Run `docker-compose up` (the logs will be displayed in the current shell)
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker-compose down --remove-orphans` to stop the Docker containers.

## Usage

### API
Open `https://localhost/api` in your favorite web browser to access interface.

### Tests
Run tests with the folowing command:
```shell
docker-compose run --rm php php bin/phpunit
```

### Import csv
Import products with symfony command `app:import-product-csv`.

A test file `products.csv` is available in `/import` folder, So just run the following command to test import:
```shell
docker-compose run --rm php bin/console app:import-product-csv products.csv
```

This file has some errors in it to check some import constraints.

Or you can create your own file and put it in the `/import` folder to use it.

The CSV file must have the following columns:
- `product` - the product name
- `brand` - the brand name
- `price` - the price of the product, no separator for decimals (fe: 189,99€ -> 18999)
