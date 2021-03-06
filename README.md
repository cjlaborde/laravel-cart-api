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

### Fixing failing tests for the product index
1. Verify Tests\Feature\Product\ProductIndexTest::test_it_shows_a_collection_of_products
2. Notice nothing is wrong with test.
3. So we go to postman and check http://cart-api.test/api/products
3. and we see data [] empty even through we do have products
4. reason for this is the Scoper
5. Go to CategoryScope and do a dd('abc')
6. Then send in postman and we see the output 'abc'
7. But our CategoryScope should not be called and run and filter when we calling http://cart-api.test/api/products
8. since the $value going to be null and cause error in test
9. We going to fix issue inside Scope.php we want to skip filtering by things that don't exist within the query string
10. Create function in Scoper.php called limitScopes
11. return a limited collection of scopes
12. Base on what is on the query string
13. Then add the protected function foreach ($this->limitScopes($scopes) as $key => $scope) {
14. Now we limiting the scopes and not running the apply filtering if we don't need to on the test.

### Qucik scoper trait refactor
1. move Model Product.php methods to a trait
2. So you can reuse it for other Models

### Product Prices
1. https://github.com/moneyphp/money
2. composer require moneyphp/money
3. On front end when it says get new cart total
4. We not going to do any calculation on the client side
5. We going to do all the adding up different prices, adding on shipping
6. All that kind of stuff in API itself
7. All we need to output in the API is the formatted price
8. public function formattedPrice() will not work
9. So you need to add public function getFormattedPriceAttribute() so we pull out dynamically
10. in HasPrice trait
11. When we try to Access Price attribute
12. It will automatically give us Custom Money class

### Product variation prices
1. Product variation can have different price
2. Key here is that if the product variation doesn't have a price
3. It needs to inherits it from the base point product the default price in products table
4. if price doesn't exist from variation we need to inherit it from the parent product
5. We need to overwrite price attribute from HasPrice.php
```php
    public function getPriceAttribute($value)
    {
        // original value we have in database
        // When we try to Access Price attribute
        // It will automatically give us Custom Money class
        return new Money($value);
    }
```
6. We overwrite since we want to target to specifit product related
```php
    public function getPriceAttribute($value)
    {
        if ($value === null) {
            // will return Money instance since you are using Attribute
            return $this->product->price;
        }
        return new Money($value);
    }
```
#### Check if price varies for product variation
1. when price varies is true it means the price is different from default cost.
2. So we add new price_varies attribute to the product data result
3. http://cart-api.test/api/products/coffee

#### Testing Product variation prices
1. If product variation doesn't have a price
 it inherits price from the parent
2. Check if price varies

### Product stock blocks
1. We are not attaching stock to products, we are to products_variations
2. We will build a stock ordering table will check which product have been ordered
3. Will give you dynamic value for the stock that you have
4. php artisan make:model Stock -m
5. php artisan make:factory StockFactory

### Basics orders to test stocks
1. Order table will be able to create orders for particular user link this in to proper variation in order.
2. From that we can deduce how much stock we actually have based on the quantities that have been ordered
3. php artisan make:model Order -m
4. Create pivot table that tells us how much been ordered
5. php artisan make:migration create_product_variation_order_table --create=product_variation_order
6. How orders work
7. We take cart content of the user cart place them into product_variation_order
8. Then create order when it been successful

### Product variation stock checks (Views Dynamic Database table)
1. Use ""SQL Query"" to create a view
```sql
create view products_view as
	select * from products
```
2. cart/Views/products_view in database
3. when we create a new product in product table, the products_view will give us the updated version.
4. delete products_view since this was an example
5. The view we going to create will be a list of all the products variations and what the current stock is
6. with the sum of the stocks(table) subtracted from the order quantity(product_variation_order) that have been made
7. As well boolean flag to check if this is out of stock or in stock.
8. Just so we have it in the table, we don't need to represent this in code
9. We want to drop VIEW if exist since we want to gradually 
10. We are selecting all the products_variations
```sql
drop view if exists product_variation_stock_view;

create view product_variation_stock_view as
	select product_variations.product_id as product_id
	from product_variations
```
11. We get all the products id from the product_variations table
```sql
drop view if exists product_variation_stock_view;
create view product_variation_stock_view as
	select 
		product_variations.product_id as product_id,
		product_variations.id as product_variation_id
	from product_variations
```
12. Now we will use join
13. Will use (id) as the primary
```sql
drop view if exists product_variation_stock_view;
create view product_variation_stock_view as
	select 
		product_variations.product_id as product_id,
		product_variations.id as product_variation_id
	from product_variations
	left join (
		select stocks.product_variation_id as id
		from stocks
		group by stocks.product_variation_id 
	) as stocks using (id)
```
14. sum(stocks.quantity) as stock will give us the total for this particular variation
15. then group it by the variation id
```sql
DROP VIEW IF EXISTS product_variation_stock_view;
CREATE VIEW product_variation_stock_view AS
	SELECT 
		product_variations.product_id AS product_id,
		product_variations.id AS product_variation_id,
		SUM(stocks.quantity) AS stock
	FROM product_variations
	LEFT JOIN (
		SELECT stocks.product_variation_id AS id,
		SUM(stocks.quantity) AS quantity
		FROM stocks
		GROUP BY stocks.product_variation_id 
	) AS stocks USING (id)
	group by product_variations.id
```
16. Now we can test it by adding another 100 in stock table
17. Now we give refresh to product_variation_stock_view and see 200
18. if stock none existing or we don't have any stock
19. We want this to represent 0
20. For example create another stocks table item and have 0 quantity
21. We will see is 0 in product_variation_stock_view
22. But if we don't add stock 0 we going to see null value
23. To resolve this we going to use COALESCE to return 0 as default
```sql
		coalesce(SUM(stocks.quantity), 0) AS stock
```
24. Now we join the product_variations_order table since it has the quantity we can substract from total amount of stock
25. We will use JOIN for this
```sql
	left join (
		 select
		 	product_variation_order.product_variation_id as id,
		 	SUM(product_variation_order.quantity) as quantity
		 from product_variation_order
		 group by product_variation_order.product_variation_id
	) as product_variation_order using (id)
	group by product_variations.id
```
26. Substract the total when another user makes a purchase
```sql
    coalesce(SUM(stocks.quantity) - SUM(product_variation_order.quantity), 0) AS stock
```
27. Now we can test it by adding more stock in stocks table
28. Then again by adding in product_variation_order so that it substract from the total stock in product_variation_stock_view
29. Now we going to add default 0 if (product_variation_order doesn't exist
```sql
	coalesce(SUM(stocks.quantity) - coalesce(SUM(product_variation_order.quantity), 0), 0) AS stock
```
30. Now try doing same by adding a new stock item

#### Now add boolean with true or false if item on stock or not
1. We will do it in SQL editor by using case value and check if greater than > 0 then we represent it as true value
2. else represent a false value
3. Now we can see the in_stock column in product_variation_stock_view
```sql
		coalesce(SUM(stocks.quantity) - coalesce(SUM(product_variation_order.quantity), 0), 0) AS stock,
		case when COALESCE(SUM(stocks.quantity) - coalesce (sum(product_variation_order.quantity), 0), 0) > 0
			then true
			else false
		end in_stock
```
4. Now we test it by making an order in product_variation_order for 50 which will turn in_stock to false
5. What we can do now is create a migration for this

#### Now we will convert the product_variation_stock_view into a migration
1. We will convert this view into a migration.
```sql
DROP VIEW IF EXISTS product_variation_stock_view;
CREATE VIEW product_variation_stock_view AS
	SELECT 
		product_variations.product_id AS product_id,
		product_variations.id AS product_variation_id,
		coalesce(SUM(stocks.quantity) - coalesce(SUM(product_variation_order.quantity), 0), 0) AS stock,
		case when COALESCE(SUM(stocks.quantity) - coalesce (sum(product_variation_order.quantity), 0), 0) > 0
			then true
			else false
		end in_stock
	FROM product_variations
	LEFT JOIN (
		SELECT stocks.product_variation_id AS id,
		SUM(stocks.quantity) AS quantity
		FROM stocks
		GROUP BY stocks.product_variation_id 
	) AS stocks USING (id)
	left join (
		 select
		 	product_variation_order.product_variation_id as id,
		 	SUM(product_variation_order.quantity) as quantity
		 from product_variation_order
		 group by product_variation_order.product_variation_id
	) as product_variation_order using (id)
	group by product_variations.id
```
2. `php artisan make:migration product_variation_stock_view`
3. We will add it using DB::statement()
```php
        DB::statement("
            CREATE VIEW product_variation_stock_view AS
            SELECT
                product_variations.product_id AS product_id,
                product_variations.id AS product_variation_id,
                coalesce(SUM(stocks.quantity) - coalesce(SUM(product_variation_order.quantity), 0), 0) AS stock,
                case when COALESCE(SUM(stocks.quantity) - coalesce (sum(product_variation_order.quantity), 0), 0) > 0
                    then true
                    else false
                end in_stock
            FROM product_variations
            LEFT JOIN (
                SELECT stocks.product_variation_id AS id,
                SUM(stocks.quantity) AS quantity
                FROM stocks
                GROUP BY stocks.product_variation_id
            ) AS stocks USING (id)
            left join (
                 select
                    product_variation_order.product_variation_id as id,
                    SUM(product_variation_order.quantity) as quantity
                 from product_variation_order
                 group by product_variation_order.product_variation_id
            ) as product_variation_order using (id)
            group by product_variations.id
        ");
```
4. Now we create the down() to drop the table
```php
    public function down()
    {
        DB::statement("DROP VIEW IF EXIST product_variation_stock_view");
    }
```
5. Now we delete the product_variation_stock_view since it should not be there
6. php artisan migrate
7. Now where ever our project go we have up to date dynamic stock information that tell us if particular product is in stock

### Product variations is out of stock
1. Create stock() method in ProductVariation
2. what we want to get back from this relationship is a product variation instance
3. we not interested in the product variation what we are interested is the pivot information the stock
4. Reason we use belongsToMany is that we can access that pivot information

### Base product stock information
1. sum up each of the product variations in Product.php called stockCount()

### JSON response profiling (using laravel-debugbar)
1. Add some profiling to see within our JSON response to see how many queries we are running
2. We more than likely end up with problems with the relationships
3. To do this we will install https://github.com/barryvdh/laravel-debugbar
4. `composer require barryvdh/laravel-debugbar --dev`
5. We will add some middleware which will add on this debug information to our json end point
6. php artisan make:middleware ProfileJsonResponse
7. We can use this to output any information inside Json response
8. in ProfileJsonResponse fill the handle test it works with dd('works') in Postman
9. Then fill the handle properly with  $request->has('_debug')
10. Check with http://cart-api.test/api/products/coffee?_debug
11. In postman   "nb_statements": 20, is how many statements been run
12.In here we can check for SQL statement that running to look for any duplication
13. To check if you have an n + 1 problem
14. Duplicate a stock in stocks table
15. Duplicate row in product_variation
16. Then check in postman to see   "nb_statements": 23, incremented
17. Problem is we should not have extra queries as we add extra records
18. In ProductController show method, added ` $product->load(['variations', 'variations.type']);`
19. To reduce  "nb_statements" by using load
19. This will reduce  "nb_statements": 17 since we don't have to iterate over each one.
20. The other thing we need to take into account is th stock
21. $product->load(['variations', 'variations.type', 'variations.stock']);
22. Remember we have relationship set up for stock so we need to pull that in as well.
23. It will reduce "nb_statements": 11 again
24. We can take out variations since that already been accessed.
25. Go to Product.php model to see what we are pulling in
26. Then go to ProductVariation.php and see if there anything here
27. Then scroll down in postman and see each of the queries that were made
28. We notice we have multiple request to our products table
29.  "sql": "select * from \"products\" where \"products\".\"id\" = 1 limit 1",
30. Lets add product as well $product->load(['variations.type', 'variations.stock', 'variations.product']);
31. "nb_statements": 5 was reduced again
32. Now lets check products page http://cart-api.test/api/products?_debug in postman
33. We have "nb_statements": 11 queries we are executing
34. We can do a search with postman of the queries we are executing "sql"
35. we have product_variations stock so it looks like we should be loading that stock
36. ProductController index() method '$products = Product::withScopes($this->scopes())->paginate(10);'
37. We should see a reduce "nb_statements": 4
38. You will notice with these changes it get a lot faster and respond time gets a lot quicker as well.

### Setting up Authentication (jwt-auth)
1. https://github.com/tymondesigns/jwt-auth
2. composer require tymon/jwt-auth
3. php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
4. php artisan jwt:secret 
5. will be used to decoding and encoding the payload
6. config/jwt.php
7. Secret placed on 'secret' => env('JWT_SECRET'), 
8. You can check it has been added JWT_SECRET in .env
9. We will change The Time Live TTL 'ttl' => env('JWT_TTL', 60), to a higher value
10. JWT_TTL=3000 
11. Go to config/auth.php and change 'guard' => 'web',
12. to api 'guard' => 'api',
13. Then change 'api' => [ 'driver' => 'token' 
14. Change it to jwt 'api' => [ 'driver' => 'jwt'
15. Implement the JWTSubject to user model
16. class User extends Authenticatable implements JWTSubject

### Registering a user
1. truncate user table since it doesn't have a hash password
2. in dbeaver left click user table > tools > truncate > checkmark Cascade|Restart identity > start
3. php artisan make:controller Auth\\RegisterController
4. http://cart-api.test/api/auth/register
5. Laravel 7 had update where the `Route::post('register', 'Auth\RegisterController@action');` does not work anymore
6. To update it check this solution
7. `https://stackoverflow.com/questions/57865517/laravel-6-0-php-artisan-routelist-returns-target-class-app-http-controllers-s`
8. We later going to create an observer inside the user model.
9. So that everytime we create new user it automatically hashes the password
10. Create user using postman click on Body > form-data > fill (email, password, name)
11. Now we will crease a Resource for the endpoint we will create in future to gather all the information from a particular user
12. When we build any application we can create Private and Public user resource
13. PrivateUserResource will only return when is that actual user that requested that information
14. PublicUserResource will be public for all users to see. For example a review for a product, if you implemented reviews
15. You will a PublicUserResource that don't contain the user email and that other private information
16. php artisan make:resource PrivateUserResource
17. php artisan make:request Auth\\RegisterRequest 
18. Has to be unique on users table under email unique:users,email
19. In newer Laravel you get a 404 page instead of JsoUn error
20. To fix it in Postman click on Headers and write  Key: Accept Value: application/json
21. Has to be unique on unique email from the users table unique:users,email

#### Testing: Registering a user
1. This is suppose to be a feature test but we can get away with unit test for our user
2. php artisan make:test Models\\Users\\UserTest --unit
3.  php artisan make:test Auth\\RegistrationTest   

### Authenticating a user
1. php artisan make:controller Auth\\LoginController
2. Reason we use action because is makes thing more tidy when you use a controller for a single thing
3. 422 is validation error
 4. php artisan make:request Auth\\LoginRequest
5. php artisan make:test Auth\\LoginTest

### The me endpoint
1. http://cart-api.test/api/auth/me
2. php artisan make:controller Auth\\MeController
3. http://cart-api.test/api/auth/login and get the token
4. then use the token in http://cart-api.test/api/auth/me
5. Click on Authorization tab, TYPE Bearer Token and paste token
6. We don't want user to get access to MeController if they not authenticated
7. So we use a __construct or middleware at the route
```php
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }
```
8. This means that if we try to access http://cart-api.test/api/auth/me
9. We get  "message": "Unauthenticated."
10. php artisan make:test Auth\\MeTest
11. Lets take a look at use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
12.  click on Concerns\MakesHttpRequests,
13. then we find     public function json($method, $uri, array $data = [], array $headers = [])
14. As you can see is almost same signature we used only difference is we are also accepting that user here.
15. All we need to do is recall the method and also pass the header with barren token for that particular user.

### The user cart relationship
1. php artisan make:migration create_cart_user_table --create=cart_user
2. you have to have each item in alphabetic order cart_ and users

#### Testing: The user cart relationship
1. Undefined table: 7 ERROR:  relation "product_variation_user" does not exist
2. to fix this error we can rename it in cart() method in User.php
3. `return $this->belongsToMany(ProductVariation::class, 'cart_user')->withPivot('quantity');`
4. Another error `Not null violation: 7 ERROR:  null value in column "quantity" violates not-null constraint`
5. To fix it  php migrate:rollback then set a default of 1
6. `$table->integer('quantity')->unsigned()->default(1);`

### Adding items to the cart
1. Goal for this part is an endpoint that allow us to specify within a json payload a list of products we want 
to add or product variations along side the quantities.z
2. We going to allow for multiple products to be added at once which is very important
3. In postman `http://cart-api.test/api/cart`
4. Click on Body and remove all columns we sending through like email, name and password
5. Click on raw radio button and choose JSON(application/json)
6. Then in raw write
7. Lets say that for example on the client side you want to allow a guest to start adding items to their cart before they checkout
8. When they checkout you want to create user account for them and then you want to add list of products to their cart
9. Actually store them and persist them into the database
10. Now if you were creating an end point like this `http://cart-api.test/api/cart` posting through a single product variation id in a single quantity
11. Lets say user added 20 item to that cart, what you will have to do is do 20 different http request to our api
12. Add each of the items they have stored on the front end
13. While if you allow multiple products to start with that means if you say on the client side storing them within a session in local storage for example
14. Then you can make 1 single request for all of the products then is just 1 http request
15. So is important to add multiple products to be added
```json
{
    "products": [
        { "id": 1, "quantity": 1 },
        { "id": 2, "quantity": 1 }

    ]
}
```
16.  php artisan make:controller Cart\\CartController 
17. Login with Post man and get the token
18. Remember to add token to Authentication Bearer Token
19. Alternative you can add token to postman via an environment persist accross all the request.
20. Next thing that is important is validate each of the products
21. This is treacky since now we have json payload and we want to validate each of the products
22. For example check if each of the id actually exist.
23. php artisan make:request Cart\\CartStoreRequest
24. add ->withTimestamps to add date as well. in cart() method in User.php model

#### Testing: Adding items to the cart
1. php artisan make:test Cart\\CartTest --unit
2. php artisan make:test Cart\\CartStoreTest 
3. you can use dd($response->getContent()); to see output from the failure in the terminals
4. To see that the issue with test was that in AppServiceProvider we taking a Cart and returning a new user
5. To fix it go to Cart Controller and mode Cart $car from controller into the store method instead

### Incrementing quantity when 
1. Everything we add a product we want to increase quantity in the cart
2. To do this we create method getCurrentQuantity() in Cart.php

### Updating product cart quantities
1. Duplicate in postman the tab for http://cart-api.test/api/cart 
2. then add the product number http://cart-api.test/api/cart/3
3. Now in Body tab move from raw to x-www/form-uriencoded
4. Then key > quantity and value > 5
5. then see the routes with php artisan route:list and see the parameter is http://cart-api.test/api/cart/{cart}
6. Then we want to rewrite the route parameters
```php
Route::resource('cart', CartController::class, [
    // paramenters we want to overwrite
    'parameters' => [
        'cart' => 'productVariation'
    ]
]);
```
7. Then we check again the routes with php artisan route:list
8. Now for the cart we accepting productVariation instead http://cart-api.test/api/cart/{productVariation
9. In postman switch POST to Patch
10. now you can update quantity with x-www/form-uriencoded 

#### Testing: Updating product cart quantities
1. php artisan make:request Cart\\CartUpdateRequest
2. php artisan make:test Cart\\CartUpdateTest
3. We doing testing in Step proccess 1) test_it_fails_if_unauthenticated 2) test_it_fails_if_product_cant_be_found()
4. Had error since I mispelled required in CartUpdateRequest `'quantity' => 'requires|numeric|min:1'` 

### Deleting cart products
1. You can delete by creating a delete mething inside of Cart.php that will detach $this->user->cart()->detach($productId);

#### Testing: Deleting cart products
1. php artisan make:test Cart\\CartDestroyTest

### Emptying the cart
1. Create method empty() in Cart.php `$this->user->cart()->detach();`

### Getting the user's cart
1. Duplicate http://cart-api.test/api/cart/3 tab
2. Then set it to GET request to http://cart-api.test/api/cart
3. Need to Create index method for the endpoint to work
4. Create resource for index() method to use
5. php artisan make:resource Cart\\CartResource
6. Send POST to http://cart-api.test/api/cart make sure to log in and get token in Authorization
7. then send a GET request to http://cart-api.test/api/cart to see the product
8. We also need Base product it belongs to
9. is not enough to say 250g since this is just a product variation
10. What we can do is not reusing a ProductVariationResource since inside it we don't show the product
11. php artisan make:resource Cart\\CartProductVariationResource
12. we can now extend the ProductVariationResource
13. We can use array_merge to make it more flexible without having lots of things all over the place
14. Now you can get access to Base Product and the other Variation Data in CartProductVariationResource
15. Now since we have access to the Pivot as well we want show quantity
16. We can change total to test it on the cart_user table
17. Now using total we can get the total price of the quantity
18. `$total = new  Money($this->pivot->quantity * $this->price->amount());`
19. Now remember to use _debug to see how many query request we getting `http://cart-api.test/api/cart?_debug`
20. Now you can see the total queries to be "nb_statements": 11,
21. Now test if we add more products using postman
22. http://cart-api.test/api/cart Body raw
```php
{
    "products": [
        { "id": 3, "quantity": 1 }

    ]
}
```
23. Change it to another product
```php
{
    "products": [
        { "id": 4, "quantity": 5 }

    ]
}
```
24. Now you can see that it increased "nb_statements": 20,
25. We can add in index() CartController a load to reduce nb_statements
26. we try $request->user()->load('cart'); and then check Postman http://cart-api.test/api/cart?_debug
27. To Realize that it doesn't work  so we instead use  $request->user()->load('cart.products');
28. http://cart-api.test/api/cart?_debug
29. "nb_statements": 12
30. Since we are dealing with things like the stock  $request->user()->load('cart.product.variations.stock');
31. http://cart-api.test/api/cart?_debug and see it was reduced as well  "nb_statements": 7
32. Lets now add $request->user()->load('cart.product.variations.stock', 'cart.stock');
33. now sql statement were reduced as well "nb_statements": 6 
34. You can check for more searching for "sql"

### Testing: Getting the user's cart
1. php artisan make:test Cart\\CartIndexTest
2. An alternative is create  API test in isolation and not much in feature test
3. since feature test, is just seeing that we can access the endpoint that we can generally see that information
4. You can also create a protected function in ProductVariationResource called getTotal to make code more organized

### Checking if the cart is empty
1. Create method isEmpty inside Cart.php
2. You could use `return $this->user->cart->count(); // 0` but you will have problems when item goes out of stock.
3. So is better to do a sum instead since it will be more accurate and avoid this issue. 
4. Sum up the items quantity in the cart and not actually the items that are in the cart
5. Go to Postman and sign up `http://cart-api.test/api/auth/login` and get the token
6. then `http://cart-api.test/api/cart` and click authentication tab and paste the token in as Bearer token
### Getting the cart totals

### Syncing the cart (Never Order more than the current stock)
1. Send a Get request with postman to http://cart-api.test/api/cart
2. You can now add items to cart_user then add products in stock.
3. Make sure there are no orders in product_variation_order
4. Now it give return the maximum amount of the current stock if your cart quantity is over that limit
5. update pivot: it will change cart stock amount to the available stock amount when you add more items than currently in stock
6. We also need to tell users if this change have happened to their cart
7. Create    protected $changed = false;
8. `$this->changed = $quantity != $product->pivot->quantity`
9. Then create method called hasChanged() that gets the changed value
10. This can be used if user tries to modify to purchase 200 items when that is not even an option
11. Also useful when you want to stop people ordering more stock that is available

#### Testing: Syncing the cart (Never Order more than the current stock)


### Testing minimum stock

### Showing the product variation type
1. See the type in Postman `http://cart-api.test/api/cart`

### SQL optimizations with laravel debugbar
1. Lets check our endpoints with debugbar
2. `http://cart-api.test/api/cart?_debug` "nb_statements": 9,
3. Lets reduce number by going to CartController.php
4. Do a search for "sql" in Postman to see the common queries
5. We can see that grabing stock count for product variation from the stock view we created is causing some problems
6. It seems the problem is cased in Cart.php in sync() method
7. We can add return in the start of method to see if is the one causing problems then count the amount of nb_statements
8. and we see the number was reduced
9. This is problem since we are calling the minStock method over in the productVariation
10. As well minStock use the stockCount which require the information of the user as we use this
11. we can fix this by doing the ego loading somewhere else perhaps service provider
12. In AppServiceProvider register() we going to load what we need
13. Now we check `http://cart-api.test/api/products/coffee?_debug` then do a search of "sql"
14. Seems it looks great so we test another link
15. `http://cart-api.test/api/products?_debug`
16. This will help your page load faster when we reload page
17. No matter the amount of items, we want it to load very quickly

### Countries table
1. `php artisan make:model Country -m`
2. create a seeder to populate the table
3. php artisan make:seeder CountriesTableSeeder
4. there was error with seeder since you didn't set timestamps to fix it
5. Go to the model Country.php and set $timestamps = false;

### Addresses setup
1. php artisan make:model Address -m
2. php artisan make:factory AddressFactory
3. php artisan make:factory CountryFactory
4. php artisan make:test Models\\Addresses\\AddressTest --unit

### Listing Addresses
1. `php artisan make:controller Addresses\\AddressesController`
2. `php artisan make:resource AddressResource`
3. `php artisan make:resource CountryResource`

#### Testing: Listing Addresses
1. `php artisan make:test Addresses\\AddressIndexTest`

### Storing an address
1. Create store() Method in AddressController.php
2. Send a Post request to `http://cart-api.test/api/addresses`
3. Then click on Body Tab to send data
4. `php artisan make:request Addresses\\AddressStoreRequest`

#### Testing: Storing an address
1. `php artisan make:test Addresses\\AddressStoreTest`

### Toggling default addresses
1. We need to toggle default address when user has multiple addresses
2. `php artisan make:migration add_default_to_addresses_table --table=addresses`
3. We going to add a default column to the address table
4. Create static boot method on the Address.php model and do a dd($address)
5. Then go to postman and send s POST to `http://cart-api.test/api/addresses`
6. We don't have default since we have not set it on fillable
```php
    protected $fillable = [
        'name',
        'address_1',
        'city',
        'postal_code',
        'country_id',
        'default'
    ];
```
7. After sending POST we see is a string instead of Boolean so we create a setDefaultAttribute() method in Address method
8. Create if statement that checks if user has a default address already and set all the newly created address default to false.
9. Use Postman now and send 2 POST request to `http://cart-api.test/api/addresses` and first address should be default true and others false

#### Testing: Toggling default addresses

### Countries endpoint
1. `php artisan make:controller Countries\\CountryController`
2. check `http://cart-api.test/api/countries` in postman to see all the countries

#### Testing:  Countries endpoint
1. `php artisan make:test Countries\\CountryIndexTest`

### Creating shipping methods
1. `php artisan make:model ShippingMethod -m`

#### Testing: Creating shipping methods
1. `php artisan make:test Models\\ShippingMethods\\ShippingMethodTest --unit`
2. `php artisan make:factory ShippingMethodFactory`

### Hooking up shipping methods to countries
1. `php artisan make:migration create_country_shipping_method_table --create=country_shipping_method`
2. `php artisan make:test Models\\Countries\\CountryTest --unit`

### Getting the right shipping methods for an address
1. `php artisan make:controller Addresses\\AddressesShippingController`
2. Create route that requires user to be logged in, in api.php
3. use postman and send a GET request to `http://cart-api.test/api/addresses/4/shipping`
4. Make sure you get a valid address id in address table
5. You need to add the auth in the Controller, since it didn't work on the api route
   ```php
   public function __construct()
   {
        $this->middleware(['auth:api']);
   }
```
6. `php artisan make:resource ShippingMethodResource`
7.  Only see shipping methods available for our own addresses
8. To resolve this make a policy
9. `php artisan make:policy AddressPolicy`
10. Then connect it on AuthServiceProvider.php
```php
    protected $policies = [
         'App\Models\Address' => 'App\Policies\AddressPolicy',
    ];
```
11. Now add the policy to the method
```php
    public function action(Address $address)
    {
        // only see shipping methods available for our own addresses
        $this->authorize('show', $address);

        return ShippingMethodResource::collection(
            $address->country->shippingMethods
        );
    }
```
12. In the policy check if id match the currently logged user id
```php
    public function show(User $user, Address $address)
    {
        return $user->id == $address->user_id;
    }
```
13. Now send with postman GET request `http://cart-api.test/api/addresses/4/shipping`
14. You can use this with the checkout where we select an address and then recheck which shipping methods are available for that particular address.

### Testing: Getting the right shipping methods for an address
1. `php artisan make:test Addresses\\AddressShippingTest`
```php
        $address = Address::factory()->create([
            'user_id' => $user-id,
            // we going to check that when we add shipping method to this country, that we are adding for this user address
            // wrap the country definition so we have access to that entire country not just the id
            'country' => ($country = Country::factory()->create())->id
        ]);
```
2. You can also remove assertion to check if everything working correctly
3. Unable to find JSON fragment: [{"id":1}] means you need to add Id to the ShippingMethodResource

### Adding Shipping onto the subtotal
1. use postman and send a GET method to `http://cart-api.test/api/cart?shipping_method_id=1`
2. You also later add validation so that it doesn't accept none existing shipping_method_id

#### Testing: Adding Shipping onto the subtotal
1. Create test for money class
2. php artisan make:test Money\\MoneyTest --unit

### Adding address and shipping method relation to orders
1. `php artisan make:migration add_address_and_shipping_to_order_table --table=orders` 


#### Testing: Adding address and shipping method relation to orders
1. Make test to check relationships between models
2. `php artisan make:test Models\\Orders\\OrderTest --unit`
3. `php artisan make:factory OrderFactory`

### Order statuses and defaults
1. We going to use const in the model which is an alternative to using ENUMS in the database, since they can be restricting when you want to update them.
```php
    // we can also show these in a status if we want to.
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const PAYMENT_FAILED = 'payment_failed';
    const COMPLETED = 'completed';
```
2. php artisan make:migration add_status_to_orders_table --table=orders
3. set the boot() method to set our default state pending

#### Testing: Order statuses and defaults

### Basic order validation
1. `php artisan make:controller Orders\\OrderController`
2. Validation is is very important for a few reasons
3. Lets say we create order with an address We need to know that the address for an order belongs to the user 
   Otherwise someone can create order and ship it to any address which is not a great idea
4. Second we need to make sure the shipping_method_id we been using is valid as well
5. For example if I am from UK I can easily find out the shipping_method_id  and switch it over
6. We need to check that the shipping method for this order is valid for the address that is being used for this order
7. We going to create a basic validation then create a more complex custom validation rule that will work for our valid shipping method
8. `http://cart-api.test/api/orders`
9. create request to add all of our order validation rules   
10. `php artisan make:request Orders\\OrderStoreRequest`
11. In OrderStoreRequest to get access to currently signed in user
12. in FormRequest `class OrderStoreRequest extends FormRequest` we follow it
13. use Illuminate\Foundation\Http\FormRequest; and see that it extend our base Request
14. `class FormRequest extends Request implements ValidatesWhenResolved`
15. We can get our user so all we have to do is $this->user()->id
16. Error: Laravel action is not authorized to fix it remember to set authorize to true in the request OrderStoreRequest
```php
    public function authorize()
    {
        return true;
    }
```
17. In Postman go to Body and add address_id to send make sure is a valid one currently in the database

#### Testing: Basic order validation
1. php artisan make:test Orders\\OrderStoreTest
2. We don't test for error message since then it would make our test very fragile when we change error message
3. Instead we look for `->assertJsonValidationErrors(['shipping_method_id']);`

### Custom shipping method validation rule
1. php artisan make:rule ValidShippingMethod


### Creating an order
1. `php artisan make:migration add_subtotal_to_orders_table --table=orders`

#### Testing: Creating an order
1. `test_it_can_create_an_order()` in OrderStoreTest going to be a complex test that is going to need a protected function to work
2. Create the protected function orderDependencies(User $user)

### Revisiting orders and product relations

### Fixing cart store failling test

### Attaching products when ordering
1. In postman send `http://cart-api.test/api/orders`
2. Send a POST with postman to `http://cart-api.test/api/cart`
3. Then on Body > raw
```js
{
    "products": [
        { "id": 2, "quantity":2 }
    ]
}
```

### Refactoring to a custom collection
1. dd(get_class($cart->products())); get_class is used to not get too much output just the class `use Illuminate\Database\Eloquent\Collection;`
2. We going to modify and extend the Collection with custom one.
3. In ProductVariation create a new newCustomCollection method
4. Which will be Custom Collection that extend base laravel collection
3. create class that extends collection there called ProductVariationCollection.php
4. Now dd(get_class($cart->products())); and see `"App\Models\Collections\ProductVariationCollection"` instead
5. create a forSyncing method in ProductVariationCollection
6. So you can use it here `$order->products()->sync($cart->products()->forSyncing());`

#### Testing: Refactoring to a custom collection
1. `php artisan make:test Collections\\ProductVariationCollectionTest --unit`

### Falling if the cart is empty
1. We don't want order created if they don't have any product attached to them.
2. delete tables data a) product_variation_order b) orders c) cart_user
3. In postman do GET request to `http://cart-api.test/api/cart`
4. we also have in Cart the isEmpty method which not only checks if there are no products
5. Checks if quantity have been reduce as part of not being available
6. So we can use isEmpty again in store method to check if our cart is empty
7. Lets try first to check how we can create empty order before we implement the if statement on store method
8. in postman send POST request to `http://cart-api.test/api/orders`
9. It does work but is an useless empty order

