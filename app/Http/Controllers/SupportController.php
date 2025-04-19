<?php
namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SupportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:customer'])->only(['create', 'store']);
        $this->middleware(['auth', 'role:admin,staff'])->only(['index']);
    }

    public function index(): View
    {
        $tickets = SupportTicket::with(['user' => function ($query) {
            $query->select('id', 'name');
        }])
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
            return DB::transaction(function () use ($validated) {
                $ticket = SupportTicket::create([
                    'user_id' => Auth::id(),
                    'subject' => $validated['subject'],
                    'description' => $validated['description'],
                    'priority' => $validated['priority'],
                    'status' => 'open',
                ]);

                SupportMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => Auth::id(),
                    'message' => $validated['description'],
                ]);

                return redirect()->route('customer.dashboard')->with('success', "Support ticket #{$ticket->id} created successfully.");
            });
        } catch (\Exception $e) {
            Log::error('Support ticket creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to create support ticket: ' . $e->getMessage());
        }
    }
}