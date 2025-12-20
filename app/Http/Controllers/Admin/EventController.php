<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CourseSchedule;
use App\Models\Event;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Display a listing of events and courses
     */
    public function index(Request $request)
    {
        $query = Event::with(['category', 'instructors']);

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type') && $request->get('type') !== 'all') {
            $query->where('type', $request->get('type'));
        }

        // Filter by status
        if ($request->filled('status') && $request->get('status') !== 'all') {
            $isActive = $request->get('status') === 'active';
            $query->where('is_active', $isActive);
        }

        // Filter by category
        if ($request->filled('category_id') && $request->get('category_id') !== 'all') {
            $query->where('category_id', $request->get('category_id'));
        }

        // Filter by featured
        if ($request->filled('featured') && $request->get('featured') !== 'all') {
            $isFeatured = $request->get('featured') === 'yes';
            $query->where('is_featured', $isFeatured);
        }

        $events = $query->latest('created_at')->paginate(15)->withQueryString();

        // Get categories for filter dropdown
        $categories = Category::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => Event::count(),
            'events' => Event::where('type', 'event')->count(),
            'courses' => Event::where('type', 'course')->count(),
            'active' => Event::where('is_active', true)->count(),
            'featured' => Event::where('is_featured', true)->count(),
            'enrollments' => Event::withCount('enrollments')->get()->sum('enrollments_count'),
        ];

        return Inertia::render('Admin/Events/Index', [
            'events' => $events,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => [
                'search' => $request->get('search'),
                'type' => $request->get('type', 'all'),
                'status' => $request->get('status', 'all'),
                'category_id' => $request->get('category_id', 'all'),
                'featured' => $request->get('featured', 'all'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new event/course
     */
    public function create()
    {
        $categories = Category::active()->ordered()->get();
        $instructors = Instructor::where('is_active', true)->get();

        return Inertia::render('Admin/Events/Create', [
            'categories' => $categories,
            'instructors' => $instructors,
        ]);
    }

    /**
     * Store a newly created event/course
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:event,course',
            'category_id' => 'required|exists:categories,id',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'flier' => 'nullable|image|max:2048',
            'location' => 'required|string|max:255',
            'location_type' => 'required|in:physical,online,hybrid',
            'meeting_link' => 'nullable|url',
            'start_date' => 'nullable|date',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'duration_hours' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'instructor_ids' => 'nullable|array',
            'instructor_ids.*' => 'exists:instructors,id',
        ]);

        // Generate slug
        $validated['slug'] = Str::slug($validated['title']);
        $validated['created_by'] = auth()->id();

        // Auto-publish if not explicitly set
        if (! isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Handle file upload
        if ($request->hasFile('flier')) {
            $path = $request->file('flier')->store('events', 'public');
            $validated['flier'] = $path;
        }

        $event = Event::create($validated);

        // Attach instructors
        if (! empty($validated['instructor_ids'])) {
            $event->instructors()->attach($validated['instructor_ids']);
        }

        return redirect()->route('admin.events.show', $event->id)
            ->with('success', ucfirst($event->type).' created successfully!');
    }

    /**
     * Display the specified event/course
     */
    public function show(Event $event)
    {
        $event->load(['category', 'instructors', 'schedules', 'prerequisites.prerequisiteCourse', 'enrollments']);

        return Inertia::render('Admin/Events/Show', [
            'event' => $event,
        ]);
    }

    /**
     * Show the form for editing the specified event/course
     */
    public function edit(Event $event)
    {
        $event->load(['instructors', 'schedules', 'prerequisites']);

        $categories = Category::active()->ordered()->get();
        $instructors = Instructor::where('is_active', true)->get();

        return Inertia::render('Admin/Events/Edit', [
            'event' => $event,
            'categories' => $categories,
            'instructors' => $instructors,
        ]);
    }

    /**
     * Update the specified event/course
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:event,course',
            'category_id' => 'required|exists:categories,id',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'flier' => 'nullable|image|max:2048',
            'location' => 'required|string|max:255',
            'location_type' => 'required|in:physical,online,hybrid',
            'meeting_link' => 'nullable|url',
            'start_date' => 'nullable|date',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'duration_hours' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'instructor_ids' => 'nullable|array',
            'instructor_ids.*' => 'exists:instructors,id',
        ]);

        // Update slug if title changed
        if ($validated['title'] !== $event->title) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Auto-publish if not explicitly set and event is currently unpublished
        if (! isset($validated['published_at']) && is_null($event->published_at)) {
            $validated['published_at'] = now();
        }

        // Handle file upload
        if ($request->hasFile('flier')) {
            // Delete old flier
            if ($event->flier) {
                \Storage::disk('public')->delete($event->flier);
            }

            $path = $request->file('flier')->store('events', 'public');
            $validated['flier'] = $path;
        }

        $event->update($validated);

        // Sync instructors
        if (isset($validated['instructor_ids'])) {
            $event->instructors()->sync($validated['instructor_ids']);
        }

        return redirect()->route('admin.events.index')
            ->with('success', ucfirst($event->type).' updated successfully!');
    }

    /**
     * Remove the specified event/course
     */
    public function destroy(Event $event)
    {
        // Soft delete
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', ucfirst($event->type).' deleted successfully!');
    }

    /**
     * Store a new schedule for the event/course
     */
    public function storeSchedule(Request $request, Event $event)
    {
        $validated = $request->validate([
            'session_number' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $validated['event_id'] = $event->id;

        $schedule = CourseSchedule::create($validated);

        return response()->json([
            'success' => true,
            'schedule' => $schedule,
            'message' => 'Schedule added successfully!',
        ], 201);
    }

    /**
     * Update an existing schedule
     */
    public function updateSchedule(Request $request, Event $event, CourseSchedule $schedule)
    {
        if ($schedule->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule does not belong to this event.',
            ], 403);
        }

        $validated = $request->validate([
            'session_number' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_completed' => 'boolean',
        ]);

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'schedule' => $schedule->fresh(),
            'message' => 'Schedule updated successfully!',
        ]);
    }

    /**
     * Delete a schedule
     */
    public function deleteSchedule(Event $event, CourseSchedule $schedule)
    {
        if ($schedule->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule does not belong to this event.',
            ], 403);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully!',
        ]);
    }
}