### Emptying the cart when ordering
<https://laravel.com/docs/8.x/events>
1. Create an event that process the payment first of all and then empty the cart in the OrderController.php store() method
2.  Go to EventService.php and add the OrderCreated and EmptyCart paths so you can generate them later
3. `php artisan` look for event section
4. We going to use `  event:generate  Generate the missing events and listeners based on registration`
5. `php artisan event:generate`
6. add even to OrderController store method
7. Send POST request to `http://cart-api.test/api/cart`
8. Send GET request to `http://cart-api.test/api/cart` See items in cart
9. Send a POST request to `http://cart-api.test/api/orders` to create order
10. Send GET request to `http://cart-api.test/api/cart` to see that cart products [] is empty again

### Returning order details
1. `php artisan make:resource OrderResource`
2. We don't have anything in cart so we need to make order with postman
3. Send POST to `http://cart-api.test/api/cart` to add item to cart
4. send POST to `http://cart-api.test/api/orders`
5. To test things out in store() method in Returning order details
6. comment these lines of code
```php
    public function store(OrderStoreRequest $request, Cart $cart)
    {
//        if ($cart->isEmpty()) {
//            return response(null, 400);
//        }

        $order = $this->createOrder($request, $cart);
        $order->products()->sync($cart->products()->forSyncing());
        
//        event(new OrderCreated($order));
        return new OrderResource($order);
    }
```
7. With Postman send a POST request to `http://cart-api.test/api/cart` to add item in cart
8. Then send POST to `http://cart-api.test/api/orders` to make order multiple times to see the id increment
9. This way we test we getting the right response back

