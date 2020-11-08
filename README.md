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
