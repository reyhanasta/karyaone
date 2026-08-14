<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use App\Models\ReplacementGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $positions = Position::with(['department', 'replacementGroup'])
            ->withCount('employees')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $departments = Department::orderBy('name')->get(['id', 'name']);
        $replacementGroups = ReplacementGroup::orderBy('name')->get(['id', 'name']);

        return Inertia::render('positions/index', [
            'positions' => $positions,
            'departments' => $departments,
            'replacementGroups' => $replacementGroups,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions')->where('department_id', $request->department_id),
            ],
            'description' => 'nullable|string|max:500',
            'department_id' => 'required|exists:departments,id',
            'replacement_group_id' => 'nullable|exists:replacement_groups,id',
            'new_group_name' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            Position::create($this->positionData($validated));
        });

        return redirect()->back()->with('success', 'Position created successfully.');
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions')->where('department_id', $request->department_id)->ignore($position->id),
            ],
            'description' => 'nullable|string|max:500',
            'department_id' => 'required|exists:departments,id',
            'replacement_group_id' => 'nullable|exists:replacement_groups,id',
            'new_group_name' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($position, $validated) {
            $position->update($this->positionData($validated));
        });

        return redirect()->back()->with('success', 'Position updated successfully.');
    }

    /**
     * Resolve the replacement group for a position payload.
     *
     * Resolution order: new_group_name → replacement_group_id → own-named default group.
     */
    private function positionData(array $validated): array
    {
        if (! empty($validated['new_group_name'])) {
            $group = ReplacementGroup::firstOrCreate(['name' => trim($validated['new_group_name'])]);
        } elseif (! empty($validated['replacement_group_id'])) {
            $group = ReplacementGroup::findOrFail($validated['replacement_group_id']);
        } else {
            $group = ReplacementGroup::firstOrCreate(['name' => trim($validated['name'])]);
        }

        return [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'department_id' => $validated['department_id'],
            'replacement_group_id' => $group->id,
        ];
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete position with active employees.');
        }

        DB::transaction(function () use ($position) {
            $position->delete();
        });

        return redirect()->back()->with('success', 'Position deleted successfully.');
    }
}
