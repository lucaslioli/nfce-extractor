<?php

Route::get('/', 'NfceController@index');

Route::get('/extractor', 'NfceController@extractor');

Route::post('/data_extract','NfceController@data_extract');

Route::get('/nfce/{id}','NfceController@show');

// Route::get('/nfce/{id}', function($id){
// 	$nfce = DB::table('nfces')->find($id);
    	
// 	return view('nfces.show', compact('nfce'));
// });

