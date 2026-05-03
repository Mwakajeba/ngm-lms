<?php

namespace App\Http\Controllers;

use App\Models\ArrearsClassification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ArrearsClassificationController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('manage system configurations'), 403);

        return view('settings.arrears-classifications.index');
    }

    public function data(Request $request)
    {
        abort_unless(auth()->user()->can('manage system configurations'), 403);

        $query = ArrearsClassification::query()
            ->forCompany()
            ->orderBy('sort_order')
            ->orderBy('id');

        return DataTables::of($query)
            ->addColumn('status_badge', function (ArrearsClassification $c) {
                $statusColors = [
                    'Current' => 'success',
                    'Past Due' => 'info',
                    'Watch' => 'primary',
                    'Especially Mentioned' => 'primary',
                    'Substandard' => 'warning',
                    'Doubtful' => 'danger',
                    'Loss/NPL' => 'dark',
                ];
                $color = $statusColors[$c->status] ?? 'secondary';

                return '<span class="badge bg-'.$color.'">'.e($c->status).'</span>';
            })
            ->addColumn('provision_formatted', function (ArrearsClassification $c) {
                return '<strong>'.number_format((float) $c->provision_percentage, 2).'%</strong>';
            })
            ->editColumn('comments', function (ArrearsClassification $c) {
                return $c->comments ? e(Str::limit($c->comments, 120)) : '-';
            })
            ->addColumn('active_badge', function (ArrearsClassification $c) {
                return $c->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->editColumn('bucket_label', function (ArrearsClassification $c) {
                return '<span class="badge bg-secondary">'.e($c->bucket_label).'</span>';
            })
            ->addColumn('actions', function (ArrearsClassification $c) {
                $payload = [
                    'id' => $c->id,
                    'days_from' => $c->days_from,
                    'days_to' => $c->days_to,
                    'bucket_label' => $c->bucket_label,
                    'status' => $c->status,
                    'provision_percentage' => (float) $c->provision_percentage,
                    'sort_order' => $c->sort_order,
                    'is_active' => (bool) $c->is_active,
                    'comments' => $c->comments,
                ];
                $row = e(json_encode($payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT));

                return '<button type="button" class="btn btn-sm btn-outline-primary btn-edit-classification" data-row="'.$row.'"><i class="bx bx-edit"></i></button>'
                    .'<button type="button" class="btn btn-sm btn-outline-danger delete-classification-btn" data-id="'.$c->id.'" data-bucket-label="'.e($c->bucket_label).'"><i class="bx bx-trash"></i></button>';
            })
            ->rawColumns(['bucket_label', 'status_badge', 'provision_formatted', 'active_badge', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('manage system configurations'), 403);

        $validated = $request->validate([
            'days_from' => 'required|integer|min:0',
            'days_to' => 'nullable|integer|min:0',
            'bucket_label' => 'required|string|max:191',
            'status' => 'required|string|max:80',
            'provision_percentage' => 'required|numeric|min:0|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:0,1',
            'comments' => 'nullable|string|max:5000',
        ]);

        if (isset($validated['days_to']) && $validated['days_to'] < $validated['days_from']) {
            return redirect()->back()->withInput()->withErrors(['days_to' => 'Days to must be greater than or equal to days from.']);
        }

        ArrearsClassification::create([
            'company_id' => current_company_id(),
            'days_from' => $validated['days_from'],
            'days_to' => $validated['days_to'] ?? null,
            'bucket_label' => $validated['bucket_label'],
            'status' => $validated['status'],
            'provision_percentage' => $validated['provision_percentage'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => ($validated['is_active'] ?? '1') === '1' || ($validated['is_active'] ?? '1') === 1,
            'comments' => $validated['comments'] ?? null,
        ]);

        return redirect()->route('settings.arrears-classifications.index')
            ->with('success', 'Arrears classification created successfully.');
    }

    public function update(Request $request, ArrearsClassification $arrearsClassification)
    {
        abort_unless(auth()->user()->can('manage system configurations'), 403);
        abort_if($arrearsClassification->company_id !== current_company_id(), 403);

        $validated = $request->validate([
            'days_from' => 'required|integer|min:0',
            'days_to' => 'nullable|integer|min:0',
            'bucket_label' => 'required|string|max:191',
            'status' => 'required|string|max:80',
            'provision_percentage' => 'required|numeric|min:0|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:0,1',
            'comments' => 'nullable|string|max:5000',
        ]);

        if (isset($validated['days_to']) && $validated['days_to'] < $validated['days_from']) {
            return redirect()->back()->withInput()->withErrors(['days_to' => 'Days to must be greater than or equal to days from.']);
        }

        $arrearsClassification->update([
            'days_from' => $validated['days_from'],
            'days_to' => $validated['days_to'] ?? null,
            'bucket_label' => $validated['bucket_label'],
            'status' => $validated['status'],
            'provision_percentage' => $validated['provision_percentage'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => ($validated['is_active'] ?? '1') === '1' || ($validated['is_active'] ?? '1') === 1,
            'comments' => $validated['comments'] ?? null,
        ]);

        return redirect()->route('settings.arrears-classifications.index')
            ->with('success', 'Arrears classification updated successfully.');
    }

    public function destroy(Request $request, ArrearsClassification $arrearsClassification)
    {
        abort_unless(auth()->user()->can('manage system configurations'), 403);
        abort_if($arrearsClassification->company_id !== current_company_id(), 403);

        $arrearsClassification->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Classification deleted.']);
        }

        return redirect()->route('settings.arrears-classifications.index')
            ->with('success', 'Arrears classification deleted.');
    }

    public function seedDefaults(Request $request)
    {
        abort_unless(auth()->user()->can('manage system configurations'), 403);

        if (ArrearsClassification::forCompany()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Classifications already exist for this company. Use “Clear all classifications” first, then create defaults again.',
            ], 422);
        }

        // Tanzania-oriented default buckets (DPD ranges and provision rates)
        $defaults = [
            [
                'days_from' => 0,
                'days_to' => 5,
                'bucket_label' => '0-5',
                'status' => 'Current',
                'provision_percentage' => 1,
                'sort_order' => 1,
                'comments' => 'Tanzania default: current (1% provision).',
            ],
            [
                'days_from' => 6,
                'days_to' => 30,
                'bucket_label' => '6-30',
                'status' => 'Especially Mentioned',
                'provision_percentage' => 5,
                'sort_order' => 2,
                'comments' => 'Especially mentioned / early watch tier (5%).',
            ],
            [
                'days_from' => 31,
                'days_to' => 60,
                'bucket_label' => '31-60',
                'status' => 'Substandard',
                'provision_percentage' => 25,
                'sort_order' => 3,
                'comments' => null,
            ],
            [
                'days_from' => 61,
                'days_to' => 90,
                'bucket_label' => '61-90',
                'status' => 'Doubtful',
                'provision_percentage' => 50,
                'sort_order' => 4,
                'comments' => null,
            ],
            [
                'days_from' => 91,
                'days_to' => null,
                'bucket_label' => '91+',
                'status' => 'Loss/NPL',
                'provision_percentage' => 100,
                'sort_order' => 5,
                'comments' => 'Non-performing / loss bucket (100%).',
            ],
        ];

        foreach ($defaults as $row) {
            ArrearsClassification::create([
                'company_id' => current_company_id(),
                'days_from' => $row['days_from'],
                'days_to' => $row['days_to'],
                'bucket_label' => $row['bucket_label'],
                'status' => $row['status'],
                'provision_percentage' => $row['provision_percentage'],
                'sort_order' => $row['sort_order'],
                'is_active' => true,
                'comments' => $row['comments'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tanzania default arrears classifications were created successfully.',
        ]);
    }

    /**
     * Remove all arrears classifications for the current company (e.g. before re-seeding defaults).
     */
    public function clearAll(Request $request)
    {
        abort_unless(auth()->user()->can('manage system configurations'), 403);

        $count = ArrearsClassification::forCompany()->count();

        if ($count === 0) {
            return response()->json([
                'success' => true,
                'message' => 'There were no classifications to clear.',
            ]);
        }

        ArrearsClassification::forCompany()->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Cleared {$count} classification(s). You can now create Tanzania defaults again.",
            ]);
        }

        return redirect()->route('settings.arrears-classifications.index')
            ->with('success', "Cleared {$count} classification(s).");
    }
}
