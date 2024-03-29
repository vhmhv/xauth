<?php

Route::post('logout', [vhmhv\Xauth\XAuthLoginController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('logout', [vhmhv\Xauth\XAuthLoginController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('login', [vhmhv\Xauth\XAuthLoginController::class, 'chooseMethod'])->name('login');
Route::post('login', [vhmhv\Xauth\XAuthLoginController::class, 'chooseMethod '])->name('login');
Route::get('login/office365', [vhmhv\Xauth\XAuthLoginController::class, 'redirectToProvider'])->name('login.office365');
Route::post('login/office365', [vhmhv\Xauth\XAuthLoginController::class, 'redirectToProvider'])->name('login.office365');
Route::get('login/apple', [vhmhv\Xauth\XAuthLoginController::class, 'redirectToApple'])->name('login.apple');
Route::post('login/apple', [vhmhv\Xauth\XAuthLoginController::class, 'redirectToApple'])->name('login.apple');
Route::get('login/graph/callback', [vhmhv\Xauth\XAuthLoginController::class, 'handleProviderCallback']);
Route::get('login/apple/callback', [vhmhv\Xauth\XAuthLoginController::class, 'handleAppleCallback']);
Route::get('login/qr', [vhmhv\Xauth\XAuthLoginController::class, 'qrLogin'])->name('login.qr');
Route::post('login/qr', [vhmhv\Xauth\XAuthLoginController::class, 'loginByQR'])->name('login.qr.process');
