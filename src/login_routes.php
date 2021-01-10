<?php
Route::post('logout', 'vhmhv\Xauth\XauthLoginController@logout')->name('logout');
Route::get('logout', 'vhmhv\Xauth\XauthLoginController@logout')->name('logout');
Route::get('login', 'vhmhv\Xauth\XauthLoginController@redirectToProvider')->name('login');
Route::post('login', 'vhmhv\Xauth\XauthLoginController@redirectToProvider')->name('login');
Route::get('login/graph/callback', 'vhmhv\Xauth\XauthLoginController@handleProviderCallback');
