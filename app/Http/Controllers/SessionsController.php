<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SessionsController extends Controller
{
    public function index()
    {
$sessions = DB::table('sessions')
    ->leftJoin('users', 'sessions.user_id', '=', 'users.id') // ← استخدم leftJoin
    ->select(
        'sessions.id',
        'users.name as user_name',
        'sessions.ip_address',
        'sessions.user_agent',
        'sessions.last_activity'
    )
    ->orderBy('sessions.last_activity', 'desc')
    ->get()
    ->map(function($session) {
        $session->last_activity = Carbon::createFromTimestamp($session->last_activity)->diffForHumans();
        return $session;
    });
        return view('sessions.index', compact('sessions'));
    }
}
