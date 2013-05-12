<?php

use Illuminate\Database\Migrations\Migration;

class CreateAuthTokenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
    Schema::create('ta_auth_tokens', function($table)
    {
      $table->integer('auth_identifier');
      $table->string('public_key', 96);
      $table->string('private_key', 96);
      $table->timestamps();
      $table->primary(array('auth_identifier', 'public_key', 'private_key'));
    });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
    Schema::drop('ta_auth_tokens');
	}

}