<?php

namespace Thanhdai\Storage;

use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class FilerobotServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Storage::extend('filerobot', function ($app, $config) {
			$options = [];
			if (isset($config['key'])) {
				$options['key'] = $config['key'];
			}
			$adapter = new FilerobotDriverAdapter($options);
			$config['disable_asserts'] = true;
			return new Filesystem($adapter, $config);
		});
	}
}
