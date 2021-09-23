<?php
declare(strict_types=1);

namespace Storage\Flysystem;

use League\Flysystem\Util;
use League\Flysystem\Config;
use Scaleflex\Filerobot\FilerobotAdapter;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\CanOverwriteFiles;
use League\Flysystem\Adapter\Polyfill\StreamedReadingTrait;

class FilerobotDriverAdapter extends AbstractAdapter implements CanOverwriteFiles
{
	use StreamedReadingTrait;

	private $key;
	private $scaleflex;

	public function __construct($config)
	{
		$this->key       = $config['key'];
		$this->scaleflex = new FilerobotAdapter($config['key']);
	}

	public function writeStream($path, $resource, Config $config)
	{
		return $this->write($path, $resource, $config);
	}

	public function write($path, $contents, Config $config)
	{
		return $this->upload($path, $contents, $config);
	}

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

	public function update($path, $contents, Config $config)
	{
		echo 'update';
		// TODO: Implement update() method.
	}

	public function updateStream($path, $resource, Config $config)
	{
		echo 'updateStream';
		// TODO: Implement updateStream() method.
	}

	public function rename($path, $newpath)
	{
		$checkPathIs = $this->checkPathIs($path);
		if (!empty($checkPathIs['file'])) {
			return $this->scaleflex->rename_file($path, $newpath);
		} elseif (!empty($checkPathIs['folder'])) {
			return $this->scaleflex->rename_folder($path, $newpath);
		}
		return [
			'error' => 'Uuid notworking please try again'
		];
	}

	public function checkPathIs($path)
	{
		$result = $this->scaleflex->detail_file($path);
		if (!empty($result['msg'])) {
			$result = $this->scaleflex->detail_folder($path);
		}
		return $result;
	}

	public function copy($path, $newpath)
	{
		$checkPathIs = $this->checkPathIs($path);
		if (!empty($checkPathIs['file'])) {
			return $this->scaleflex->move_file($path, $newpath);
		} elseif (!empty($checkPathIs['folder'])) {
			return $this->scaleflex->move_folder($path, $newpath);
		}

		return [
			'error' => 'Uuid notworking please try again'
		];
		// TODO: Implement copy() method.
	}

	public function delete($path)
	{
		return $this->scaleflex->delete_file($path);
		// TODO: Implement delete() method.
	}

	public function deleteDir($dirname)
	{
		return $this->scaleflex->delete_folder($dirname);
		// TODO: Implement deleteDir() method.
	}

	public function createDir($dirname, Config $config)
	{
		return $this->scaleflex->create_folder($dirname);
		// TODO: Implement createDir() method.
	}

	public function setVisibility($path, $visibility)
	{
		echo 'setVisibility';
		// TODO: Implement setVisibility() method.
	}

	public function has($path)
	{
		$path = Util::normalizePath($path);

		return $path;
	}

	public function read($path)
	{
		$path   = $this->applyPathPrefix($path);
		$result = $this->checkPathIs($path);


		$result['contents'] = !empty($result['file']) ? $result['file'] : $result['folder'];
		return $result;
	}

	public function readStream($path)
	{
		echo 'readStream';
		// TODO: Implement readStream() method.
	}

	public function listContents($directory = '', $recursive = false)
	{
		$listing = $this->scaleflex->list_folder($directory);

		return $this->normaliseObject($listing, $directory);
	}

	/**
	 * Get normalised files array from Google_Service_Drive_DriveFile
	 *
	 *            Parent directory itemId path
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

	public function getMetadata($path)
	{
		echo 'getMetadata';
		// TODO: Implement getMetadata() method.
	}

	public function getSize($path)
	{
		echo 'getSize';
		// TODO: Implement getSize() method.
	}

	public function getMimetype($path)
	{
		echo 'getMimetype';
		// TODO: Implement getMimetype() method.
	}

	public function getTimestamp($path)
	{
		echo 'getTimestamp';
		// TODO: Implement getTimestamp() method.
	}

	public function getVisibility($path)
	{
		echo 'getVisibility';
		// TODO: Implement getVisibility() method.
	}
}