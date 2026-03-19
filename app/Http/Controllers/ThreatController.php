<?php

namespace App\Http\Controllers;

class ThreatController extends Controller
{
    public function index()
    {
        return view('threats.index');
    }
}