### Fixing up failing order test
1. This error is happening because we have an empty table once we creating this test
2. What causing this in store() method we have an if ($cart->isEmpty()) that prevent ordering from happening if cart is empty
3. The problem is the test not actual app.
4. After we added the if statement to prevent ordering from happening if cart is empty
5. Since previously when we wrote this tes to create an order we were not thinking  about that
6. So we make sure we have a list of products in our cart with stock
```php
        $user->cart()->sync(
            $product = $this->productWithStock()
        );
```

### Warning users of cart changes, plus some refactoring (Fix minus quantity issue)
1. We going to show warning to the user if the item they trying to order suddenly ends up out of stock.
2. In postman Add item to your cart by sending Post to `http://cart-api.test/api/cart`
3. Then send a GET request with postman `http://cart-api.test/api/cart`
4. Then we see we added id: 4 which is the product_variation_id
5. Then go to stocks database and set product_variation_id: 4 quantity to 0
6. Then we send GET request with postman `http://cart-api.test/api/cart` which is going to sync our cart
7. Now we go and see product_variation_order table and see there is quantity of 1 yes we know we don't have that quantity available.
8. What we need to do is go to CartController and see what we do is $cart->sync();
9. What we going to do is in OrderController.php sync the cart as we sync the order. adding `$cart->sync();`
10. In postman Add item to your cart by sending Post to `http://cart-api.test/api/cart`
11. Then send a GET request with postman `http://cart-api.test/api/cart` and see that quantity is -2 so is not working
12. Now lets delete all in product_variation_order table
13. Delete all in orders table
14. Now send POST request to `http://cart-api.test/api/orders` now we see it doesn't work and we have  "subtotal": "-7500"
15. The issue seems to be with Cart.php isEmpty()
```php 
    public function isEmpty()
    {
        return $this->user->cart->sum('pivot.quantity') === 0;
    }
```
16. Instead lets change it to <= 0
```php  
    public function isEmpty()
    {
        return $this->user->cart->sum('pivot.quantity') <= 0;
    }
```
17. Now run phpunit to see we didn't break anything for changing the code
18. Now lets delete all in product_variation_order table
19. Delete all in orders table
20. We should not create any of these with minus quantity
21. In postman Add item to your cart by sending Post to `http://cart-api.test/api/cart`
22. Then send a GET request with postman `http://cart-api.test/api/cart`
23. Now we should have quantity of 0
24. Now try to make order with `http://cart-api.test/api/orders` and see that you get a Status: 400 Bad Request
26. In postman Add item to your cart by sending Post to `http://cart-api.test/api/cart`
27. Then send a GET request with postman `http://cart-api.test/api/cart`
28. Make sure there is nothing in orders table and
29. Then set stop to 1 again to cart and  again with `http://cart-api.test/api/orders`
30. Now since you using $cart->sync() in different places you don't want to have it in controller
31. Instead extact it to middleware
32. `php artisan make:middleware Cart\\Sync`
33. Add middleware to HTTP/Kernel.php
```php 
    protected $routeMiddleware = [
        'cart.sync' => \App\Http\Middleware\Cart\Sync::class,
    ];
```
34. Then add the cart.sync to the middleware in OrderController
```php 
    public function __construct()
    {
        $this->middleware(['auth:api', 'cart.sync']);
    }
```
35. ` Tests\Feature\Orders\OrderStoreTest::test_it_fails_if_not_authenticated` test failing because in
37. cart.sync here
```php 
    public function __construct()
    {
        $this->middleware(['auth:api', 'cart.sync']);
    }
```
37. This test is failing because we putting it out of the container
```php
   public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }
```
38. What we do is go to our AppServiceProviders.php when we register the cart return null value
```php
    public function register()
    {
        // now we can always have it in our container
        $this->app->singleton(Cart::class, function ($app) {
            if (!$app->auth->user()) {
                return null;
            }
            $app->auth->user()->load([
                'cart.stock'
            ]);

            return new Cart($app->auth->user());
        });
    }
```
#### Cart middleware so if cart store() is empty it will return null
1. `php artisan make:middleware Cart\\ResponseIfEmpty`
2. It will replace store() method if statement that check if cart is empty
```php
    if ($cart->isEmpty()) {
        return response(null, 400);
    }
```
3. Now we can remove it and add in ResponseIfEmpty middleware
```php 
    public function handle(Request $request, Closure $next)
    {
        if ($this->cart->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty'
            ], 400);
        }
    }
```
4. We have issue since middleware is being run before we hit the store method
5. To fix this in tests that fails you need to add
```php 
    $user->cart()->sync(
        $product = $this->productWithStock()
    );
```
### Orders endpoint
1. Create index method in OrderController.php to grab the order to show it
2. We have problem that 'cart.sync', 'cart.isnotempty' should not apply to this method
3. So we can use only so that the middleware only apply to store() method
```php 
        $this->middleware(['cart.sync', 'cart.isnotempty'])->only('store');
```
4. Send Get request with postman to `http://cart-api.test/api/orders`

