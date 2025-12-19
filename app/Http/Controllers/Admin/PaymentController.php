<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Payment::with(['event', 'user', 'enrollment'])
            ->latest('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $payments = $query->paginate(15);

        // Calculate stats
        $stats = [
            'total' => Payment::count(),
            'paid' => Payment::where('status', 'paid')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
            'total_amount' => Payment::where('status', 'paid')->sum('amount'),
        ];

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'stats' => $stats,
            'currentFilter' => $status,
        ]);
    }

    public function show(Payment $payment)
    {
        $payment->load(['event', 'user', 'enrollment']);

        return Inertia::render('Admin/Payments/Show', [
            'payment' => $payment,
        ]);
    }
}
