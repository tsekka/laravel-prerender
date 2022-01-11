<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCacheKeyFieldFromPrerenderedPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prerendered_pages', function (Blueprint $table) {
            $table->dropColumn('cache_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prerendered_pages', function (Blueprint $table) {
            $table->string('cache_key');
        });
    }
}