#### Testing: Orders endpoint
1. `php artisan make:test Orders\\OrderIndexTest`
2. Test test_it_orders_by_the_latest_first()
3. test_it_has_pagination()

### Formatting order total and subtotal
1. In Postman send Get Request to `http://cart-api.test/api/orders`
2. We modify "subtotal:" by using to "subtotal": {},
3. Using `public function getSubtotalAttribute($subtotal)`
4. Send Post Request to `http://cart-api.test/api/orders`

### Product variation product relationship and reduce queries with laravel debugbar
1. Send post request to `http://cart-api.test/api/orders` with postman
2. Send post request to `http://cart-api.test/api/orders?_debug` with postman to see the rp_statements of laravel debugbar
3. "nb_statements": 47 which is a lot of queries and we should reduce amount
4. We can start reducing them in OrderController.php
```php
       $orders = $request->user()->orders()
            ->with([
                'products',
                'address',
                'shippingMethod'
            ])
            ->latest()
            ->paginate(10);
```
5. We also load products.product the main product of product variation to reduce queries
```php 
    ->with([
        'products',
        'products.product',
        'address',
        'shippingMethod'
    ])
```
6.   "nb_statements": 23, queries got reduced with this change.
7. add new order and product_variation_order to database and see that "nb_statements": 25 does increase
8. What we can do is do a search for "sql" in Postman to see what look like is being query too much.
9. We see product_variation is appearing too much so we add `'products.product.variations',`
```php
    ->with([
        'products',
        'products.product',
        'products.product.variations',
        'address',
        'shippingMethod'
    ])
```
10. Yet seems it didn't reduce nb_statements": 25 
11. Now we add `'products.product.variations.stock',` we can find stock in ProductVariation.php methods it reduced and nb_statements": 20
12. We also going to ass `products.type` as well and it reduced "nb_statements": 16 queries again.
13. Now we search for "sql" again
```php 
"sql": "select \"product_variations\".*, \"product_variation_stock_view\".\"product_variation_id\" as \"pivot_product_variation_id\", \"product_variation_stock_view\".\"stock\" as \"pivot_stock\", \"product_variation_stock_view\".\"in_stock\" as \"pivot_in_stock\" from \"product_variations\" inner join \"product_variation_stock_view\" on \"product_variations\".\"id\" = \"product_variation_stock_view\".\"product_variation_id\" where \"product_variation_stock_view\".\"product_variation_id\" = 8",
"sql": "select \"product_variations\".*, \"product_variation_stock_view\".\"product_variation_id\" as \"pivot_product_variation_id\", \"product_variation_stock_view\".\"stock\" as \"pivot_stock\", \"product_variation_stock_view\".\"in_stock\" as \"pivot_in_stock\" from \"product_variations\" inner join \"product_variation_stock_view\" on \"product_variations\".\"id\" = \"product_variation_stock_view\".\"product_variation_id\" where \"product_variation_stock_view\".\"product_variation_id\" = 8",
"sql": "select \"product_variations\".*, \"product_variation_stock_view\".\"product_variation_id\" as \"pivot_product_variation_id\", \"product_variation_stock_view\".\"stock\" as \"pivot_stock\", \"product_variation_stock_view\".\"in_stock\" as \"pivot_in_stock\" from \"product_variations\" inner join \"product_variation_stock_view\" on \"product_variations\".\"id\" = \"product_variation_stock_view\".\"product_variation_id\" where \"product_variation_stock_view\".\"product_variation_id\" = 8",
```
14. So we add `'products.stock'` and reduced again "nb_statements": 12
15. Now we can remove one of the orders and product_variation_order 
16. And we should see the exact same result


