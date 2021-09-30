<?php
declare(strict_types=1);

namespace Thanhdai\Storage;

use League\Flysystem\Util;
use League\Flysystem\Config;
use Scaleflex\Filerobot\FilerobotAdapter;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Polyfill\StreamedReadingTrait;

/**
 * Class FilerobotDriverAdapter
 *
 * @package Thanhdai\Storage
 */
class FilerobotDriverAdapter extends AbstractAdapter
{
	use StreamedReadingTrait;

	/**
	 * @var mixed
	 */
	private $key;
	/**
	 * @var FilerobotAdapter
	 */
	private $scaleflex;

	/**
	 * FilerobotDriverAdapter constructor.
	 *
	 * @param $config
	 */
	public function __construct($config)
	{
		$this->key       = $config['key'];
		$this->scaleflex = new FilerobotAdapter($config['key']);
	}

	/**
	 * @param          $path
	 * @param          $resource
	 * @param  Config  $config
	 *
	 * @return void
	 */
	public function writeStream($path, $resource, Config $config)
	{
		return $this->upload($path, $resource, $config);
	}

	/**
	 * @param          $path
	 * @param          $content
	 * @param  Config  $config
	 */
	public function upload($path, $content, Config $config)
	{
		if (is_resource($content)) {
			$this->scaleflex->stream_upload_file($path, $content, $config->get('name'));
		} else {
			switch ($config->get('type')) {
				case 'base64':
					$this->scaleflex->upload_file_binary($config->get('name').'/'.$path, $content);
					break;
				case 'multipart':
					$this->scaleflex->upload_file_multipart($config->get('name'), $content, $path);
					break;
				case 'remote':
					$this->scaleflex->upload_file_remote($config->get('name'), $content);
					break;
				default:
					break;
			}
		}
	}

	/**
	 * @param          $path
	 * @param          $contents
	 * @param  Config  $config
	 */
	public function write($path, $contents, Config $config)
	{
		return $this->upload($path, $contents, $config);
	}

	/**
	 * @param          $path
	 * @param          $contents
	 * @param  Config  $config
	 */
	public function update($path, $contents, Config $config)
	{
		return $this->upload($path, $contents, $config);
	}

	/**
	 * @param          $path
	 * @param          $resource
	 * @param  Config  $config
	 */
	public function updateStream($path, $resource, Config $config)
	{
		return $this->upload($path, $resource, $config);
	}

	/**
	 * @param $path
	 * @param $newpath
	 *
	 * @return string[]
	 */
	public function rename($path, $newpath): array
	{
		$checkPathIs = $this->checkPathIs($path);
		if (!empty($checkPathIs['file'])) {
			return $this->scaleflex->rename_file($path, $newpath);
		} elseif (!empty($checkPathIs['folder'])) {
			return $this->scaleflex->rename_folder($path, $newpath);
		}
		return [
			'error' => 'Uuid not working please try again'
		];
	}

	/**
	 * @param $path
	 *
	 * @return mixed
	 */
	public function checkPathIs($path)
	{
		$result = $this->scaleflex->detail_file($path);
		if (!empty($result['msg'])) {
			$result = $this->scaleflex->detail_folder($path);
		}
		return $result;
	}

	/**
	 * @param $path
	 * @param $newpath
	 *
	 * @return string[]
	 */
	public function copy($path, $newpath): array
	{
		$checkPathIs = $this->checkPathIs($path);
		if (!empty($checkPathIs['file'])) {
			return $this->scaleflex->move_file($path, $newpath);
		} elseif (!empty($checkPathIs['folder'])) {
			return $this->scaleflex->move_folder($path, $newpath);
		}
		return [
			'error' => 'Uuid not working please try again'
		];
	}

	/**
	 * @param $path
	 *
	 * @return mixed
	 */
	public function delete($path)
	{
		return $this->scaleflex->delete_file($path);
	}

	/**
	 * @param $dirname
	 *
	 * @return mixed
	 */
	public function deleteDir($dirname)
	{
		return $this->scaleflex->delete_folder($dirname);
	}

	/**
	 * @param          $dirname
	 * @param  Config  $config
	 *
	 * @return mixed
	 */
	public function createDir($dirname, Config $config)
	{
		return $this->scaleflex->create_folder($dirname);
	}

	/**
	 * @param $path
	 * @param $visibility
	 */
	public function setVisibility($path, $visibility)
	{
		echo 'setVisibility';
		// TODO: Implement setVisibility() method.
	}

	/**
	 * @param $path
	 *
	 * @return string
	 */
	public function has($path): string
	{
		return Util::normalizePath($path);
	}

