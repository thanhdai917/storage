<?php
declare(strict_types=1);

namespace Thanhdai\Storage;

use League\Flysystem\Util;
use League\Flysystem\Config;
use Scaleflex\Filerobot\FilerobotAdapter;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\CanOverwriteFiles;
use League\Flysystem\Adapter\Polyfill\StreamedReadingTrait;

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
	 */
	public function writeStream($path, $resource, Config $config)
	{
		return $this->write($path, $resource, $config);
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
	 * @param          $content
	 * @param  Config  $config
	 */
	public function upload($path, $content, Config $config)
	{
		if (is_resource($content)) {

		} else {
			switch ($config->get('type')) {
				case 'base64':
					$this->scaleflex->upload_file_binary($path, $content);
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
	public function update($path, $contents, Config $config)
	{
		echo 'update';
		// TODO: Implement update() method.
	}

	/**
	 * @param          $path
	 * @param          $resource
	 * @param  Config  $config
	 */
	public function updateStream($path, $resource, Config $config)
	{
		echo 'updateStream';
		// TODO: Implement updateStream() method.
	}

	/**
	 * @param $path
	 * @param $newpath
	 *
	 * @return string[]
	 */
	public function rename($path, $newpath)
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
	public function copy($path, $newpath)
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
		// TODO: Implement copy() method.
	}

	/**
	 * @param $path
	 *
	 * @return mixed
	 */
	public function delete($path)
	{
		return $this->scaleflex->delete_file($path);
		// TODO: Implement delete() method.
	}

	/**
	 * @param $dirname
	 *
	 * @return mixed
	 */
	public function deleteDir($dirname)
	{
		return $this->scaleflex->delete_folder($dirname);
		// TODO: Implement deleteDir() method.
	}

	/**
	 * @param          $dirname
	 * @param  Config  $config
	 *
	 * @return mixed
	 */
	public function createDir($dirname, Config $config)
	{

		$this->scaleflex->create_folder($dirname);
		return $this->normaliseObject($listing, $directory);
		// TODO: Implement createDir() method.
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
	 * @return mixed
	 */
	public function has($path)
	{
		$path = Util::normalizePath($path);
		return $path;
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
			$contents['contents'] = $result['file'];
		}else{
			$contents['contents'] = 'Uuid not working please try again';
		}
		return $contents;
	}

	/**
	 * @param $path
	 */
	public function readStream($path)
	{
		echo 'readStream';
		// TODO: Implement readStream() method.
	}

	/**
	 * @param  string  $directory
	 * @param  false   $recursive
	 *
	 * @return array
	 */
	public function listContents($directory = '', $recursive = false)
	{
		$arrayDirector = explode(':',$directory);
		$parsDirectory = str_replace($arrayDirector[0].':','/',$directory);

		$result = '';
		
		if($arrayDirector[0] == 'file'){
			$listing = $this->scaleflex->list_file($parsDirectory);
			$result = $this->normaliseObject($listing, $directory);
		}elseif($arrayDirector[0] == 'folder'){
			$listing = $this->scaleflex->list_folder($parsDirectory);
			$result = $this->normaliseObjectFolder($listing, $directory);
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
			$result[$key]['uuid']        = $row['uuid'];
			$result[$key]['name']        = $row['name'];
			$result[$key]['path']        = $row['path'];
			$result[$key]['dirname']     = $path;
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
	 * @param $path
	 */
	public function getMetadata($path)
	{
		echo 'getMetadata';
		// TODO: Implement getMetadata() method.
	}

	/**
	 * @param $path
	 */
	public function getSize($path)
	{
		echo 'getSize';
		// TODO: Implement getSize() method.
	}

	/**
	 * @param $path
	 */
	public function getMimetype($path)
	{
		echo 'getMimetype';
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
	 */
	public function getVisibility($path)
	{
		echo 'getVisibility';
		// TODO: Implement getVisibility() method.
	}
}