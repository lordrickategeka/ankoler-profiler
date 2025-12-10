<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FilterConfiguration;
use App\Models\Organization;
use Illuminate\Http\Request;

class FilterConfigurationController extends Controller
{
    public function index()
    {
        $currentOrganization = user_current_organization();

        if (!$currentOrganization) {
            return redirect()->back()->with('error', 'No organization selected.');
        }

        $filterConfigurations = FilterConfiguration::where('organization_id', $currentOrganization->id)
            ->orderBy('sort_order')
            ->get();

        return view('admin.filter-configurations.index', compact('filterConfigurations', 'currentOrganization'));
    }

    public function create()
    {
        $currentOrganization = user_current_organization();

        if (!$currentOrganization) {
            return redirect()->back()->with('error', 'No organization selected.');
        }

        $fieldTypes = ['text', 'select', 'multiselect', 'date', 'number', 'boolean'];

        return view('admin.filter-configurations.create', compact('currentOrganization', 'fieldTypes'));
    }

    public function store(Request $request)
    {
        $currentOrganization = user_current_organization();

        if (!$currentOrganization) {
            return redirect()->back()->with('error', 'No organization selected.');
        }

        $validatedData = $request->validate([
            'field_name' => 'required|string|max:255',
            'field_type' => 'required|in:text,select,multiselect,date,number,boolean',
            'field_options' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $validatedData['organization_id'] = $currentOrganization->id;

        FilterConfiguration::create($validatedData);

        return redirect()->route('admin.filter-configurations.index')
            ->with('success', 'Filter configuration created successfully.');
    }

    public function edit(FilterConfiguration $filterConfiguration)
    {
        $currentOrganization = user_current_organization();

        if ($filterConfiguration->organization_id !== $currentOrganization->id) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $fieldTypes = ['text', 'select', 'multiselect', 'date', 'number', 'boolean'];

        return view('admin.filter-configurations.edit', compact('filterConfiguration', 'fieldTypes', 'currentOrganization'));
    }

    public function update(Request $request, FilterConfiguration $filterConfiguration)
    {
        $currentOrganization = user_current_organization();

        if ($filterConfiguration->organization_id !== $currentOrganization->id) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $validatedData = $request->validate([
            'field_name' => 'required|string|max:255',
            'field_type' => 'required|in:text,select,multiselect,date,number,boolean',
            'field_options' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $filterConfiguration->update($validatedData);

        return redirect()->route('admin.filter-configurations.index')
            ->with('success', 'Filter configuration updated successfully.');
    }

    public function destroy(FilterConfiguration $filterConfiguration)
    {
        $currentOrganization = user_current_organization();

        if ($filterConfiguration->organization_id !== $currentOrganization->id) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $filterConfiguration->delete();

        return redirect()->route('admin.filter-configurations.index')
            ->with('success', 'Filter configuration deleted successfully.');
    }

    public function toggle(FilterConfiguration $filterConfiguration)
    {
        $currentOrganization = user_current_organization();

        if ($filterConfiguration->organization_id !== $currentOrganization->id) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $filterConfiguration->update(['is_active' => !$filterConfiguration->is_active]);

        return redirect()->back()->with('success', 'Filter configuration status updated.');
    }
}
