<?php

use Illuminate\Support\Facades\Route;

////////////////////////////////////////////// HR & PAYROLL ROUTES ////////////////////////////////////////////////

Route::get('/hr-payroll', [App\Http\Controllers\HrPayrollController::class, 'index'])->name('hr-payroll.index')->middleware(['auth', 'company.scope', 'require.branch']);
Route::get('/hr-payroll/payroll-settings', [App\Http\Controllers\PayrollSettingsController::class, 'index'])->name('hr.payroll-settings.index')->middleware(['auth', 'company.scope', 'require.branch']);

// Payroll Approval Settings
Route::prefix('hr-payroll')->name('hr-payroll.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::get('/approval-settings', [App\Http\Controllers\PayrollApprovalSettingsController::class, 'index'])->name('approval-settings.index');
    Route::post('/approval-settings', [App\Http\Controllers\PayrollApprovalSettingsController::class, 'store'])->name('approval-settings.store');
    Route::get('/approval-settings/users-by-branch', [App\Http\Controllers\PayrollApprovalSettingsController::class, 'getUsersByBranch'])->name('approval-settings.users-by-branch');

    // Payment Approval Settings
    Route::get('/payment-approval-settings', [App\Http\Controllers\Hr\PayrollPaymentApprovalSettingsController::class, 'index'])->name('payment-approval-settings.index');
    Route::post('/payment-approval-settings', [App\Http\Controllers\Hr\PayrollPaymentApprovalSettingsController::class, 'store'])->name('payment-approval-settings.store');
    Route::get('/payment-approval-settings/users-by-branch', [App\Http\Controllers\Hr\PayrollPaymentApprovalSettingsController::class, 'getUsersByBranch'])->name('payment-approval-settings.users-by-branch');

    // Overtime Approval Settings
    Route::get('/overtime-approval-settings', [App\Http\Controllers\Hr\OvertimeApprovalSettingsController::class, 'index'])->name('overtime-approval-settings.index');
    Route::post('/overtime-approval-settings', [App\Http\Controllers\Hr\OvertimeApprovalSettingsController::class, 'store'])->name('overtime-approval-settings.store');
    Route::get('/overtime-approval-settings/users-by-branch', [App\Http\Controllers\Hr\OvertimeApprovalSettingsController::class, 'getUsersByBranch'])->name('overtime-approval-settings.users-by-branch');

    // Timesheet Approval Settings
    Route::get('/timesheet-approval-settings', [App\Http\Controllers\Hr\TimesheetApprovalSettingsController::class, 'index'])->name('timesheet-approval-settings.index');
    Route::post('/timesheet-approval-settings', [App\Http\Controllers\Hr\TimesheetApprovalSettingsController::class, 'store'])->name('timesheet-approval-settings.store');
    Route::get('/timesheet-approval-settings/users-by-branch', [App\Http\Controllers\Hr\TimesheetApprovalSettingsController::class, 'getUsersByBranch'])->name('timesheet-approval-settings.users-by-branch');

    // Vacancy Requisition Approval Settings
    Route::get('/vacancy-requisition-approval-settings', [App\Http\Controllers\Hr\VacancyRequisitionApprovalSettingsController::class, 'index'])->name('vacancy-requisition-approval-settings.index');
    Route::post('/vacancy-requisition-approval-settings', [App\Http\Controllers\Hr\VacancyRequisitionApprovalSettingsController::class, 'store'])->name('vacancy-requisition-approval-settings.store');
    Route::get('/vacancy-requisition-approval-settings/users-by-branch', [App\Http\Controllers\Hr\VacancyRequisitionApprovalSettingsController::class, 'getUsersByBranch'])->name('vacancy-requisition-approval-settings.users-by-branch');
});