### Fixing a syncing bug
1. If you have more than one product, and product 1 changes we set $this->changed = true but is product 2 comes after that and have not changed
It will always change $this->change = false, so if first product changes you not going to be alerted.
2. We going to modify tests to help us fix this issue
3. Test will detect issue n test_it_can_check_if_the_cart_has_changed_after_syncing()
```php
    $user->cart()->attach([
        $product->id => [
            'quantity' => 2
        ],
        $anotherProduct->id => [
            'quantity' => 0
        ],
    ]);
```
3. Now to fix issue we use if statement in Cart.php sync() method
4. Change this
```php 
   $this->changed = $quantity != $product->pivot->quantity;
```
5. to this which will now only change value if this evaluate to true.
```php 
    if ($quantity != $product->pivot->quantity) {
        $this->changed = true;
    }
```

### Setting up payment methods
1. Going to be similar to addresses
2. php artisan make:model PaymentMethod -m
3. php artisan make:factory PaymentMethodFactory
   


4. php artisan make:test Models\PaymentMethods\\PaymentMethodTest --unit

5. We found problem that in migration we have unique index set for provider id, since we got 2 payment method they going to conflict and get error
```php
Illuminate\Database\QueryException : SQLSTATE[23505]: Unique violation: 7 ERROR:  duplicate key value violates unique constraint "payment_methods_provider_id_unique"
DETAIL:  Key (provider_id)=(abc) already exists. (SQL: insert into "payment_methods" ("card_type", "last_four", "provider_id", "default", "user_id", "updated_at", "created_at") 
values (Visa, 4242, abc, 1, 1, 2020-12-30 01:57:09, 2020-12-30 01:57:09) returning "id")
```
6. the issue is that we hardcoded in the PaymentMethodFactory
```php
   public function definition()
    {
        return [
            'card_type' => 'Visa',
            'last_four' => '4242',
            'provider_id' => 'abc',
        ];
    }
```
7. So we create random provider_id so that the id is unique instead of static

