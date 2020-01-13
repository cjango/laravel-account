<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('accountable');
            $table->decimal('balance', 20, 2)->unsigned();
            $table->decimal('score', 20, 2)->unsigned();
            $table->timestamps();
        });
        Schema::create('account_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('rule_id')->nullable();
            $table->string('type', 32);
            $table->decimal('variable', 20, 2)->unsigned();
            $table->decimal('balance', 20, 2)->unsigned();
            $table->unsignedTinyInteger('frozen')->default(0);
            $table->json('source');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('account_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('name');
            $table->string('type');
            $table->decimal('variable', 20, 2)->default(0);
            $table->integer('trigger')->default(0);
            $table->unsignedTinyInteger('deductions')->default(0);
            $table->string('remark');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_logs');
        Schema::dropIfExists('account_rules');
    }

}
