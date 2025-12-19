<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstructorController extends Controller
{
    /**
     * Display a listing of instructors
     */
    public function index()
    {
        $instructors = Instructor::withCount('events')
            ->latest()
            ->get();

        $stats = [
            'total' => Instructor::count(),
            'active' => Instructor::where('is_active', true)->count(),
            'inactive' => Instructor::where('is_active', false)->count(),
        ];

        return Inertia::render('Admin/Instructors/Index', [
            'instructors' => $instructors,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new instructor
     */
    public function create()
    {
        return Inertia::render('Admin/Instructors/Create');
    }

    /**
     * Store a newly created instructor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:instructors,email',
            'phone_number' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
            'specialization' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('instructors', 'public');
            $validated['profile_photo'] = $path;
        }

        Instructor::create($validated);

        return redirect()->route('admin.instructors.index')
            ->with('success', 'Instructor created successfully!');
    }

    /**
     * Display the specified instructor
     */
    public function show(Instructor $instructor)
    {
        $instructor->load(['events' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Admin/Instructors/Show', [
            'instructor' => $instructor,
        ]);
    }

    /**
     * Show the form for editing the specified instructor
     */
    public function edit(Instructor $instructor)
    {
        return Inertia::render('Admin/Instructors/Edit', [
            'instructor' => $instructor,
        ]);
    }

    /**
     * Update the specified instructor
     */
    public function update(Request $request, Instructor $instructor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:instructors,email,' . $instructor->id,
            'phone_number' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
            'specialization' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($instructor->profile_photo) {
                \Storage::disk('public')->delete($instructor->profile_photo);
            }

            $path = $request->file('profile_photo')->store('instructors', 'public');
            $validated['profile_photo'] = $path;
        }

        $instructor->update($validated);

        return redirect()->route('admin.instructors.index')
            ->with('success', 'Instructor updated successfully!');
    }

    /**
     * Remove the specified instructor
     */
    public function destroy(Instructor $instructor)
    {
        // Check if instructor has events
        if ($instructor->events()->count() > 0) {
            return back()->with('error', 'Cannot delete instructor with assigned events/courses!');
        }

        // Delete profile photo
        if ($instructor->profile_photo) {
            \Storage::disk('public')->delete($instructor->profile_photo);
        }

        $instructor->delete();

        return redirect()->route('admin.instructors.index')
            ->with('success', 'Instructor deleted successfully!');
    }
}
