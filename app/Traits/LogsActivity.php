<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->storeActivityLog('create');
        });

        static::updated(function ($model) {
            $model->storeActivityLog('update');
        });

        static::deleted(function ($model) {
            $model->storeActivityLog('delete');
        });
    }

    protected function storeActivityLog($action)
    {
        $agent = new Agent();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'model'       => class_basename($this),
            'action'      => $action,
            'description' => "{$action}d " . class_basename($this) . " (ID: {$this->id})",
            'ip_address'  => request()->ip(),
            'device'      => $agent->device() . ' - ' . $agent->browser(),
            'activity_time' => now(),
        ]);
    }
}
