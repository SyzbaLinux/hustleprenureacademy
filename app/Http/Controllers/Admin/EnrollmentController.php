<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Enrollment::with(['event', 'user', 'payment'])
            ->latest('enrollment_date');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $enrollments = $query->paginate(15);

        // Calculate stats
        $stats = [
            'total' => Enrollment::count(),
            'confirmed' => Enrollment::where('status', 'confirmed')->count(),
            'pending' => Enrollment::where('status', 'pending')->count(),
            'cancelled' => Enrollment::where('status', 'cancelled')->count(),
            'completed' => Enrollment::whereNotNull('completion_date')->count(),
        ];

        return Inertia::render('Admin/Enrollments/Index', [
            'enrollments' => $enrollments,
            'stats' => $stats,
            'currentFilter' => $status,
        ]);
    }

    public function create()
    {
        $events = Event::where('is_active', true)
            ->select('id', 'title', 'type', 'amount', 'currency')
            ->orderBy('title')
            ->get();

        $users = User::select('id', 'name', 'email', 'phone_number')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Enrollments/Create', [
            'events' => $events,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => 'nullable|exists:users,id',
            'phone_number' => 'required|string|max:20',
            'full_name' => 'required_without:user_id|string|max:255',
            'email' => 'required_without:user_id|email|max:255',
            'status' => 'required|in:pending,confirmed,cancelled',
            'notes' => 'nullable|string',
        ]);

        // If user_id is provided, get their details
        if (!empty($validated['user_id'])) {
            $user = User::find($validated['user_id']);
            $validated['full_name'] = $user->name;
            $validated['email'] = $user->email;
        }

        $validated['enrollment_date'] = now();

        Enrollment::create($validated);

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Enrollment created successfully!');
    }

    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['event', 'user', 'payment']);

        return Inertia::render('Admin/Enrollments/Show', [
            'enrollment' => $enrollment,
        ]);
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'notes' => 'nullable|string',
        ]);

        $enrollment->update($validated);

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Enrollment updated successfully!');
    }

    public function destroy(Enrollment $enrollment)
    {
        if ($enrollment->status === 'confirmed') {
            return back()->with('error', 'Cannot delete confirmed enrollment!');
        }

        $enrollment->delete();

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Enrollment deleted successfully!');
    }
}
