<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


    //ECFプロジェクト web
    Route::group(['prefix' => '/equity/project', 'as' => 'equity.project.'], function () {
        Route::resource('', 'Front\EquityController', ['only' => ['index','show'], 'parameters' => [''=>'id'], 'middleware' => 'userconfirmation']);
        Route::get('/document/kouhu', 'Front\EquityController@document_kouhu')->name('document.kouhu');
        Route::get('/{id}/document/{type}', 'Front\EquityController@showDocument')->name('document.show')->middleware('userconfirmation');
        Route::group(['middleware' => ['auth.user']], function () {
            Route::get('/{id}/purchase/entry', 'Front\EquityController@purchaseEntry')->name('purchase.entry')->middleware('userconfirmation');
            Route::post('/{id}/purchase/confirm', 'Front\EquityController@purchaseConfirm')->name('purchase.confirm');
            Route::post('/{id}/purchase/store', 'Front\EquityController@purchaseStore')->name('purchase.store');
            Route::get('/{id}/purchase/complete', 'Front\EquityController@purchaseComplete')->name('purchase.complete');
        });
    });


    // ECF管理
    Route::group(['prefix' => '/admin', 'as' => 'admin.'],function (){
        Route::group(['prefix' => '/equity', 'as' => 'equity.'], function () {
            Route::group(['prefix' => '/project', 'as' => 'project.'], function () {
                Route::resource('', 'EquityController', ['parameters' => [''=>'id']]);
                Route::get('/{id}/detail/{type}', 'EquityDetailController@show')->name('detail.show');
                Route::get('/{id}/detail/{type}/edit', 'EquityDetailController@edit')->name('detail.edit');
                Route::match(['PUT', 'PATCH'], '/{id}/detail/{type}/edit', 'EquityDetailController@update')->name('detail.update');
                Route::post('/{id}/detail/{type}/confirm', 'EquityDetailController@confirm')->name('detail.confirm');
                Route::get('/{id}/detail/{type}/preview', 'EquityDetailController@preview')->name('detail.preview');
                Route::get('/{id}/complete', 'EquityController@complete')->name('complete');
                Route::get('/{id}/status', 'EquityStatusController@index')->name('status');
                Route::patch('/{id}/status', 'EquityStatusController@update')->name('status.update');
                Route::get('/{id}/status/complete', 'EquityStatusController@complete')->name('status.complete');
                Route::post('/{id}/general_report', 'EquityStatusController@general_report')->name('general_report');
                Route::get('/{id}/investor', 'EquityInvestorController@index')->name('investor');
                Route::get('/investor/mail/preview', 'EquityInvestorController@mail_preview')->name('investor.mail_preview');
                Route::post('/{id}/investor/mail', 'EquityInvestorController@mail')->name('investor.mail');
                Route::post('/{id}/investor/csv/payment_status/download', 'EquityInvestorController@download')->name('investor.download');
                Route::post('/{id}/investor/csv/payment_status/upload', 'EquityInvestorController@upload')->name('investor.upload');
                Route::post('/{id}/investor/cancel', 'EquityInvestorController@cancel')->name('investor.cancel');//強制キャンセル
                Route::get('/{id}/investor/complete', 'EquityInvestorController@complete')->name('investor.complete');
                Route::get('/{id}/waiting', 'EquityWaitingController@index')->name('waiting');
                Route::post('/{id}/waiting/set_request_data', 'EquityWaitingController@set_request_data')->name('waiting.set_request_data');//プールの適応
                Route::get('/{id}/waiting/{waiting_id}/edit', 'EquityWaitingController@edit')->name('waiting.edit');
                Route::patch('/{id}/waiting/update', 'EquityWaitingController@update')->name('waiting.update');
                Route::post('/{id}/waiting/cancel', 'EquityWaitingController@cancel')->name('waiting.cancel');
                Route::post('/{id}/waiting/release', 'EquityWaitingController@release')->name('waiting.release');
            });
        });
    });