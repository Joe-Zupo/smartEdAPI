<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Support\Scramble\SanctumAuthOperationTransformer;
use Dedoc\Scramble\Configuration\OperationTransformers;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

class AppServiceProvider extends ServiceProvider
{
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
         Scramble::configure()->withOperationTransformers(function (Operation $operation) {
            $operation->addParameters([
                Parameter::make('Environment', 'header')
                    ->description("Use this header to toggle between backend and frontend environments for development. Defaults to backend if omitted.")
                    ->setSchema(Schema::fromType(new StringType()))
                    ->required(false)
                    ->example('frontend / backend'),
                Parameter::make('X-XSRF-TOKEN', 'header')
                    ->description("CSRF token value from the XSRF-TOKEN cookie. Required for POST/PUT/DELETE requests. Obtain by calling GET /sanctum/csrf-cookie first.")
                    ->setSchema(Schema::fromType(new StringType()))
                    ->required(false),
            ]);
        });
        // Add bearer auth scheme to the generated OpenAPI document
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->components->addSecurityScheme(
                'sessionAuth',
                SecurityScheme::apiKey('cookie', 'laravel_session')
                    ->setDescription('Session cookie set after POST /api/login. Call GET /sanctum/csrf-cookie first to initialize the CSRF token.')
            );

            $openApi->components->addSecurityScheme(
                'xsrfToken',
                SecurityScheme::apiKey('header', 'X-XSRF-TOKEN')
                    ->setDescription('CSRF token from the XSRF-TOKEN cookie. Required for all state-mutating requests (POST, PUT, PATCH, DELETE).')
            );
        });

        // Mark operations protected by auth:sanctum as secured in docs
        Scramble::configure()->withOperationTransformers(function (OperationTransformers $transformers) {
            $transformers->append(SanctumAuthOperationTransformer::class);
        });

        Scramble::configure()
        ->withOperationTransformers(function (Operation $operation) {
            $operation->addParameters([
                Parameter::make('Environment', 'header')
                    ->description("Use this header to toggle between backend and frontend environments for development. Defaults to backend if omitted.")
                    ->setSchema(Schema::fromType(new StringType()))
                    ->required(false)
                    ->example('frontend / backend'),
            ]);
        });
    }
}
