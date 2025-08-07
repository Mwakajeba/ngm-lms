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

            // Get all user roles
            $userRoles = $user->roles;

            if ($userRoles->isEmpty()) {
                $view->with('menus', []);
                return;
            }

            // Get role IDs
            $roleIds = $userRoles->pluck('id')->toArray();

            // Get menus for all user roles
            $menus = Menu::with('children')
                ->whereNull('parent_id')
                ->whereHas('roles', function ($query) use ($roleIds) {
                    $query->whereIn('roles.id', $roleIds);
                })
                ->get();

            $view->with('menus', $menus);
        });
    }
}
