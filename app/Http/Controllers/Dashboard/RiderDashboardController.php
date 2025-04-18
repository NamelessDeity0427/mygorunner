<?php

// app/Http/Controllers/Dashboard/RiderDashboardController.php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiderDashboardController extends Controller
{
    public function index(): View
    {
        // Later, pass data like assigned tasks, status, earnings
        return view('rider.dashboard');
    }
}
