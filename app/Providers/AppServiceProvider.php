<?php

namespace App\Providers;

use App\Models\BankData;
use App\Models\MealType;
use App\Models\Order;
use App\Models\Package;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $languages = ['ar', 'en'];
        App::setLocale('ar');
    }
}