Route::prefix('hr-payroll')->name('hr.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::resource('departments', App\Http\Controllers\Hr\DepartmentController::class);
    Route::resource('positions', App\Http\Controllers\Hr\PositionController::class);

    // Phase 1: Core HR Enhancement routes
    Route::resource('job-grades', App\Http\Controllers\Hr\JobGradeController::class);
    Route::resource('contracts', App\Http\Controllers\Hr\ContractController::class);
    Route::post('contracts/{contract}/attachments', [App\Http\Controllers\Hr\ContractController::class, 'storeAttachment'])->name('contracts.attachments.store');
    Route::delete('contracts/{contract}/attachments/{attachment}', [App\Http\Controllers\Hr\ContractController::class, 'deleteAttachment'])->name('contracts.attachments.destroy');
    Route::get('employee-compliance/check-existing', [App\Http\Controllers\Hr\EmployeeComplianceController::class, 'checkExisting'])->name('employee-compliance.check-existing');
    Route::resource('employee-compliance', App\Http\Controllers\Hr\EmployeeComplianceController::class);

    // Phase 2: Time, Attendance & Leave Enhancement routes
    Route::resource('work-schedules', App\Http\Controllers\Hr\WorkScheduleController::class);
    Route::resource('shifts', App\Http\Controllers\Hr\ShiftController::class);
    Route::resource('employee-schedules', App\Http\Controllers\Hr\EmployeeScheduleController::class);
    Route::resource('attendance', App\Http\Controllers\Hr\AttendanceController::class);
    Route::post('attendance/{attendance}/approve', [App\Http\Controllers\Hr\AttendanceController::class, 'approve'])->name('attendance.approve');
    Route::resource('timesheets', App\Http\Controllers\Hr\TimesheetController::class);
    Route::post('timesheets/{timesheet}/submit', [App\Http\Controllers\Hr\TimesheetController::class, 'submit'])->name('timesheets.submit');
    Route::post('timesheets/{timesheet}/approve', [App\Http\Controllers\Hr\TimesheetController::class, 'approve'])->name('timesheets.approve');
    Route::post('timesheets/{timesheet}/reject', [App\Http\Controllers\Hr\TimesheetController::class, 'reject'])->name('timesheets.reject');
    Route::resource('overtime-rules', App\Http\Controllers\Hr\OvertimeRuleController::class);
    Route::get('overtime-requests/get-overtime-rate', [App\Http\Controllers\Hr\OvertimeRequestController::class, 'getOvertimeRate'])->name('overtime-requests.get-overtime-rate');
    Route::resource('overtime-requests', App\Http\Controllers\Hr\OvertimeRequestController::class);
    Route::post('overtime-requests/{overtimeRequest}/approve', [App\Http\Controllers\Hr\OvertimeRequestController::class, 'approve'])->name('overtime-requests.approve');
    Route::post('overtime-requests/{overtimeRequest}/reject', [App\Http\Controllers\Hr\OvertimeRequestController::class, 'reject'])->name('overtime-requests.reject');
    Route::resource('holiday-calendars', App\Http\Controllers\Hr\HolidayCalendarController::class);
    Route::post('holiday-calendars/{holidayCalendar}/add-holiday', [App\Http\Controllers\Hr\HolidayCalendarController::class, 'addHoliday'])->name('holiday-calendars.add-holiday');
    Route::post('holiday-calendars/{holidayCalendar}/seed-tanzania', [App\Http\Controllers\Hr\HolidayCalendarController::class, 'seedTanzaniaHolidays'])->name('holiday-calendars.seed-tanzania');
    Route::delete('holiday-calendars/holidays/{holidayCalendarDate}', [App\Http\Controllers\Hr\HolidayCalendarController::class, 'removeHoliday'])->name('holiday-calendars.remove-holiday');

    // Phase 3: Payroll Enhancement & Statutory Compliance routes
    Route::resource('payroll-calendars', App\Http\Controllers\Hr\PayrollCalendarController::class);
    Route::post('payroll-calendars/{payrollCalendar}/lock', [App\Http\Controllers\Hr\PayrollCalendarController::class, 'lock'])->name('payroll-calendars.lock');
    Route::post('payroll-calendars/{payrollCalendar}/unlock', [App\Http\Controllers\Hr\PayrollCalendarController::class, 'unlock'])->name('payroll-calendars.unlock');
    Route::resource('pay-groups', App\Http\Controllers\Hr\PayGroupController::class);
    Route::resource('salary-components', App\Http\Controllers\Hr\SalaryComponentController::class);
    // Custom routes must come BEFORE resource route to avoid conflicts
    Route::get('employee-salary-structure/bulk-assign', [App\Http\Controllers\Hr\EmployeeSalaryStructureController::class, 'bulkAssignForm'])->name('employee-salary-structure.bulk-assign-form');
    Route::post('employee-salary-structure/bulk-assign', [App\Http\Controllers\Hr\EmployeeSalaryStructureController::class, 'bulkAssign'])->name('employee-salary-structure.bulk-assign');
    Route::get('employee-salary-structure/apply-template', [App\Http\Controllers\Hr\EmployeeSalaryStructureController::class, 'applyTemplateForm'])->name('employee-salary-structure.apply-template-form');
    Route::post('employee-salary-structure/apply-template', [App\Http\Controllers\Hr\EmployeeSalaryStructureController::class, 'applyTemplate'])->name('employee-salary-structure.apply-template');
    Route::delete('employee-salary-structure/{employee}/component/{structure}', [App\Http\Controllers\Hr\EmployeeSalaryStructureController::class, 'destroy'])->name('employee-salary-structure.destroy-component');
    Route::resource('employee-salary-structure', App\Http\Controllers\Hr\EmployeeSalaryStructureController::class)->parameters([
        'employee-salary-structure' => 'employee'
    ]);
    Route::resource('salary-structure-templates', App\Http\Controllers\Hr\SalaryStructureTemplateController::class);
    Route::resource('statutory-rules', App\Http\Controllers\Hr\StatutoryRuleController::class);
    Route::get('statutory-rules/category-options', [App\Http\Controllers\Hr\StatutoryRuleController::class, 'getCategoryOptions'])->name('statutory-rules.category-options');

    // Payroll Reports
    Route::get('payroll-reports', [App\Http\Controllers\Hr\PayrollReportController::class, 'index'])->name('payroll-reports.index');
    Route::get('payroll-reports/payroll-by-department', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollByDepartment'])->name('payroll-reports.payroll-by-department');
    Route::post('payroll-reports/payroll-by-department/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollByDepartmentExportExcel'])->name('payroll-reports.payroll-by-department.export-excel');
    Route::post('payroll-reports/payroll-by-department/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollByDepartmentExportPdf'])->name('payroll-reports.payroll-by-department.export-pdf');
    Route::get('payroll-reports/payroll-by-pay-group', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollByPayGroup'])->name('payroll-reports.payroll-by-pay-group');
    Route::post('payroll-reports/payroll-by-pay-group/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollByPayGroupExportExcel'])->name('payroll-reports.payroll-by-pay-group.export-excel');
    Route::post('payroll-reports/payroll-by-pay-group/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollByPayGroupExportPdf'])->name('payroll-reports.payroll-by-pay-group.export-pdf');
    Route::get('payroll-reports/statutory-compliance', [App\Http\Controllers\Hr\PayrollReportController::class, 'statutoryCompliance'])->name('payroll-reports.statutory-compliance');
    Route::post('payroll-reports/statutory-compliance/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'statutoryComplianceExportExcel'])->name('payroll-reports.statutory-compliance.export-excel');
    Route::post('payroll-reports/statutory-compliance/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'statutoryComplianceExportPdf'])->name('payroll-reports.statutory-compliance.export-pdf');
    Route::get('payroll-reports/statutory-compliance-enhanced', [App\Http\Controllers\Hr\PayrollReportController::class, 'statutoryComplianceEnhanced'])->name('payroll-reports.statutory-compliance-enhanced');
    Route::post('payroll-reports/statutory-compliance-enhanced/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'statutoryComplianceEnhancedExportExcel'])->name('payroll-reports.statutory-compliance-enhanced.export-excel');
    Route::post('payroll-reports/statutory-compliance-enhanced/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'statutoryComplianceEnhancedExportPdf'])->name('payroll-reports.statutory-compliance-enhanced.export-pdf');
    Route::get('payroll-reports/employee-payroll-history', [App\Http\Controllers\Hr\PayrollReportController::class, 'employeePayrollHistory'])->name('payroll-reports.employee-payroll-history');
    Route::post('payroll-reports/employee-payroll-history/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'employeePayrollHistoryExportExcel'])->name('payroll-reports.employee-payroll-history.export-excel');
    Route::post('payroll-reports/employee-payroll-history/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'employeePayrollHistoryExportPdf'])->name('payroll-reports.employee-payroll-history.export-pdf');
    Route::get('payroll-reports/payroll-cost-analysis', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollCostAnalysis'])->name('payroll-reports.payroll-cost-analysis');
    Route::post('payroll-reports/payroll-cost-analysis/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollCostAnalysisExportExcel'])->name('payroll-reports.payroll-cost-analysis.export-excel');
    Route::post('payroll-reports/payroll-cost-analysis/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollCostAnalysisExportPdf'])->name('payroll-reports.payroll-cost-analysis.export-pdf');
    Route::get('payroll-reports/payroll-audit-trail', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollAuditTrail'])->name('payroll-reports.payroll-audit-trail');
    Route::post('payroll-reports/payroll-audit-trail/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollAuditTrailExportExcel'])->name('payroll-reports.payroll-audit-trail.export-excel');
    Route::post('payroll-reports/payroll-audit-trail/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollAuditTrailExportPdf'])->name('payroll-reports.payroll-audit-trail.export-pdf');
    Route::get('payroll-reports/year-to-date-summary', [App\Http\Controllers\Hr\PayrollReportController::class, 'yearToDateSummary'])->name('payroll-reports.year-to-date-summary');
    Route::post('payroll-reports/year-to-date-summary/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'yearToDateSummaryExportExcel'])->name('payroll-reports.year-to-date-summary.export-excel');
    Route::post('payroll-reports/year-to-date-summary/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'yearToDateSummaryExportPdf'])->name('payroll-reports.year-to-date-summary.export-pdf');
    Route::get('payroll-reports/payroll-variance', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollVariance'])->name('payroll-reports.payroll-variance');
    Route::post('payroll-reports/payroll-variance/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollVarianceExportExcel'])->name('payroll-reports.payroll-variance.export-excel');
    Route::post('payroll-reports/payroll-variance/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollVarianceExportPdf'])->name('payroll-reports.payroll-variance.export-pdf');
    Route::get('payroll-reports/bank-payment', [App\Http\Controllers\Hr\PayrollReportController::class, 'bankPayment'])->name('payroll-reports.bank-payment');
    Route::post('payroll-reports/bank-payment/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'bankPaymentExportExcel'])->name('payroll-reports.bank-payment.export-excel');
    Route::post('payroll-reports/bank-payment/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'bankPaymentExportPdf'])->name('payroll-reports.bank-payment.export-pdf');
    Route::get('payroll-reports/overtime', [App\Http\Controllers\Hr\PayrollReportController::class, 'overtimeReport'])->name('payroll-reports.overtime');
    Route::post('payroll-reports/overtime/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'overtimeExportExcel'])->name('payroll-reports.overtime.export-excel');
    Route::post('payroll-reports/overtime/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'overtimeExportPdf'])->name('payroll-reports.overtime.export-pdf');
    Route::get('payroll-reports/payroll-summary', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollSummary'])->name('payroll-reports.payroll-summary');
    Route::post('payroll-reports/payroll-summary/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollSummaryExportExcel'])->name('payroll-reports.payroll-summary.export-excel');
    Route::post('payroll-reports/payroll-summary/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollSummaryExportPdf'])->name('payroll-reports.payroll-summary.export-pdf');
    Route::get('payroll-reports/leave', [App\Http\Controllers\Hr\PayrollReportController::class, 'leaveReport'])->name('payroll-reports.leave');
    Route::post('payroll-reports/leave/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'leaveExportExcel'])->name('payroll-reports.leave.export-excel');
    Route::post('payroll-reports/leave/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'leaveExportPdf'])->name('payroll-reports.leave.export-pdf');
    Route::get('payroll-reports/paye-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'payeRemittance'])->name('payroll-reports.paye-remittance');
    Route::post('payroll-reports/paye-remittance/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'payeRemittanceExportExcel'])->name('payroll-reports.paye-remittance.export-excel');
    Route::post('payroll-reports/paye-remittance/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'payeRemittanceExportPdf'])->name('payroll-reports.paye-remittance.export-pdf');
    Route::get('payroll-reports/nssf-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'nssfRemittance'])->name('payroll-reports.nssf-remittance');
    Route::post('payroll-reports/nssf-remittance/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'nssfRemittanceExportExcel'])->name('payroll-reports.nssf-remittance.export-excel');
    Route::post('payroll-reports/nssf-remittance/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'nssfRemittanceExportPdf'])->name('payroll-reports.nssf-remittance.export-pdf');
    Route::get('payroll-reports/nhif-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'nhifRemittance'])->name('payroll-reports.nhif-remittance');
    Route::post('payroll-reports/nhif-remittance/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'nhifRemittanceExportExcel'])->name('payroll-reports.nhif-remittance.export-excel');
    Route::post('payroll-reports/nhif-remittance/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'nhifRemittanceExportPdf'])->name('payroll-reports.nhif-remittance.export-pdf');
    Route::get('payroll-reports/wcf-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'wcfRemittance'])->name('payroll-reports.wcf-remittance');
    Route::post('payroll-reports/wcf-remittance/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'wcfRemittanceExportExcel'])->name('payroll-reports.wcf-remittance.export-excel');
    Route::post('payroll-reports/wcf-remittance/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'wcfRemittanceExportPdf'])->name('payroll-reports.wcf-remittance.export-pdf');
    Route::get('payroll-reports/sdl-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'sdlRemittance'])->name('payroll-reports.sdl-remittance');
    Route::post('payroll-reports/sdl-remittance/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'sdlRemittanceExportExcel'])->name('payroll-reports.sdl-remittance.export-excel');
    Route::post('payroll-reports/sdl-remittance/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'sdlRemittanceExportPdf'])->name('payroll-reports.sdl-remittance.export-pdf');
    Route::get('payroll-reports/heslb-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'heslbRemittance'])->name('payroll-reports.heslb-remittance');
    Route::post('payroll-reports/heslb-remittance/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'heslbRemittanceExportExcel'])->name('payroll-reports.heslb-remittance.export-excel');
    Route::post('payroll-reports/heslb-remittance/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'heslbRemittanceExportPdf'])->name('payroll-reports.heslb-remittance.export-pdf');
    Route::get('payroll-reports/combined-statutory-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'combinedStatutoryRemittance'])->name('payroll-reports.combined-statutory-remittance');
    Route::post('payroll-reports/combined-statutory-remittance/export-excel', [App\Http\Controllers\Hr\PayrollReportController::class, 'combinedStatutoryRemittanceExportExcel'])->name('payroll-reports.combined-statutory-remittance.export-excel');
    Route::post('payroll-reports/combined-statutory-remittance/export-pdf', [App\Http\Controllers\Hr\PayrollReportController::class, 'combinedStatutoryRemittanceExportPdf'])->name('payroll-reports.combined-statutory-remittance.export-pdf');

    // Biometric Device Management
    Route::resource('biometric-devices', App\Http\Controllers\Hr\BiometricDeviceController::class);
    Route::post('biometric-devices/{biometricDevice}/sync', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'sync'])->name('biometric-devices.sync');
    Route::post('biometric-devices/{biometricDevice}/regenerate-api-key', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'regenerateApiKey'])->name('biometric-devices.regenerate-api-key');
    Route::post('biometric-devices/{biometricDevice}/process-logs', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'processPendingLogs'])->name('biometric-devices.process-logs');
    Route::post('biometric-devices/{biometricDevice}/connect', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'connect'])->name('biometric-devices.connect');
    Route::post('biometric-devices/{biometricDevice}/disconnect', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'disconnect'])->name('biometric-devices.disconnect');
    Route::post('biometric-devices/{biometricDevice}/restart', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'restart'])->name('biometric-devices.restart');
    Route::get('biometric-devices/{biometricDevice}/device-data', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'getDeviceData'])->name('biometric-devices.device-data');
    Route::get('biometric-devices/{biometricDevice}/test-connection', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'testConnection'])->name('biometric-devices.test-connection');
    Route::post('biometric-devices/{biometricDevice}/pull-logs', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'pullLogs'])->name('biometric-devices.pull-logs');
    Route::post('biometric-devices/{biometricDevice}/delete-orphaned-logs', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'deleteOrphanedLogs'])->name('biometric-devices.delete-orphaned-logs');
    Route::post('biometric-devices/{biometricDevice}/reprocess-failed-logs', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'reprocessFailedLogs'])->name('biometric-devices.reprocess-failed-logs');
    Route::post('biometric-devices/{biometricDevice}/sync-all-employees', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'syncAllEmployees'])->name('biometric-devices.sync-all-employees');
    Route::post('biometric-devices/{biometricDevice}/fix-punch-types', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'fixPunchTypes'])->name('biometric-devices.fix-punch-types');
    Route::post('biometric-devices/{biometricDevice}/recalculate-attendance', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'recalculateAttendance'])->name('biometric-devices.recalculate-attendance');

    // Employee import routes (must be before resource route)
    Route::get('employees/import', [App\Http\Controllers\Hr\EmployeeController::class, 'showImport'])->name('employees.import');
    Route::post('employees/import', [App\Http\Controllers\Hr\EmployeeController::class, 'import'])->name('employees.import.post');
    Route::get('employees/template/download', [App\Http\Controllers\Hr\EmployeeController::class, 'downloadTemplate'])->name('employees.template');
    // Employee validation routes
    Route::post('employees/check-email', [App\Http\Controllers\Hr\EmployeeController::class, 'checkEmailUnique'])->name('employees.check-email');
    Route::post('employees/check-phone', [App\Http\Controllers\Hr\EmployeeController::class, 'checkPhoneUnique'])->name('employees.check-phone');
    Route::resource('employees', App\Http\Controllers\Hr\EmployeeController::class);

    Route::resource('payrolls', App\Http\Controllers\Hr\PayrollController::class)->parameters([
        'payrolls' => 'payroll:hash_id'
    ]);
    Route::post('payrolls/{payroll:hash_id}/process', [App\Http\Controllers\Hr\PayrollController::class, 'process'])->name('payrolls.process');
    Route::post('payrolls/{payroll:hash_id}/approve', [App\Http\Controllers\Hr\PayrollController::class, 'approve'])->name('payrolls.approve');
    Route::get('payrolls/{payroll:hash_id}/audit-logs', [App\Http\Controllers\Hr\PayrollController::class, 'auditLogs'])->name('payrolls.audit-logs');
    Route::get('payrolls/{payroll:hash_id}/reverse', [App\Http\Controllers\Hr\PayrollController::class, 'showReverseForm'])->name('payrolls.reverse');
    Route::post('payrolls/{payroll:hash_id}/reject', [App\Http\Controllers\Hr\PayrollController::class, 'reject'])->name('payrolls.reject');
    Route::post('payrolls/{payroll:hash_id}/request-payment-approval', [App\Http\Controllers\Hr\PayrollController::class, 'requestPaymentApproval'])->name('payrolls.request-payment-approval');
    Route::post('payrolls/{payroll:hash_id}/approve-payment', [App\Http\Controllers\Hr\PayrollController::class, 'approvePayment'])->name('payrolls.approve-payment');
    Route::post('payrolls/{payroll:hash_id}/reject-payment', [App\Http\Controllers\Hr\PayrollController::class, 'rejectPayment'])->name('payrolls.reject-payment');
    Route::post('payrolls/{payroll:hash_id}/lock', [App\Http\Controllers\Hr\PayrollController::class, 'lock'])->name('payrolls.lock');
    Route::post('payrolls/{payroll:hash_id}/unlock', [App\Http\Controllers\Hr\PayrollController::class, 'unlock'])->name('payrolls.unlock');
    Route::post('payrolls/{payroll:hash_id}/reverse', [App\Http\Controllers\Hr\PayrollController::class, 'reverse'])->name('payrolls.reverse');

    Route::get('payrolls/{payroll:hash_id}/payment', [App\Http\Controllers\Hr\PayrollController::class, 'showPaymentForm'])->name('payrolls.payment');
    Route::post('payrolls/{payroll:hash_id}/process-payment', [App\Http\Controllers\Hr\PayrollController::class, 'processPayment'])->name('payrolls.process-payment');
    Route::get('payrolls/{payroll:hash_id}/employees', [App\Http\Controllers\Hr\PayrollController::class, 'getEmployees'])->name('payrolls.employees');
    Route::get('payrolls/{payroll:hash_id}/slip/{employee}', [App\Http\Controllers\Hr\PayrollController::class, 'slip'])->name('payrolls.slip');
    Route::get('payrolls/{payroll:hash_id}/slip/{employee}/print', [App\Http\Controllers\Hr\PayrollController::class, 'slipPrint'])->name('payrolls.slip.print');
    Route::get('payrolls/{payroll:hash_id}/slip/{employee}/pdf', [App\Http\Controllers\Hr\PayrollController::class, 'slipPdf'])->name('payrolls.slip.pdf');
    Route::get('payrolls/{payroll:hash_id}/export-all-slips', [App\Http\Controllers\Hr\PayrollController::class, 'exportAllSlips'])->name('payrolls.export-all-slips');
    Route::resource('trade-unions', App\Http\Controllers\Hr\TradeUnionController::class);
    Route::get('trade-unions/data', [App\Http\Controllers\Hr\TradeUnionController::class, 'data'])->name('trade-unions.data');
    Route::get('trade-unions/ajax/list', [App\Http\Controllers\Hr\TradeUnionController::class, 'getActiveTradeUnions'])->name('trade-unions.ajax.list');
    Route::resource('file-types', App\Http\Controllers\Hr\FileTypeController::class);
    Route::resource('allowance-types', App\Http\Controllers\Hr\AllowanceTypeController::class);
    Route::resource('allowances', App\Http\Controllers\Hr\AllowanceController::class);
    Route::resource('external-loans', App\Http\Controllers\Hr\ExternalLoanController::class)->parameters([
        'external-loans' => 'encodedId'
    ]);
    Route::resource('external-loan-institutions', App\Http\Controllers\Hr\ExternalLoanInstitutionController::class)->parameters([
        'external-loan-institutions' => 'encodedId'
    ]);
    Route::get('salary-advances/bulk-repayment', [App\Http\Controllers\Hr\SalaryAdvanceController::class, 'bulkRepayment'])->name('salary-advances.bulk-repayment');
    Route::get('salary-advances/bulk-repayment/download-template', [App\Http\Controllers\Hr\SalaryAdvanceController::class, 'downloadBulkRepaymentTemplate'])->name('salary-advances.bulk-repayment.download-template');
    Route::post('salary-advances/bulk-repayment', [App\Http\Controllers\Hr\SalaryAdvanceController::class, 'processBulkRepayment'])->name('salary-advances.bulk-repayment.process');
    Route::resource('salary-advances', App\Http\Controllers\Hr\SalaryAdvanceController::class)->parameters([
        'salary-advances' => 'salaryAdvance'
    ]);
    Route::get('salary-advances/{salaryAdvance}/statement-pdf', [App\Http\Controllers\Hr\SalaryAdvanceController::class, 'statementPdf'])->name('salary-advances.statement-pdf');
    Route::post('salary-advances/{salaryAdvance}/record-manual-repayment', [App\Http\Controllers\Hr\SalaryAdvanceController::class, 'recordManualRepayment'])->name('salary-advances.record-manual-repayment');
    Route::resource('heslb-loans', App\Http\Controllers\Hr\HeslbLoanController::class);

    // Payroll Chart Account Settings
    Route::get('payroll-settings/chart-accounts', [App\Http\Controllers\Hr\PayrollChartAccountSettingsController::class, 'index'])->name('payroll.chart-accounts.index');
    Route::put('payroll-settings/chart-accounts', [App\Http\Controllers\Hr\PayrollChartAccountSettingsController::class, 'update'])->name('payroll.chart-accounts.update');

    // Employee documents
    Route::post('employees/{employee}/documents', [App\Http\Controllers\Hr\EmployeeController::class, 'storeDocument'])->name('employees.documents.store');
    Route::get('documents/{document}/download', [App\Http\Controllers\Hr\EmployeeController::class, 'downloadDocument'])->name('documents.download');
    Route::delete('documents/{document}', [App\Http\Controllers\Hr\EmployeeController::class, 'deleteDocument'])->name('documents.delete');

    Route::prefix('leave')->name('leave.')->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\Hr\LeaveManagementController::class, 'index'])->name('index');

        // Leave Types
        Route::get('types', [App\Http\Controllers\Hr\LeaveTypeController::class, 'index'])->name('types.index');
        Route::get('types/create', [App\Http\Controllers\Hr\LeaveTypeController::class, 'create'])->name('types.create');
        Route::post('types', [App\Http\Controllers\Hr\LeaveTypeController::class, 'store'])->name('types.store');
        Route::get('types/{type}', [App\Http\Controllers\Hr\LeaveTypeController::class, 'show'])->name('types.show');
        Route::get('types/{type}/edit', [App\Http\Controllers\Hr\LeaveTypeController::class, 'edit'])->name('types.edit');
        Route::put('types/{type}', [App\Http\Controllers\Hr\LeaveTypeController::class, 'update'])->name('types.update');
        Route::delete('types/{type}', [App\Http\Controllers\Hr\LeaveTypeController::class, 'destroy'])->name('types.destroy');

        // Leave Requests
        Route::get('requests', [App\Http\Controllers\Hr\LeaveRequestController::class, 'index'])->name('requests.index');
        Route::get('requests/create', [App\Http\Controllers\Hr\LeaveRequestController::class, 'create'])->name('requests.create');
        Route::post('requests', [App\Http\Controllers\Hr\LeaveRequestController::class, 'store'])->name('requests.store');
        Route::get('requests/{request}', [App\Http\Controllers\Hr\LeaveRequestController::class, 'show'])->name('requests.show');
        Route::get('requests/{request}/edit', [App\Http\Controllers\Hr\LeaveRequestController::class, 'edit'])->name('requests.edit');
        Route::put('requests/{request}', [App\Http\Controllers\Hr\LeaveRequestController::class, 'update'])->name('requests.update');
        Route::delete('requests/{request}', [App\Http\Controllers\Hr\LeaveRequestController::class, 'destroy'])->name('requests.destroy');

        // Leave Request Actions
        Route::post('requests/{request}/submit', [App\Http\Controllers\Hr\LeaveRequestController::class, 'submit'])->name('requests.submit');
        Route::post('requests/{request}/approve', [App\Http\Controllers\Hr\LeaveRequestController::class, 'approve'])->name('requests.approve');
        Route::post('requests/{request}/reject', [App\Http\Controllers\Hr\LeaveRequestController::class, 'reject'])->name('requests.reject');
        Route::post('requests/{request}/return', [App\Http\Controllers\Hr\LeaveRequestController::class, 'returnForEdit'])->name('requests.return');
        Route::post('requests/{request}/cancel', [App\Http\Controllers\Hr\LeaveRequestController::class, 'cancel'])->name('requests.cancel');
        Route::post('requests/{request}/attachments', [App\Http\Controllers\Hr\LeaveRequestController::class, 'addAttachment'])->name('requests.attachments.store');
        Route::delete('requests/{request}/attachments/{attachment}', [App\Http\Controllers\Hr\LeaveRequestController::class, 'deleteAttachment'])->name('requests.attachments.destroy');

        // Leave Balances
        Route::get('balances', [App\Http\Controllers\Hr\LeaveBalanceController::class, 'index'])->name('balances.index');
        Route::get('balances/{employee}', [App\Http\Controllers\Hr\LeaveBalanceController::class, 'show'])->name('balances.show');
        Route::get('balances/{employee}/edit', [App\Http\Controllers\Hr\LeaveBalanceController::class, 'edit'])->name('balances.edit');
        Route::put('balances/{employee}', [App\Http\Controllers\Hr\LeaveBalanceController::class, 'update'])->name('balances.update');
    });

    // Phase 5: Performance & Training routes
    // Performance Management
    Route::resource('kpis', App\Http\Controllers\Hr\KpiController::class);
    Route::resource('appraisal-cycles', App\Http\Controllers\Hr\AppraisalCycleController::class);
    Route::resource('appraisals', App\Http\Controllers\Hr\AppraisalController::class);

    // Training Management
    Route::resource('training-programs', App\Http\Controllers\Hr\TrainingProgramController::class);
    Route::resource('training-attendance', App\Http\Controllers\Hr\TrainingAttendanceController::class);
    Route::resource('employee-skills', App\Http\Controllers\Hr\EmployeeSkillController::class);
    Route::resource('training-bonds', App\Http\Controllers\Hr\TrainingBondController::class);

    // Phase 6: Employment Lifecycle Management routes
    // Recruitment
    Route::resource('vacancy-requisitions', App\Http\Controllers\Hr\VacancyRequisitionController::class);
    Route::post('vacancy-requisitions/{vacancyRequisition}/submit', [App\Http\Controllers\Hr\VacancyRequisitionController::class, 'submit'])->name('vacancy-requisitions.submit');
    Route::post('vacancy-requisitions/{vacancyRequisition}/approve', [App\Http\Controllers\Hr\VacancyRequisitionController::class, 'approve'])->name('vacancy-requisitions.approve');
    Route::post('vacancy-requisitions/{vacancyRequisition}/reject', [App\Http\Controllers\Hr\VacancyRequisitionController::class, 'reject'])->name('vacancy-requisitions.reject');
    Route::post('vacancy-requisitions/{vacancyRequisition}/publish', [App\Http\Controllers\Hr\VacancyRequisitionController::class, 'publish'])->name('vacancy-requisitions.publish');
    Route::post('vacancy-requisitions/{vacancyRequisition}/unpublish', [App\Http\Controllers\Hr\VacancyRequisitionController::class, 'unpublish'])->name('vacancy-requisitions.unpublish');
    Route::resource('applicants', App\Http\Controllers\Hr\ApplicantController::class);
    Route::post('applicants/{applicant}/convert-to-employee', [App\Http\Controllers\Hr\ApplicantController::class, 'convertToEmployee'])->name('applicants.convert-to-employee');
    Route::post('applicants/{applicant}/override-normalization', [App\Http\Controllers\Hr\ApplicantController::class, 'overrideNormalization'])->name('applicants.override-normalization');
    Route::post('applicants/{applicant}/shortlist', [App\Http\Controllers\Hr\ApplicantController::class, 'shortlist'])->name('applicants.shortlist');
    Route::post('interview-records/bulk-store', [App\Http\Controllers\Hr\InterviewRecordController::class, 'bulkStore'])->name('interview-records.bulk-store');
    Route::resource('interview-records', App\Http\Controllers\Hr\InterviewRecordController::class);
    Route::resource('offer-letters', App\Http\Controllers\Hr\OfferLetterController::class);

    // Onboarding
    Route::resource('onboarding-checklists', App\Http\Controllers\Hr\OnboardingChecklistController::class);
    Route::resource('onboarding-records', App\Http\Controllers\Hr\OnboardingRecordController::class);

    // Confirmation
    Route::resource('confirmation-requests', App\Http\Controllers\Hr\ConfirmationRequestController::class);

    // Transfers & Promotions
    Route::resource('employee-transfers', App\Http\Controllers\Hr\EmployeeTransferController::class);
    Route::resource('employee-promotions', App\Http\Controllers\Hr\EmployeePromotionController::class);

    // Phase 7: Discipline, Grievance & Exit routes
    Route::resource('disciplinary-cases', App\Http\Controllers\Hr\DisciplinaryCaseController::class);
    Route::resource('grievances', App\Http\Controllers\Hr\GrievanceController::class);
    Route::resource('exits', App\Http\Controllers\Hr\ExitController::class);
});
