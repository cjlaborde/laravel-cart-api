<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductVariationTypeIdToProductVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->integer('product_variation_type_id')->unsigned()->index();
            $table->foreign('product_variation_type_id')->references('id')->on('product_variation_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn('product_variation_type_id');
        });
    }
}
