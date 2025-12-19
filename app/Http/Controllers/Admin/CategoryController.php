<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = Category::withCount('events')
            ->ordered()
            ->get();

        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'events' => Category::where('type', 'event')->count(),
            'courses' => Category::where('type', 'course')->count(),
        ];

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return Inertia::render('Admin/Categories/Create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:event,course',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->load(['events' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Admin/Categories/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'type' => 'required|in:event,course',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        if ($validated['name'] !== $category->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        // Check if category has events
        if ($category->events()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing events/courses!');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}
