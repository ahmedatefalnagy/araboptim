<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $categories = ItemCategory::withCount('items')->latest()->get();
        return Inertia::render('ItemCategories/Index', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:item_categories',
            'description' => 'nullable|string',
        ]);

        ItemCategory::create($validated);

        return redirect()->route('item-categories.index')->with('message', 'تم إضافة المجموعة بنجاح');
    }

    public function update(Request $request, ItemCategory $itemCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:item_categories,name,' . $itemCategory->id,
            'description' => 'nullable|string',
        ]);

        $itemCategory->update($validated);

        return redirect()->route('item-categories.index')->with('message', 'تم تحديث المجموعة بنجاح');
    }

    public function destroy(ItemCategory $itemCategory)
    {
        if ($itemCategory->items()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف المجموعة لوجود أصناف مرتبطة بها.');
        }

        $itemCategory->delete();

        return redirect()->route('item-categories.index')->with('message', 'تم حذف المجموعة بنجاح');
    }
}
