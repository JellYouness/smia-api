<?php

namespace App\Providers;

use App\Models\Ambassador;
use App\Models\Client;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

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
    Relation::morphMap([
      'CLIENT' => Client::class,
      'AMBASSADOR' => Ambassador::class,
    ]);
  }
}
