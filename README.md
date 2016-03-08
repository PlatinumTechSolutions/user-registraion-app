# User Registration App

[![Build Status](https://travis-ci.org/PlatinumTechSolutions/user-registration-app.svg)](https://travis-ci.org/PlatinumTechSolutions/user-registration-app) [![Coverage Status](https://coveralls.io/repos/github/PlatinumTechSolutions/user-registration-app/badge.svg)](https://coveralls.io/github/PlatinumTechSolutions/user-registration-app)

Symfony 3 app to allow user registration and login

## Create database

```
php bin/console doctrine:schema:create
```

## Create an admin user

```
php bin/console pts:createUser
```
