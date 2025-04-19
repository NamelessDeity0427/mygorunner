<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class SupportController extends Controller
{
    public function index(): View
    {
        $tickets = SupportTicket::with('user')
            ->latest()
            ->paginate(15);
        return view('admin.support.index', compact('tickets'));
    }

    public function create(): View
    {
        return view('support.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        try {
            SupportTicket::create([
                'user_id' => Auth::id(),
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => 'open',
            ]);
            return redirect()->route('customer.dashboard')->with('success', 'Support ticket created successfully.');
        } catch (Exception $e) {
            Log::error("Support ticket creation failed", [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to create support ticket.');
        }
    }
}