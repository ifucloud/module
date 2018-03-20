<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateServicesTable.
 */
class CreateServicesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('services', function(Blueprint $table) {
            $table->increments('id');
            $table->string('ser_name')->comment('服务名称');
            $table->string('ser_state')->comment('服务状态');
            $table->string('ser_code')->comment('服务代号');
            $table->string('ser_secret')->comment('服务密钥');
            $table->string('oauth_token')->default('')->comment('认证令牌');
            $table->string('ser_desc')->comment('服务描述');
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
		Schema::dropIfExists('services');
	}
}
