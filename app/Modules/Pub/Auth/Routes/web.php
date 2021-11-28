<?php

Route::group(['prefix' => 'auths', 'middleware' => []], function () {
    Route::get('/login', 'LoginController@showLoginForm')->name('auths.login');
});
