<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Backup;
use App\Services\BackupService;
use App\Services\AiAssistantService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Vinkla\Hashids\Facades\Hashids;

class SettingsController extends Controller
{
    public function __construct()
    {
        // Middleware is applied in routes/web.php
    }

    public function index()
    {
        $company = Company::find(current_company_id());
        $branches = Branch::forCompany()->active()->get();
        
        return view('settings.index', compact('company', 'branches'));
    }

    public function companySettings()
    {
        $company = Company::find(current_company_id());
        
        return view('settings.company', compact('company'));
    }

    public function updateCompanySettings(Request $request)
    {
        $company = Company::find(current_company_id());
        
        // Custom validation for email to handle existing email
        $emailRules = 'required|email';
        if ($request->email !== $company->email) {
            $emailRules .= '|unique:companies,email,' . $company->id . ',id';
        }
        
        // Custom validation for license_number to handle existing license
        $licenseRules = 'required|string';
        if ($request->license_number !== $company->license_number) {
            $licenseRules .= '|unique:companies,license_number,' . $company->id . ',id';
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => $emailRules,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'license_number' => $licenseRules,
            'registration_date' => 'required|date',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bg_color' => 'nullable|string|max:7',
            'txt_color' => 'nullable|string|max:7',
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            
            $logo = $request->file('logo');
            $logoName = 'company_' . $company->id . '_' . time() . '.' . $logo->getClientOriginalExtension();
            $logoPath = $logo->storeAs('uploads/companies', $logoName, 'public');
            $data['logo'] = $logoPath;
        }

        $company->update($data);

        return redirect()->route('settings.company')->with('success', 'Company settings updated successfully!');
    }

    public function branchSettings()
    {
        $branches = Branch::forCompany()->paginate(10);
        
        return view('settings.branches', compact('branches'));
    }

    public function createBranch()
    {
        return view('settings.branches.create');
    }

    public function storeBranch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:branches,email,NULL,id,company_id,' . current_company_id(),
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'location' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $branch = Branch::create([
            'company_id' => current_company_id(),
            'name' => $request->name,
            'branch_name' => $request->branch_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'location' => $request->location,
            'manager_name' => $request->manager_name,
            'branch_id' => \Illuminate\Support\Str::uuid(),
            'status' => $request->status,
        ]);

