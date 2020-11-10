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

