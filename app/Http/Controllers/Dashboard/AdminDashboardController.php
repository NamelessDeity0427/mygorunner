<?php

// app/Http/Controllers/Dashboard/AdminDashboardController.php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        // Later, pass data like active riders, pending tasks, reports
        return view('admin.dashboard');
    }
}
