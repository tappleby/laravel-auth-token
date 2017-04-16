<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSessionIdToAuthTokenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ta_auth_tokens', function(Blueprint $table)
		{
			$table->string('session_id', 40)->nullable()->after('auth_identifier');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ta_auth_tokens', function(Blueprint $table)
		{
			$table->dropColumn('session_id');
		});
	}

}