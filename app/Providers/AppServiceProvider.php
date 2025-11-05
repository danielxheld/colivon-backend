<?php

namespace App\Providers;

use App\Models\Household;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Policies\HouseholdPolicy;
use App\Policies\ShoppingListPolicy;
use App\Policies\ShoppingListItemPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        Household::class => HouseholdPolicy::class,
        ShoppingList::class => ShoppingListPolicy::class,
        ShoppingListItem::class => ShoppingListItemPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
