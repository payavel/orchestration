<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaseOrchestrationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('service_id');
            $table->string('gateway');
            $table->timestamps();
        });

        Schema::create('merchants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('service_id');
            $table->string('default_provider_id')->nullable();
            $table->timestamps();

            $table->foreign('default_provider_id')->references('id')->on('providers')->onUpdate('cascade')->onDelete('set null');
        });

        Schema::create('merchant_provider', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merchant_id');
            $table->string('provider_id');
            $table->json('config')->nullable();
            $table->timestamps();

            $table->foreign('merchant_id')->references('id')->on('merchants')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('providers')->onUpdate('cascade')->onDelete('cascade');
        });
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
    }
}
