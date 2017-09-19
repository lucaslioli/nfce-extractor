<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NfceController extends Controller
{
    public function index()
    {
    	return view('nfce');
    }

    public function extractor()
    {
    	return view('extractor');
    }
}
