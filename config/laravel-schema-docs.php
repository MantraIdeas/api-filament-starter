<?php

return [
    /**
     * Path to the YAML file where the database schema will be stored.
     * Make sure this path is writable by the application.
     */
    'yaml_file' => base_path('laravel-schema-docs.yaml'),
    /**
     * Excluded tables from documentation generation.
     */
    'excluded_tables' => [
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'migrations',
        'password_reset_tokens',
        'sessions',
    ],
    /**
     * Middleware to apply to the routes.
     */
    'middleware' => ['web'],
    /**
     * Redirect URI from Schema Docs Dashboard.
     */
    'redirect_url' => '/',
    /**
     * Show SchemaDocsPage
     */
    'show_pages' => env('SHOW_SCHEMA_DOCS', true),

];
