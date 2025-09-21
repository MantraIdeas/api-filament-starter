<?php

namespace App\Providers;

use App\Pagination\CustomPaginator;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\InfoObject;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->alias(CustomPaginator::class, LengthAwarePaginator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Model::automaticallyEagerLoadRelationships();

        Model::shouldBeStrict(! app()->isProduction());

        Schema::defaultStringLength(191);

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi): void {
            $openApi->setInfo(new InfoObject(config('app.name').' API', '1.0.0'));
            $openApi->secure(
                SecurityScheme::http('bearer', 'JWT')
                    ->setDescription('JWT Bearer Token. (`Bearer ` is prepended automatically)')
            );
        });

        JsonResource::withoutWrapping();

        Response::macro('success', function ($data, $message = null, $status = 200) {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $data,
            ], $status);
        });

        Response::macro('collection', function ($collection, $message = null, $status = 200) {
            if (request()->page && request()->size) {
                ['data' => $data, 'paginate' => $paginate] = $collection->toArray(request());
            } else {
                ['data' => $data] = $collection->toArray(request());
            }

            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $data,
                'paginate' => $paginate ?? null,
            ], $status);
        });

        Response::macro('custom', function ($data, $status, $message = null) {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $data,
            ], $status);
        });

        Response::macro('error', function (string|Throwable $e, $status = 400) {
            Log::error($e instanceof Throwable ? $e->getMessage() : $e);

            return response()->json([
                'status' => $status,
                'message' => $e instanceof Throwable ? $e->getMessage() : $e,
                'data' => null,
            ], $status);
        });
    }
}
