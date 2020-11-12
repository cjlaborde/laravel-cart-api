### Setting up for testing
1. Create test database
2. Usually you just create a sqlite database is a lot faster
3. Yet is great to have a real database instead
4. in phpunit.xml and add
```php
        <server name="DB_DATABASE" value="cart_testing"/>
```
5.  Now when we run tests we will use cart_testing database
6. code ~/.zshrc
7. `alias phpunit="./vendor/bin/phpunit"`
8. test/TestCase.php
9. For each test that we have
10 . Tests\Feature;
11. We have `use Illuminate\Foundation\Testing\RefreshDatabase;`
12. Add it to the main TestCase.php
```
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;
}
```
13. Delete Feature/ExampleTest.php
14. use phpunit to get green
15. Delete Unit/ExampleTest.php
16. After we write a feature we write test for it.

### Building the category model
1. php artisan make:model -m
2. Create a route in routes/api
3. check `http://cart-api.test/api` in Postman Preview
4. `php artisan make:migration add_order_to_categories_table --table=categories`
5. `php artisan make:test Models\\Categories\\CategoryTest --unit`
6. `php artisan make:factory CategoryFactory`
7. composer require laravel/helpers
8.  Call to a member function connection()
```
https://stackoverflow.com/questions/42512676/laravel-5-unit-test-call-to-a-member-function-connection-on-null
check if the test class is extending use Tests\TestCase; rather then use PHPUnit\Framework\TestCase;
Laravel ships with the later, but Tests\TestCase class take care of setting up the application, 
otherwise models wont be able to communicate with the database if they are extending PHPunit\Framework\TestCase.
```
9. https://github.com/laravel/framework/issues/34209
10. Create traits to reuse code in Categories for other Models

### Category index endpoint
1. php artisan make:controller Categories\\CategoryController 
2. create route in api.php
3. Route::resource('categories', 'Categories\CategoryController');
4. Go to Postman and check the api 
6. http://cart-api.test/api/categories
7. https://dev.to/seankerwin/laravel-8-rest-api-with-resource-controllers-5bok
8. In APi you only need to create 1 route and laravel 8 will handle the rest
9. Route::resource('categories', CategoryController::class);
10. create index() in CategoryController and check the route in Postman
11. Now we going to use Category Resources.
12. php artisan make:resource CategoryResource   

#### Test Category index endpoint
1. php artisan make:test Categories\\CategoryIndexTest
2. phpunit tests/Feature/Categories/

### Simple Products
1. php artisan make:model Product -m
2. php artisan make:test Product\\ProductTest --unit

