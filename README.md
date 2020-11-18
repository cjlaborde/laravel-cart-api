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






