
# Crypto Coin Api


The application serves to search prices of cryptocurrencies in USD. Functionalities were created that allow specifying the currency to be searched, which can be DAXCI, ETHEREUM, ATOM and BITCOIN, in addition to the possibility of searching the current price and also a price for a specific date.

# Endpoints

- Search for the current price of a specific currency.



```
/crypto-coin/current/?coin_name={COIN_NAME}
```

- Search for the price of a currency on a given date.

```
/crypto-coin/previous/?coin_name={COIN_NAME}&date={DATE}
```

- Return example.
```
{
    "data": {
        "id": 113,
        "coin_id": "bitcoin",
        "price": 19998.467839576,
        "name": "bitcoin",
        "consulted_at": "12-07-2022"
    }
}
````

# About the API

### The api was made using the PHP language and the Laravel framework. Some choices of structures used:

 - Creation of an `Action` layer to separate the business rule from the application.
 - Creation of a layer of `Repositories` to abstract the use of the database.
 - Creation of `Unit Tests` to predict the functionality of an Actions.
 - Creation of `Functional Tests` to predict the communication between actions and repositories.
 - Creation of a `Gateways` layer to abstract the use of the `Coingecko` library.

# Run project

## Requirements

- Docker
- Docker-compose

## Step by step

1. - Clone the project
```
git clone git@github.com:Chris7T/crypto-coin.git
```
2. - Enter the project folder
```
cd crypton-coin
```
3. - Install the composer
```
composer install
```
4. - Enter the laradock folder
```
cd laradock
```
5. - Up the containers
```
docker-compose up -d nginx redis mysql
```
6. - Enter the workspace
```
docker exec --user=laradock -it crypto_docker_workspace_1 bash
```
7. - Generate the API Key
```
php artisan key:generate
```
8. - Run the migrations
```
php artisan migrate
```
9. - Run the tests
```
php artisan test
```