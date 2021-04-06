<?php
Route::post('logout', 'vhmhv\Xauth\XAuthLoginController@logout')->middleware('auth')->name('logout');
Route::get('logout', 'vhmhv\Xauth\XAuthLoginController@logout')->middleware('auth')->name('logout');
Route::get('login', 'vhmhv\Xauth\XAuthLoginController@xauthlogin')->name('login');
Route::post('login', 'vhmhv\Xauth\XAuthLoginController@xauthlogin')->name('login');
Route::get('login/office365', 'vhmhv\Xauth\XAuthLoginController@xauthlogin')->name('login.office365');
Route::post('login/office365', 'vhmhv\Xauth\XAuthLoginController@xauthlogin')->name('login.office365');
Route::get('login/graph/callback', 'vhmhv\Xauth\XAuthLoginController@handleProviderCallback');
