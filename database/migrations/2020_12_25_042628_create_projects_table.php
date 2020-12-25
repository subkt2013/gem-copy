<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name', 30)->comment('プロジェクト名');
            $table->string('description', 150)->nullable()->comment('プロジェクト説明');
            $table->integer('status')->comment('ステータス');
            $table->string('thumbnail', 256)->comment('サムネイル画像');
            $table->dateTime('release_datetime')->comment('公開日時');
            $table->dateTime('recruit_start_datetime')->comment('募集開始日時');
            $table->dateTime('scheduled_end_datetime')->comment('終了予定日時');
            $table->dateTime('end_datetime')->nullable()->comment('終了日時');
            $table->decimal('target_amount', 15, 0)->default(0)->comment('目標額');
            $table->decimal('maximum_amount', 15, 0)->default(0)->comment('上限額');
            $table->decimal('minimum_amount', 15, 0)->default(0)->comment('下限額');
            $table->integer('stock_type')->comment('株式種別');
            $table->decimal('unit_price_of_stock', 15, 0)->comment('株単価');
            $table->string('pre_contract_document', 256)->comment('契約締結前交付書面');
            $table->string('wanted_matters_notice', 256)->comment('⾮上場株式のご購⼊に関する確認書');
            $table->string('convocation_notification', 256)->comment('GEMSEE サービス利⽤規約(契約締結前にもう一度ご確認ください)');
            $table->unsignedBigInteger('corporation_id')->comment('会社ID');
            $table->unsignedBigInteger('bank_id')->comment('銀行ID');
            $table->text('memo', 10000)->nullable()->comment('メモ');

            $table->date('transfer_start_date')->nullable()->comment('振込開始日');
            $table->date('transfer_date')->nullable()->comment('振込期日');
            $table->dateTime('establishment_date')->nullable()->comment('成立日/約定日');
            $table->date('paymented_date')->nullable()->comment('発行企業への払込完了日付');
            $table->date('payment_due_date')->nullable()->comment('払込期日');

            $table->unsignedBigInteger('html_id')->comment('htmlID');

            $table->boolean('report_flag')->default(0)->comment('取引報告書出力フラグ');
            $table->boolean('emergency_flag')->default(0)->comment('非常停止フラグ');
            
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("ALTER TABLE projects COMMENT '【株式投資型CF】プロジェクトテーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
