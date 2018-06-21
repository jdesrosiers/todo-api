[![Heroku CI Status](https://ci-badges.herokuapp.com/pipelines/f9c4543a-2d69-4cef-bae9-f86cff384d41/master.svg)](https://dashboard.heroku.com/pipelines/f9c4543a-2d69-4cef-bae9-f86cff384d41/tests)

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
