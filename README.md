[![Build Status](https://travis-ci.org/jdesrosiers/todo-api.svg?branch=master)](https://travis-ci.org/jdesrosiers/todo-api)

TODO API
========

The TODO API was created to showcase a Hypermedia API.

Development
------------

This API is built using the Resourceful framework.

* `php -S localhost:8000 index.php` - Run the server

### Tests

What, no tests?! This is sacrilege!

Resourceful allows us to declaratively define an API with almost no code. Until
there is need for actual code, we will put off setting up a test framework.

Deployment
----------

Continuous deployment is used for this application. Continuous deployment aims to automate the process of deploying code. Each stage in the process is automated, so that every change that passes all the tests is deployed to production automatically. These are the steps in the process:

* A PR is opened for a branch (feature, bug fix, etc.)
* Travis CI checks the build
* The PR is approved
* The branch is merged into master
* Travis CI deploys the app to Heroku

This app is deployed to: https://hypermedia-todo.herokuapp.com/

**Note**: Deployment should never be done manually. It should only be done through the automated process.
