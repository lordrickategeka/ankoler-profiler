<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AllPersonsListController extends Controller
{
    public function index() {
        return view('persons.all-persons-list');
    }
}
