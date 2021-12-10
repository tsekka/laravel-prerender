<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrawlerVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawler_visits', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->unsignedSmallInteger('http_status_code')
                ->nullable();
            $table->text('user_agent');
            $table->unsignedInteger('server_response_time')
                ->comment('Milliseconds since laravel booted until moment before persisted to table');
            $table->foreignId('prerendered_page_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawler_visits');
    }
}
