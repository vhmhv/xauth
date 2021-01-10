<?php
Route::post('logout', 'vhmhv\Xauth\XauthLoginController@logout')->name('logout');
Route::get('logout', 'vhmhv\Xauth\XauthLoginController@logout')->name('logout');
Route::get('login', 'vhmhv\Xauth\XauthLoginController@xauthlogin')->middleware('web')->name('login');
Route::post('login', 'vhmhv\Xauth\XauthLoginController@xauthlogin')->middleware('web')->name('login');
Route::get('login/office365', 'vhmhv\Xauth\XauthLoginController@xauthlogin')->name('login.office365');
Route::post('login/office365', 'vhmhv\Xauth\XauthLoginController@xauthlogin')->name('login.office365');
Route::get('login/graph/callback', 'vhmhv\Xauth\XauthLoginController@handleProviderCallback');
