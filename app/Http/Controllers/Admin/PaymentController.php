<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\Chatbot\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    public function markPaid(Payment $payment)
    {
        if ($payment->status === 'paid') {
            return redirect()->back()->with('error', 'Payment is already marked as paid');
        }

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        Log::info('Payment manually marked as paid by admin', [
            'payment_id' => $payment->id,
            'reference' => $payment->reference_number,
        ]);

        return redirect()->back()->with('success', 'Payment marked as paid successfully');
    }

    public function createEnrollment(Payment $payment, EnrollmentService $enrollmentService)
    {
        if ($payment->status !== 'paid') {
            return redirect()->back()->with('error', 'Only paid payments can be enrolled');
        }

        if ($payment->enrollment) {
            return redirect()->back()->with('error', 'Enrollment already exists for this payment');
        }

        if (! $payment->event) {
            return redirect()->back()->with('error', 'Payment has no associated event');
        }

        try {
            // Create enrollment
            $enrollment = Enrollment::create([
                'event_id' => $payment->event_id,
                'phone_number' => $payment->phone_number,
                'payment_id' => $payment->id,
                'status' => 'confirmed',
                'enrollment_date' => now(),
            ]);

            Log::info('Enrollment manually created by admin', [
                'enrollment_id' => $enrollment->id,
                'payment_id' => $payment->id,
                'event_id' => $payment->event_id,
            ]);

            return redirect()->back()->with('success', 'Enrollment created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create enrollment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to create enrollment: '.$e->getMessage());
        }
    }
}
