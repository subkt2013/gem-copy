<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->string('email')->default('')->comment('[メールアドレス]ログインIDとして使用');
			$table->string('password')->default('')->comment('[パスワード]bcrypt hashed');
			$table->string('display_id', 100)->nullable()->comment('[ユーザーID]画面に表示するID。カスタマーサポートで顧客本人であることを確認する場合使用\n\n会員ID カスタマーサポートで顧客本人であることを確認する場合使用');
			$table->string('display_name', 100)->default('')->comment('[ニックネーム]マイページ画面に出す名前。本名が常に表示されるのを防ぐ');
			$table->string('user_type', 30)->nullable()->comment('[会員タイプ]区分値　個人/法人');
			$table->string('investor_status', 30)->default('NONE')->comment('[投資家ステータス]区分値 NONE:投資家でない PENDING:手続き中 投資家 OK:投資可能');
			$table->string('raiser_status', 30)->default('NONE')->comment('[調達企業ステータス]区分値 NONE:調達でない PENDING:手続き中 OK:調達可能');
			$table->boolean('is_valid_2step_auth')->default(0)->comment('[二段階認証設定フラグ]');
			$table->string('status', 30)->nullable()->comment('[ステータス]区分値 退会などのユーザー状態を扱う プレでは OK:有効 のみ');
			$table->timestamps();
        });
        DB::statement("ALTER TABLE users COMMENT '本platformを利用する人。このplatform上でのみ関係する情報を保存し、氏名などの現実世界に即した情報はkycsテーブルに保存する。'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
