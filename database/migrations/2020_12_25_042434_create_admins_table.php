<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('admins', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('email', 256)->default('')->comment('[メールアドレス]ログインIDとして使用');
			$table->string('password', 128)->default('')->comment('[パスワード]bcrypt hashed');
			$table->string('display_name', 100)->default('')->comment('[表示名]');
			$table->string('admin_type', 30)->default('')->comment('[権限種別]区分値 SUPERUSER: 特権管理者, ADMINISTRATOR: 一般管理者, OPERATOR: 作業者');
			$table->boolean('is_reviewer')->comment('[確認者フラグ]0: 確認者でない 1: 確認者（確認者プルダウンに表示される）');
			$table->string('status', 30)->nullable()->comment('[ステータス]区分値 退社などの状態を扱う プレでは OK:有効 のみ');
			$table->timestamps();
		});
		DB::statement("ALTER TABLE admins COMMENT '管理画面にログインできる人。'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
