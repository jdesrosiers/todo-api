[![Build Status](https://travis-ci.org/jdesrosiers/todo-api.svg?branch=master)](https://travis-ci.org/jdesrosiers/todo-api)

TODO API
========

The TODO API was created to showcase a Hypermedia API. This API is built using
the Resourceful framework to declaratively define the API with JSON Hyper-Schema
(draft-04).

Development
------------

```bash
docker-compose up
```

### Tests

```bash
docker-compose run web vendor/bin/phpunit
```

Deployment
----------

The master branch is automatically deployed to: https://hypermedia-todo.herokuapp.com/
