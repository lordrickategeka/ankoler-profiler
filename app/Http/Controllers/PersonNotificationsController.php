<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonNotificationsController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(15);
        return view('person.notifications', compact('notifications'));
    }
}
