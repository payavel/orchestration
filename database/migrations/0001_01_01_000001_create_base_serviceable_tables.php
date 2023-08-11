<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class CreateBaseServiceableTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $usingDatabaseDriver = Config::get('serviceable.defaults.driver') === 'database';

        if ($usingDatabaseDriver) {
            Schema::create('services', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->timestamps();
            });

            Schema::create('providers', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('service_id');
                $table->string('name');
                $table->string('request_class');
                $table->string('response_class');
                $table->timestamps();

                $table->foreign('service_id')->references('id')->on('services')->onUpdate('cascade')->onDelete('cascade');
            });

            Schema::create('merchants', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->timestamps();
            });

            Schema::create('merchant_provider', function (Blueprint $table) {
                $table->increments('id');
                $table->string('merchant_id');
                $table->string('provider_id');
                $table->boolean('default')->default(false);
                $table->json('config')->nullable();
                $table->timestamps();

                $table->foreign('merchant_id')->references('id')->on('merchants')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('provider_id')->references('id')->on('providers')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_provider');
        Schema::dropIfExists('merchants');
        Schema::dropIfExists('providers');
        Schema::dropIfExists('services');
    }
}
