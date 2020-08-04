<p align="center"><a href="http://actcms.work/actadmin/" target="_blank"><img width="400" src="https://s3.amazonaws.com/thecontrolgroup/actadmin.png"></a></p>

<p align="center">
<a href="https://travis-ci.org/the-control-group/Actadmin"><img src="https://travis-ci.org/the-control-group/actadmin.svg?branch=master" alt="Build Status"></a>
<a href="https://styleci.io/repos/72069409/shield?style=flat"><img src="https://styleci.io/repos/72069409/shield?style=flat" alt="Build Status"></a>
<a href="https://packagist.org/packages/act/actadmin"><img src="https://poser.pugx.org/act/actadmin/downloads.svg?format=flat" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/act/actadmin"><img src="https://poser.pugx.org/act/actadmin/v/stable.svg?format=flat" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/act/actadmin"><img src="https://poser.pugx.org/act/actadmin/license.svg?format=flat" alt="License"></a>
<a href="https://github.com/larapack/awesome-Actadmin"><img src="https://cdn.rawgit.com/sindresorhus/awesome/d7305f38d29fed78fa85652e3a63e154dd8e8829/media/badge.svg" alt="Awesome Actadmin"></a>
</p>

# **V**oyager - The ACT Laravel Admin
Made with ❤️ by [The ACT CMA](http://actcms.work)

![Actadmin Screenshot](https://s3.amazonaws.com/thecontrolgroup/actadmin-screenshot.png)

Website & Documentation: http://actcms.work

Video Tutorial Here: https://laravelactadmin.com/academy/

Join our Slack chat: https://actadmin-slack-invitation.herokuapp.com/

View the Actadmin Cheat Sheet: https://actadmin-cheatsheet.ulties.com/

<hr>

Laravel Admin & BREAD System (Browse, Read, Edit, Add, & Delete), supporting Laravel 5.4, 5.5, 5.6 and 5.7!

## Installation Steps

### 1. Require the Package

After creating your new Laravel application you can include the Actadmin package with the following command: 

```bash
composer require act/actadmin
```

### 2. Add the DB Credentials & APP_URL

Next make sure to create a new database and add your database credentials to your .env file:

```
DB_HOST=localhost
DB_DATABASE=homestead
DB_USERNAME=homestead
DB_PASSWORD=secret
```

You will also want to update your website URL inside of the `APP_URL` variable inside the .env file:

```
APP_URL=http://localhost:8000
```

> Only if you are on Laravel 5.4 will you need to [Add the Service Provider.](https://actadmin.readme.io/docs/adding-the-service-provider)

### 3. Run The Installer

Lastly, we can install actadmin. You can do this either with or without dummy data.
The dummy data will include 1 admin account (if no users already exists), 1 demo page, 4 demo posts, 2 categories and 7 settings.

To install Actadmin without dummy simply run

```bash
php artisan actadmin:install
```

If you prefer installing it with dummy run

```bash
php artisan actadmin:install --with-dummy
```

> Troubleshooting: **Specified key was too long error**. If you see this error message you have an outdated version of MySQL, use the following solution: https://laravel-news.com/laravel-5-4-key-too-long-error

And we're all good to go!

Start up a local development server with `php artisan serve` And, visit [http://localhost:8000/admin](http://localhost:8000/admin).

## Creating an Admin User

If you did go ahead with the dummy data, a user should have been created for you with the following login credentials:

>**email:** `admin@admin.com`   
>**password:** `password`

NOTE: Please note that a dummy user is **only** created if there are no current users in your database.

If you did not go with the dummy user, you may wish to assign admin privileges to an existing user.
This can easily be done by running this command:

```bash
php artisan actadmin:admin your@email.com
```

If you did not install the dummy data and you wish to create a new admin user you can pass the `--create` flag, like so:

```bash
php artisan actadmin:admin your@email.com --create
```

And you will be prompted for the user's name and password.
