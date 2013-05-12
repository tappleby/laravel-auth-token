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
      /*                ->where('auth_identifier', $authToken->getAuthIdentifier())
                ->where('public_key', $authToken->getPublicKey())
                ->where('private_key', $authToken->getPrivateKey())*/

      $table->increments('id');
      $table->integer('auth_identifier');
      $table->string('public_key', 96);
      $table->string('private_key', 96);
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
    Schema::drop('ta_auth_tokens');
	}

}