```php 
     public function definition()
    {
        return [
            'card_type' => 'Visa',
            'last_four' => '4242',
            'provider_id' => str_random(10),
        ];
    }
```

### Refractoring defaults to a trait
1. In Address model we going to make this dynamic
```php
    static::creating(function ($address) {
//            dd($address);
        // If the user already has default address set the other ones defaults to false after
        if ($address->default) {
            $address->user->addresses()->update([
                'default' => false
            ]);
        }
    });
```
2. by using newQuery
```php
       static::creating(function ($address) {
            if ($address->default) {
                $address->newQuery()->where('user_id', $address->user->id)->update([
                    'default' => false
                ]);
            }
        });
```

### Payment methods index endpoint
1. php artisan make:controller PaymentMethods\\PaymentMethodController
2. Create route in api.php `Route::resource('paymentMethods', PaymentMethodController::class);`
3. Check in Postman to see if route is working by sending get request to `http://cart-api.test/api/payment-methods`
4. Do a dd() in PaymentMethodController.php index method and  create 2 payment methods in database
```php 
    public function index(Request $request)
    {
        dd($request->user()->paymentMethods);
    }
```
5. Now send Get Request to Postman to see the information `http://cart-api.test/api/payment-methods`
6. Now make resource to display that data php artisan make:resource PaymentMethodResource
7. Then use the PaymentMethodResource in the index method to display data you specified.
```php
  public function index(Request $request)
    {
//        dd($request->user()->paymentMethods);
        return PaymentMethodResource::collection(
            $request->user()->paymentMethods
        );
    }
```
8. Now send Get Request to Postman to see the information `http://cart-api.test/api/payment-methods`
```json
{
    "data": [
        {
            "id": 1,
            "card_type": "Visa",
            "last_four": "4242",
            "default": true
        },
        {
            "id": 2,
            "card_type": "Mastercard",
            "last_four": "1234",
            "default": false
        }
    ]
}```
9. Now we have the information we get to display on the front end.
10. Now we add some authentication to PaymentMethodController.php
```php
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }
```
11. Then test if it authenticated with postman by removing bearer token


