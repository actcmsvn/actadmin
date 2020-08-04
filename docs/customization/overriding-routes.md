# Overriding Routes

You can override any Actadmin routes by writing the routes you want to overwrite below `Actadmin::routes()`. For example if you want to override your post LoginController:

```php
Route::group(['prefix' => 'admin'], function () {
   Actadmin::routes();

   // Your overwrites here
   Route::post('login', ['uses' => 'MyAuthController@postLogin', 'as' => 'postlogin']);
});
```