	/**
	 * @param $path
	 *
	 * @return mixed
	 */
	public function read($path)
	{
		$path   = $this->applyPathPrefix($path);
		$result = $this->checkPathIs($path);

		$contents = [];
		if (!empty($result['file'])) {
			$contents['contents'] = $result['file'];
		} elseif (!empty($result['folder'])) {
			$contents['contents'] = $result['folder'];
		} else {
			$contents['contents'] = 'Uuid not working please try again';
		}
		return $contents;
	}

	/**
	 * @param $path
	 *
	 * @return array
	 */
	public function readStream($path): array
	{
		$contents['stream'] = $this->checkPathIs($path);

//		dd($this->readStream($path));
		return $contents;
	}

	/**
	 * @param  string  $directory
	 * @param  false   $recursive
	 *
	 * @return array
	 */
	public function listContents($directory = '', $recursive = false)
	{
		$arrayDirector = explode(':', $directory);
		$parsDirectory = str_replace($arrayDirector[0].':', '/', $directory);
		$result        = '';

		if ($arrayDirector[0] == 'file') {
			$listing = $this->scaleflex->list_file($parsDirectory);
			$result  = $this->normaliseObject($listing, $directory);
		} elseif ($arrayDirector[0] == 'folder') {
			$listing = $this->scaleflex->list_folder($parsDirectory);
			$result  = $this->normaliseObjectFolder($listing, $directory);
		}
		return $result;
	}

	/**
	 * Get normalised folder array
	 *
	 * @return array Normalised files array
	 */
	protected function normaliseObject($array, $path): array
	{
		$result = [];
		foreach ($array['files'] as $key => $row) {
			$result[$key]['uuid']        = $row['uuid'];
			$result[$key]['name']        = $row['name'];
			$result[$key]['path']        = $row['name'];
			$result[$key]['extension']   = $row['extension'] ?? '';
			$result[$key]['size']        = $row['size'];
			$result[$key]['flags']       = $row['flags'];
			$result[$key]['type']        = $row['type'];
			$result[$key]['meta']        = $row['meta'];
			$result[$key]['dirname']     = $path;
			$result[$key]['tags']        = $row['tags'];
			$result[$key]['url']         = $row['url'];
			$result[$key]['modified_at'] = $row['modified_at'];
		}
		return $result;
	}

	/**
	 * Get normalised files array
	 *
	 *
	 * @return array Normalised files array
	 */
	protected function normaliseObjectFolder($array, $path): array
	{
		$result = [];
		foreach ($array['folders'] as $key => $row) {
			$result[$key]['uuid']    = $row['uuid'];
			$result[$key]['name']    = $row['name'];
			$result[$key]['path']    = $row['path'];
			$result[$key]['dirname'] = $path;
		}
		return $result;
	}

	/**
	 * @param $path
	 *
	 * @return mixed|string
	 */
	public function getMetadata($path)
	{
		$result = $this->checkPathIs($path);

		if (!empty($result['file'])) {
			$contents = $result['file']['meta'];
		} elseif (!empty($result['folder'])) {
			$contents = $result['folder']['meta'];
		} else {
			$contents = 'Uuid not working please try again';
		}
		return $contents;
	}

	/**
	 * @param $path
	 *
	 * @return mixed|string
	 */
	public function getSize($path)
	{
		$result = $this->checkPathIs($path);

		if (!empty($result['file'])) {
			$contents = $result['file']['size'];
		} elseif (!empty($result['folder'])) {
			$contents = $result['folder']['size'];
		} else {
			$contents = 'Uuid not working please try again';
		}
		return $contents;
		// TODO: Implement getSize() method.
	}

	/**
	 * @param $path
	 *
	 * @return mixed|string
	 */
	public function getMimetype($path)
	{
		$result = $this->checkPathIs($path);

		if (!empty($result['file'])) {
			$contents = $result['file']['type'];
		} else {
			$contents = 'Uuid not file please try again';
		}
		return $contents;
		// TODO: Implement getMimetype() method.
	}

	/**
	 * @param $path
	 */
	public function getTimestamp($path)
	{
		echo 'getTimestamp';
		// TODO: Implement getTimestamp() method.
	}

	/**
	 * @param $path
	 *
	 * @return mixed|string
	 */
	public function getVisibility($path)
	{
		$result = $this->checkPathIs($path);

		if (!empty($result['file'])) {
			$contents = $result['file']['visibility'];
		} elseif (!empty($result['folder'])) {
			$contents = $result['folder']['visibility'];
		} else {
			$contents = 'Uuid not working please try again';
		}
		return $contents;
	}
}