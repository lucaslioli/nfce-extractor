<?php

Route::get('/', 'NfceController@index');

Route::get('/extractor', 'NfceController@extractor');

Route::post('/data_extract','NfceController@data_extract');

// Route::get('/nfce/{id}','NfceController@show');
