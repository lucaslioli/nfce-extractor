<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Nfce;

class NfceController extends Controller
{
    public function index()
    {
    	return view('home', ['count' => Nfce::count()]);
    }

    public function extractor()
    {
    	return view('extractor', ['count' => Nfce::count()]);
    }

    public function show($id)
    {
		$nfce = Nfce::find($id);
	
		// return view('nfces.show', compact('nfce'));

    	$data = Nfce::get_all_data($nfce->access_key, 1);
    	
    	return view('show_nfce', ['data' => $data, 'count' => Nfce::count()]);
    }

    public function data_extract()
    {
    	$key = request('key');
    	if(strlen($key)!=44)
    		return view('show_nfce', ['data' => 'Chave de Acesso menor que 44 dígitos!', 'count' => Nfce::count()]);

    	$data = Nfce::get_all_data($key);

		return view('show_nfce', ['data' => $data, 'count' => Nfce::count()]);
    }
}
