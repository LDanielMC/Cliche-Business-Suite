<?php

namespace App\Providers;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Storage::extend('gcs', function ($app, $config) {
            $client = new StorageClient(array_filter([
                'keyFilePath' => $config['key_file'],
                'projectId'   => $config['project_id'] ?? null,
            ]));

            $bucket  = $client->bucket($config['bucket']);
            $prefix  = $config['path_prefix'] ?? '';
            $adapter = new GoogleCloudStorageAdapter($bucket, $prefix);

            return new FilesystemAdapter(
                new Filesystem($adapter),
                $adapter,
                $config
            );
        });
    }
}
