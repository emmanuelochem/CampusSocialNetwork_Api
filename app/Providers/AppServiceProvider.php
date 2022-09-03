<?php

namespace App\Providers;

use App\Mixins\PaginationMixin;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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
        //
        Builder::mixin(new PaginationMixin);
        Relation::mixin(new PaginationMixin);



        Request::macro('isPresent', function (string $key) {
            return $this->has($key) && $this->filled($key);
        });

        Response::macro('success', function ($statusMsg = 'Operation successful', $data = []) {
            return (
            Response::json(['status' => 'success', 'message' => $statusMsg, 'data' => $data], 200)
            );
        });

        Response::macro('failed', function ($statusMsg = 'Operation failed', $data = []) {
            return (
            Response::json(['status' => 'dailed', 'message' => $statusMsg, 'data' => $data], 200)
            );
        });

        Response::macro('error', function (string $message, int $statusCode) {
            return (
            Response::json(compact('message'), $statusCode)
            );
        });
    }


}