        return redirect()->route('settings.branches')->with('success', 'Branch created successfully!');
    }

    public function editBranch(Branch $branch)
    {
        // Ensure branch belongs to current company
        if ($branch->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        return view('settings.branches.edit', compact('branch'));
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        // Ensure branch belongs to current company
        if ($branch->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        // Custom validation for email to handle existing email
        $emailRules = 'nullable|email';
        if ($request->email !== $branch->email) {
            $emailRules .= '|unique:branches,email,' . $branch->id . ',id,company_id,' . current_company_id();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'email' => $emailRules,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'location' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $branch->update($request->all());

        return redirect()->route('settings.branches')->with('success', 'Branch updated successfully!');
    }

    public function destroyBranch(Branch $branch)
    {
        // Ensure branch belongs to current company
        if ($branch->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        // Check if branch has users
        if ($branch->users()->count() > 0) {
            return redirect()->route('settings.branches')->with('error', 'Cannot delete branch with active users.');
        }

        $branch->delete();

        return redirect()->route('settings.branches')->with('success', 'Branch deleted successfully!');
    }

    public function userSettings()
    {
        $user = auth()->user();
        $user->load(['branch', 'company', 'roles']);
        
        return view('settings.user', compact('user'));
    }

    public function updateUserSettings(Request $request)
    {
        $user = auth()->user();
        
        // Custom validation for email to handle existing email
        $emailRules = 'nullable|email';
        if ($request->email !== $user->email) {
            $emailRules .= '|unique:users,email,' . $user->id . ',id,company_id,' . current_company_id();
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id . ',id,company_id,' . current_company_id(),
            'email' => $emailRules,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        // Verify current password if changing password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
        }

        $userData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ];

        if ($request->filled('new_password')) {
            $userData['password'] = Hash::make($request->new_password);
        }

        $user->update($userData);

        return redirect()->route('settings.user')->with('success', 'User settings updated successfully!');
    }

    public function systemSettings()
    {
        $groups = [
            'general' => 'General Settings',
            'email' => 'Email Configuration',
            'security' => 'Security Settings',
            'backup' => 'Backup Configuration',
            'maintenance' => 'Maintenance Settings',
            'microfinance' => 'Microfinance Settings'
        ];

        $groupIcons = [
            'general' => 'bx-cog',
            'email' => 'bx-envelope',
            'security' => 'bx-shield',
            'backup' => 'bx-data',
            'maintenance' => 'bx-wrench',
            'microfinance' => 'bx-money'
        ];

        $timezones = [
            'Africa/Dar_es_Salaam',
            'Africa/Nairobi',
            'Africa/Kampala',
            'Africa/Kigali',
            'Africa/Bujumbura',
            'UTC',
            'America/New_York',
            'America/Chicago',
            'America/Denver',
            'America/Los_Angeles',
            'Europe/London',
            'Europe/Paris',
            'Europe/Berlin',
            'Asia/Tokyo',
            'Asia/Shanghai',
            'Asia/Kolkata',
            'Australia/Sydney',
            'Africa/Cairo',
            'Africa/Lagos',
            'America/Sao_Paulo',
            'Pacific/Auckland'
        ];

        $settings = [];
        foreach ($groups as $groupKey => $groupName) {
            $settings[$groupKey] = \App\Models\SystemSetting::getByGroup($groupKey);
        }

        return view('settings.system', compact('groups', 'groupIcons', 'timezones', 'settings'));
    }

    public function updateSystemSettings(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        try {
            foreach ($request->settings as $key => $value) {
                $setting = \App\Models\SystemSetting::where('key', $key)->first();
                
                if ($setting) {
                    // Handle different input types
                    if ($setting->type === 'boolean') {
                        $value = $value === '1' || $value === 'true' || $value === 'on';
                    } elseif ($setting->type === 'integer') {
                        $value = (int) $value;
                    }
                    
                    $setting->update(['value' => $value]);
                }
            }

            // Clear cache
            \App\Models\SystemSetting::clearCache();

            return redirect()->route('settings.system')->with('success', 'System settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('settings.system')->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    public function resetSystemSettings()
    {
        try {
            \App\Models\SystemSetting::truncate();
            \App\Models\SystemSetting::initializeDefaults();
            
            return redirect()->route('settings.system')->with('success', 'System settings reset to defaults successfully!');
        } catch (\Exception $e) {
            return redirect()->route('settings.system')->with('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }



    /**
     * Test email configuration
     */
    public function testEmailConfig()
    {
        try {
            $result = \App\Services\SystemSettingService::testEmailConfig();
            
            if ($result['success']) {
                return response()->json(['success' => true, 'message' => $result['message']]);
            } else {
                return response()->json(['success' => false, 'message' => $result['message']], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Email test failed: ' . $e->getMessage()], 500);
        }
    }

    public function backupSettings()
    {
        $backupService = new BackupService();
        $backups = Backup::forCompany()->orderBy('created_at', 'desc')->paginate(10);
        $stats = $backupService->getBackupStats();
        
        return view('settings.backup', compact('backups', 'stats'));
    }

    public function createBackup(Request $request)
    {
        $request->validate([
            'type' => 'required|in:database,files,full',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $backupService = new BackupService();
            
            switch ($request->type) {
                case 'database':
                    $backup = $backupService->createDatabaseBackup($request->description);
                    break;
                case 'files':
                    $backup = $backupService->createFilesBackup($request->description);
                    break;
                case 'full':
                    $backup = $backupService->createFullBackup($request->description);
                    break;
                default:
                    throw new \Exception('Invalid backup type');
            }

            return redirect()->route('settings.backup')->with('success', ucfirst($request->type) . ' backup created successfully!');

        } catch (\Exception $e) {
            return redirect()->route('settings.backup')->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_id' => 'required|exists:backups,id',
        ]);

        try {
            $backup = Backup::forCompany()->findOrFail($request->backup_id);
            $backupService = new BackupService();
            
            $backupService->restoreBackup($backup);

            return redirect()->route('settings.backup')->with('success', 'Backup restored successfully!');

        } catch (\Exception $e) {
            return redirect()->route('settings.backup')->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function downloadBackup($hash_id)
    {
        // Decode hash ID to get backup ID
        $id = Hashids::decode($hash_id);
        if (empty($id)) {
            abort(404, 'Backup not found.');
        }

        $backup = Backup::forCompany()->find($id[0]);
        if (!$backup) {
            abort(404, 'Backup not found.');
        }

        $fullPath = storage_path('app/' . $backup->file_path);
        if (!file_exists($fullPath)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($fullPath, $backup->filename);
    }

    public function deleteBackup($hash_id)
    {
        // Decode hash ID to get backup ID
        $id = Hashids::decode($hash_id);
        if (empty($id)) {
            abort(404, 'Backup not found.');
        }

        $backup = Backup::forCompany()->find($id[0]);
        if (!$backup) {
            abort(404, 'Backup not found.');
        }

        try {
            $backup->deleteFile();
            $backup->delete();

            return redirect()->route('settings.backup')->with('success', 'Backup deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->route('settings.backup')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function cleanOldBackups(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $backupService = new BackupService();
            $deletedCount = $backupService->cleanOldBackups($request->days);

            return redirect()->route('settings.backup')->with('success', "{$deletedCount} old backups cleaned successfully!");

        } catch (\Exception $e) {
            return redirect()->route('settings.backup')->with('error', 'Clean failed: ' . $e->getMessage());
        }
    }

    /**
     * AI Assistant Settings
     */
    public function aiAssistantSettings()
    {
        return view('settings.ai-assistant');
    }

    /**
     * Handle AI chat requests
     */
    public function aiChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            $aiService = new AiAssistantService();
            $response = $aiService->processMessage($request->message);

            return response()->json([
                'success' => true,
                'response' => $response
            ]);

        } catch (\Exception $e) {
            \Log::error('AI Chat Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'AI processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
