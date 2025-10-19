<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    /**
     * Display the communication dashboard
     */
    public function index()
    {
        return view('communication.index');
    }

    /**
     * Display the send message form
     */
    public function sendMessage()
    {
        return view('communication.send-message');
    }

    /**
     * Display the message history
     */
    public function history()
    {
        return view('communication.history');
    }

    /**
     * Display communication settings
     */
    public function settings()
    {
        return view('communication.settings');
    }
}
