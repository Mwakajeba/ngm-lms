<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
     public function boot()
    {
        View::composer('incs.sideMenu', function ($view) {
            $user = Auth::user();

            if (!$user) {
                $view->with('menus', []);
                return;
            }

            $role = $user->roles->first();

            $menus = Menu::with('children')
                ->whereNull('parent_id')
                ->whereHas('roles', fn($q) => $q->where('roles.id', $role->id)) // 👈 FIXED HERE
                ->get();

            $view->with('menus', $menus);
        });
    }
}
