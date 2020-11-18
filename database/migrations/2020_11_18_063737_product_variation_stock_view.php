<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductVariationStockView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXIST product_variation_stock_view");
    }
}