#### Testing: Payment methods index endpoint
1. php artisan make:test PaymentMethods\\PaymentMethodIndexTest

### Attaching payment methods to orders (Not allow user to use other users payment method)
1. php artisan make:migration add_payment_method_id_to_orders_table --table=orders
2. We can check if the payment method belong to current user in OrderStoreRequest.php
   ```php 
   'payment_method_id' => [
    'required',
    Rule::exists('payment_methods', 'id')->where(function ($builder) {
        $builder->where('user_id', $this->user()->id);
    })
],
```
3. In Postman send GET request to `http://cart-api.test/api/cart` we don't have product
4. Lets add product to cart with Postman POST request `http://cart-api.test/api/cart` 
5. login with `http://cart-api.test/api/auth/login` if you get error to get bearer token
6. Send GET request with Postman to see now we have item in cart `http://cart-api.test/api/cart`
7. Now Try to create order with POST request in postman to `http://cart-api.test/api/orders`
8. Make sure that there is stock or you going to end up putting quantity 0 in cart, and not be able to make order.
9. and you will see the new validation rule is working properly
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "payment_method_id": [
            "The payment method id field is required."
        ]
    }
}
```
10. Try to create order with POST request in postman to `http://cart-api.test/api/orders` and add payment_method_id with another user id
11. Will give validation error
12. Now use currently logged in id and it should work
13. Since we added payment_method_id the test ` test_it_can_create_an_order()` should fail.
14. So we need to now modify $this->oderDependencies and modify the test
15. Now we need to modify every test that relies on `$this->oderDependencies`
16. We get multiple errors since OrderFactory doesn't generate payment_mehod_id
17. Still get error since we forget to assign user_id to payment_method_id

### Setting up Stripe
1. <https://github.com/stripe/stripe-php>
2. `composer require stripe/stripe-php`
3. in stripe.com create new store
4. Grab the keys from Developers > Api keys <https://dashboard.stripe.com/test/apikeys>
5. go to config/services.php to see where to add the Key
```php
    'stripe' => [
        'secret' => env('STRIPE_SECRET')
    ]
```
6. Now we can access it using stripe.secret
7. We can add it on boot in AppServiceProvider.php
```php 
    public function boot()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
```
8. do a phpunit to check changes didn't break anything

### Mocking up our payment gateway (Be Able to Switch to either Stripe,Braintree or others)
1. This will be collection of methods in 1 class.
2. Which will allow us to create customer, add a cart, charge user with their default payment method
3. This will be tied down to payment provider we are using
4. With Postman send a POST request to `http://cart-api.test/api/payment-methods` to see what is missing
5. We can use AppServiceProvider to declare Gateway which we can use to switch payment method from Stripe to another.
6. Now we can verify if interface working by sending POST request to `http://cart-api.test/api/payment-methods`
 ```js
    "message": "Class App\\Cart\\Payments\\Gateways\\StripeGateway contains 2 abstract methods and must therefore be declared abstract or implement the remaining methods (App\\Cart\\Payments\\Gateway::withUser, App\\Cart\\Payments\\Gateway::createCustomer)",
```
7. PaymentMethodController.php store() method remains the same regardless how we switch gateway
8. If you later want to implement braintree you just have to implement
```
a) BraintreeGateway.php
b) BraintreeCustomer.php
```
8. We need to return $this to be able to chain any other method. in  app /Cart/Payments/Gateways/StripeGateway.php  withUser method!
9. In stripeGateway.php
```php
    public function createCustomer()
    {
        return new StripeGatewayCustomer();
    }
```
10. If where you will see that we connect stripeGateway.php to  StripeGatewayCustomer.php

### Storing a payment method
1. At the moment we don't want to add card yet in PaymentMethodController.php
```php 
    public function store(Request $request)
    {
        $card = $this->gateway->withUser($request->user())
            ->createCustomer();
        dd($card);
//            ->addCard($request->token);
    }
```
2. php artisan make:migration add_gateway_customer_id_to_users_table --table=users

3. Do a dd($customer) to see what happens.
```php 
    public function createCustomer()
    {
        // we will implement method that will get customer from stripe, based on the user gateway_customer_id
        if ($this->user->gateway_customer_id) {
            return 'customer';
        }

        // create stripe customer here and return that as part of our stripe gateway customer object.

        $customer = $this->createStripeCustomer();

        dd($customer);
        return new StripeGatewayCustomer();
    }
```
4. Now send a POST request to `http://cart-api.test/api/payment-methods` using POSTMAN
5. Now you can see we get a customer back from stripe
6. You can also use Stripe dashboard to see this new created customer
7. Send POST request to `http://cart-api.test/api/payment-methods`
8. After adding id to database you will get error
```
"message": "Call to a member function addCard() on string",
```
9. You can check customer id here `https://dashboard.stripe.com/test/customers/cus_IgUKHnTDgrYld1` writing the customer id in the end the one in user table cart database
10. We need to truncate and delete all files from payment_methods 
11.  Right click on payment_methods table, that needs to be truncated in Dbeaver Database Navigator, choose Tools->Truncate
12. Click on Restart Identity and Cascade click 'Start'. That's all.
13. Send POST request to `http://cart-api.test/api/payment-methods` we should get null since we are using dd() and method not returning information
12. Now check Stripe Dashboard for new customer `https://dashboard.stripe.com/test/customers/cus_Igkt5xUNbyv9R3`
13. Then click on card and see the `ID card_1I5NO2HCos07RG12lTRiDHf6` match the card id in payment_methods table and compare it with the provider_id 
14. There is a problem that each card added is being added as default true, it should only be last card.
15. To fix this go to StripeGateWayCustomer.php create method and add 'default' => true
```php
        $this->gateway->user()->paymentMethods()->create([
            'provider_id' => $card->id,
            'card_type' => $card->brand,
            'last_four' => $card->last4,
            'default' => true
        ]);
```
Each time you make changes truncate payment_methods and in users table delete the gateway_customer_id
15. Now the last card added will be default one so lets get provider_id  from the default card and check dashboard to see if it set as default.

