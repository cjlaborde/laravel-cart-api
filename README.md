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
8. `use Tests\TestCase;` instead of `PHPUnit\Framework\TestCase`
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


### Product Index endpoint
1. php artisan make:controller Products\\ProductController
2. php artisan make:resource ProductIndexResource
3. php artisan make:migration add_description_to_products_table --table=products
4. php artisan make:test Product\\ProductIndexTest 
5. php artisan make:factory ProductFactory
6. ProductResource is a standard resource that extends ProductIndexResource
7. php artisan make:resource ProductResource
8. extend class ProductResource extends ProductIndexResource instead of JsonResource
9. Then merge with array_merge  array_merge(parent::toArray($request)
10.  php artisan make:test ProductShowTest 
11. Product = things we Testing | Show = Action we are testing | Test
12. Now we can show list of products and show a product

### Hooking up producta to categories
1. php artisan make:migration create_category_product_table --create=category_product
2. creates belong to many relationship

### Scoping(filtering) products by category
1.  http://cart-api.test/api/products?category=coffee

#### Testing Scoping(filtering) products by category
1. php artisan make:test Products\\ProductScopingTest

### Simple CORS support
> Reason we use CORS is that our API and front end can be in different domains
> so we need to tell the api that request from the 
> front end using a certain domain we specify is allowed

1. https://github.com/fruitcake/laravel-cors
2. composer require fruitcake/laravel-cors
3. in app/Http/Kernel.php
```
protected $middleware = [
  \Fruitcake\Cors\HandleCors::class,
    // ...
];
```
4. php artisan vendor:publish --tag="cors"
5. Now we can add our origins in config/cors.php
6. 'allowed_origins' => ['*'], means anything can access it
7. 'allowed_origins' => ['yoursite.com'], only your site can access it.
8. Remember to add 'paths' => ['api/*'], to allow cors path

### Product variations
1. example with shoes
```
nike air max
    colors
        blue
        black
        white
    sizes
        uk 9
        uk 10
```
2. when he add product to cart what we actually adding is product variation
3. php artisan make:model ProductVariation -m
4. php artisan make:resource ProductVariationResource
5. php artisan make:factory ProductVariationFactory  

### Product variation types
1. php artisan make:model ProductVariationType -m
2. php artisan make:migration add_product_variation_type_id_to_product_variations_table --table=product_variations
3. Adding Product variation
```
  When we use product variation result we saying this->id but that doesn't work since we are trying
  to look for a key on a collection, is not expecting something to be grouped by something else
  What we can do is check, $this->resource and check if it an instance of Collection
  We want to return ProductVariationResource, but we want to return collection for each.
  What is happening here is that if we are grouping items each of them groups will be an individual collection
  with items inside of themselves
 
  What Resource trying to do without this if statement is trying to access $this->id which will not work
  What the if statement does is goes into each of the keys keys that we group by and then go ahead and
  return a collection of products variation
```
4. php artisan make:test Product\\ProductVariationTest --unit
5. php artisan make:factory ProductVariationTypeFactory
