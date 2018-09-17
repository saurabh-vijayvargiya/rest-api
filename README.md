rest-api is a demo Laravel project used to create RESTful api. The folder structure of the project is -

1. setup.sh - Bash script to setup the project
2. docroot - Laravel project folder
3. laradock - [Laradock](http://laradock.io/) repo to setup docker for Laravel.

There are thre endpoints -

`api/order` - To place an order.

Request (POST): 
```php
[
    'origin' => ["xx.xxx", "xx.xxx"],
    'destination' => ["xx.xxx", "xx.xxx"]
]
```

Response:
```php
[
    'id' => 21,
    'distance' => 132 km,
    'status' => 'UNASSIGN'
]
```
`api/order/:id` - To take the placed order.

Request (PUT):
```php
[
    'status' => 'taken',
]
```
Response:
```php
[
    'status' => 'SUCCESS'
]
```
`api/orders?page=:page&limit=:limit` - To list all orders, it is GET request.

Response:
```php
{
    [
        'id' => 1,
        'distance' => 131 km,
        'status' => 'UNASSIGN'
    ]
    [
        'id' => 2,
        'distance' => 132 km,
        'status' => 'UNASSIGN'
    ]
    [
        'id' => 3,
        'distance' => 133 km,
        'status' => 'TAKEN'
    ]
    .
    .
    .
}
```