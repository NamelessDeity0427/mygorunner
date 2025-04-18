<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(): View
    {
        // Later, pass data like recent bookings, etc.
        return view('customer.dashboard');
    }
}
