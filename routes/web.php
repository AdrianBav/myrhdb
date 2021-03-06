<?php

use AdrianBav\Traffic\Middlewares\RecordVisits;

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

Route::get('/', 'StatisticsController@index')->middleware(RecordVisits::class)->name('home');

Route::get('concerts/{concert}', 'StatisticsController@concert')->name('concert');

Route::get('releases/{release}', 'StatisticsController@release')->name('release');