### Responding with a card and writing some tests
1. In StripeGatewayCustomer.php addCard() method return the data
```php
        return $this->gateway->user()->paymentMethods()->create([
            'provider_id' => $card->id,
            'card_type' => $card->brand,
            'last_four' => $card->last4,
            'default' => true
        ]);
```
2.  Then send a Post Request with Postman to http://cart-api.test/api/payment-methods
3. Now on PaymentMethodController.php store() method
```php 
        return new PaymentMethodResource($card);
```
4. and you will receive data to display on the site.
```php
{
    "data": {
        "id": 4,
        "card_type": "Visa",
        "last_four": "4242",
        "default": true
    }
}
```
### Testing: Responding with a card and writing some tests (hitting API in Testing)
1. `php artisan make:test PaymentMethods\\PaymentMethodStoreTest`
2. Test failed `Failed to find a validation error in the response for key: 'token'`
3. Since we needed to add Validation in PaymentMethodController.php
```php 
    public function store(Request $request)
    {
        $this->validate($request, [
           'token' => 'required'
        ]);
    }
```
4. Is better to deal with API rather than deal with Fake data through mocking.
5. Only problem is if Stripe API is temporally down and you need internet connection to test it
6. php artisan make:request PaymentMethods\\PaymentMethodRequest

### Event handler for processing the payment


### Processing a payment
1. Go to POSTMAN and send a Get request to cart `http://cart-api.test/api/cart`
2. send a POST request to add item to cart `http://cart-api.test/api/cart`
3. Make an order with Postman by send POST request to `http://cart-api.test/api/orders`
4. Then check on <https://dashboard.stripe.com/test/payments?status%5B%5D=successful>

### Handling a failed payment
1. php artisan
2. php artisan make:exception PaymentFailedException
3. php artisan make:event Orders\\OrderPaymentFailed
4. Send a POST & GET request with Postman in `http://cart-api.test/api/cart` to add product to cart and see item added.
5. If stock is 0 make sure to add stock in stocks table on the cart database.
6. Send POST request to `http://cart-api.test/api/orders` with postman
7. Now check database for orders table to see order status as payment_failed
8. We checked the Exception worked by adding it in the charge method of StripeGatewayCustomer.php
```php 
    public function charge(PaymentMethod $card, $amount)
    {
        try {
//            throw new PaymentFailedException();
```


### Handling a successful payment
1. Send a POST & GET request with Postman in `http://cart-api.test/api/cart` to add product to cart and see item added.
2. Send POST request to `http://cart-api.test/api/orders` with postman

### Fixing failing 'cart empty' test
1. Use `dd($response->getContent());` in `test_it_empties_the_cart_when_ordering()` to debug
2. Then run `test_it_empties_the_cart_when_ordering()` to see what is the actual problem
3. `"message": "Could not determine which URL to request: Stripe\\Customer instance has invalid ID:`
4. The issue is happening in ProcessPayment.php in handle method since
5. We are sending in the withUser() to grab the getCustomer() instance
6. We can't grab the customer since we don't have the id associated with that customer on the test `test_it_empties_the_cart_when_ordering()`
7. What we going to do is update the orderDependencies() method in OrderStoreTest.php
9. So we going to update user with real account Stripe id
```php
    protected function orderDependencies(User $user)
    {
        $stripeCustomer =  \Stripe\Customer::create([
            'email' => $user->email,
        ]);

        $user->update([
            'gateway_customer_id' => $stripeCustomer->id
        ]);
```
10.  Had an issue where app/Events/Order/OrderPaymentFailed.php  namespace was incorrect.
11. Was able to find error using `dd($response->getContent());` in `test_it_empties_the_cart_when_ordering()` to debug
12. Be aware we're not yet testing that payment is passing we will work on it next.

### Testing listeners
1. Since these going to be unit test we not going to hit the stripe api so we going to use mockery data
2. php artisan make:test Listeners\\EmptyCartListenerTest --unit
3. In EmptyCart.php you can check that the test from EmptyCartListenerTest.php test_it_should_clear_the_cart() is working by commenting  `$this->cart->empty();`
```php 
    public function handle()
    {
//        $this->cart->empty();
    }
```

### Using Mockery to test more complex listeners
1. What we plan to test in ProcessPayment.php
```php 
    public function handle(OrderCreated $event)
    {
        $order = $event->order;
        try {
            $this->gateway->withUser($order->user)
            ->getCustomer()
            ->charge(
            // 1# Test that order successfully charge and pass through correct paymentMethod
            // 2# correct total for the order
                $order->paymentMethod, $order->total()->amount()
            );
            // 3# Test it fires the OrderPaid event
            event(new OrderPaid($order));
        } catch (PaymentFailedException $e) {
            // 4# Test if event happens if order fails
            event(new OrderPaymentFailed($order));
        }
    }
```
2. We going to test all of these without hitting Stripe
3. We not Mocking Stripe but Mock Payment Gateway
4. `php artisan make:test Listeners\\ProcessPaymentListenerTest --unit`

### Order transactions setup
1. php artisan make:model Transaction -m
2. Has many so you can setup up in future so that user can may more or pay half
3. HasMany makes things easier to work with.
```php
public function transactions()
{
    return $this->hasMany(Transaction::class);
}
```

### Testing: Order transactions setup
1. php artisan make:factory TransactionFactory
2. There was an error with test `public function test_it_has_many_transactions()`
3. `Undefined column: 7 ERROR:  column "amount" of relation "transactions" does not exist`
4. Problem was that in our TransactionFactory we wrote amount instead of total

### Storing transactions
1. Now that we have transaction in place.
2. Once we had OrderPaid and fire the event. We can go ahead and create that transaction.
3. In `app/Providers/EventServiceProvider.php`
4. add   CreateTransaction::class so that it runs when OrderPaid::class runs.
```php 
    protected $listen = [
        OrderPaid::class => [
            CreateTransaction::class,
            MarkOrderProcessing::class
        ],
    ];
```
5. Then add `use App\Listeners\Order\CreateTransaction;`
6. Run `php artisan event:generate`
7. It should generate `app/Listeners/Order/CreateTransaction.php`
8. Then we can get the information from the event
```php 
    public function handle(OrderPaid $event)
    {
        $event->order->transactions()->create([
            'total' => $event->order->total()->amount() // 1000
        ]);
    }
```
9. Now to test if this works properly we can use Postman
#### Making Order to see if transactions are working 
1. Login to get Token by sending GET request to `http://cart-api.test/api/auth/login` using POSTMAN and copy the token
2. Now in POSTMAN send GET request to see what items in your cart `http://cart-api.test/api/cart` and if empty then
3. Send POSTMAN send POST request to add item to cart `http://cart-api.test/api/cart`
4. In POSTMAN send GET request to see what items in your cart `http://cart-api.test/api/cart`
5. Now make an Order by sending with POSTMAN a POST request to `http://cart-api.test/api/orders`
6. Now Check cart transactions table has an order inside

#### Testing: Storing transactions
1. Copy MarkOrderPaymentListenerTest.php to save time and create CreateTransactionListenerTest.php
2. You can add more information that you need to show in a transaction after processing payments.















