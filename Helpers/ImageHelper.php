<?php
/*
 * This file is part of the congraph/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Core\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * ImageHelper class
 * 
 * Helping with image operations: croping, resizing, filtering, etc.
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ImageHelper
{

	public $imageVersions;
	public $imagePackages;

	public function __construct(){
		$this->imageVersions = Config::get('congraph::congraph.image_versions');
		$this->imagePackages = Config::get('congraph::congraph.image_packages');
	}

	public function getImageVersionsPaths($imagepath){
		$img = Image::make($imagepath);

		if(!$img){
			return false;
		}

		$directory = $img->dirname;
		$filename = $img->filename;

		$versions = array();

		foreach ($this->imageVersions as $versionName => $imageVersion) {
			$version_filename = $filename;
			if(!empty($imageVersion['prefix'])){
				$version_filename = $imageVersion['prefix'] . $filename;
			}
			if(!empty($imageVersion['sufix'])){
				$version_filename = $filename . $imageVersion['sufix'];
			}

			$basename = $version_filename . '.' . $img->extension;
			$versionPath = $directory . DIRECTORY_SEPARATOR . $basename;

			if(FileHandler::exists($versionPath)){
				$versions[$versionName] = $versionPath;
			}
		}

		return $versions;
	}

	public function getImageVersionsURLs($imageurl){
		$filepath = FileHandler::uploadsPath($imageurl);
		
		// gives Image source not readable (sometimes)
		// $img = Image::make($filepath);

		// if(!$img){
		// 	return false;
		// }
		
		// $directory = $img->dirname;
		// $filename = $img->filename;
		// $extension = $img->extension;

		// ---------------------------------------------------------------------
		// OVERRIDE
		// ---------------------------------------------------------------------
		/**
		 *  Image::make($filepath) is too slow 
		 *  Override
		 * 
		 */
		
		if ( ! FileHandler::exists( $filepath ) ){
			return false;
		}

		$path_parts = pathinfo( $filepath );

		// echo $path_parts['dirname'], "\n";
		// echo $path_parts['basename'], "\n";
		// echo $path_parts['extension'], "\n";
		// echo $path_parts['filename'], "\n"; // since PHP 5.2.0
		// dd( $path_parts );

		$directory = $path_parts['dirname'];
		$filename = $path_parts['filename'];
		$extension = $path_parts['extension'];

		// ---------------------------------------------------------------------
		// END OVERRIDE
		// ---------------------------------------------------------------------

		$versions = array();

		foreach ($this->imageVersions as $versionName => $imageVersion) {
			$version_filename = $filename;
			if(!empty($imageVersion['prefix'])){
				$version_filename = $imageVersion['prefix'] . $filename;
			}
			if(!empty($imageVersion['sufix'])){
				$version_filename = $filename . $imageVersion['sufix'];
			}

			$version_filename = $version_filename . '.' . $extension;
			$versionPath = $directory . DIRECTORY_SEPARATOR . $version_filename;

			if(FileHandler::exists($versionPath)){
				$versions[$versionName] = FileHandler::uploadsUrl($version_filename);
			}
		}

		// dd($versions);
		return $versions;
	}

	public function getImageVersion($version_name){
		if(empty($version_name) || !is_string($version_name)){
			return false;
		}
		if(empty($this->imageVersions)){
			$this->imageVersions = Config::get('congraph::congraph.image_versions');
		}
		

		if(!empty($this->imageVersions) && is_array($this->imageVersions) && array_key_exists($version_name, $this->imageVersions)){
			return $this->imageVersions[$version_name];
		}else{
			return false;
		}
	}

	public function getImagePackageVersions($package_name){

		$versions = array();
		
		if(!empty($this->imagePackages) && !empty($this->imagePackages['default'])){
			$versions = $this->imagePackages['default'];
		}

		if(!empty($package_name) && is_string($package_name)){
			if(!empty($this->imagePackages) && array_key_exists($package_name, $this->imagePackages)){
				$versions = array_merge($versions, $this->imagePackages[$package_name]);
			}
		}
		$versionObjects = array();
		if(!empty($versions)){
			foreach ($versions as $version) {
				$versionObject = $this->getImageVersion($version);
				$versionObjects[] = $versionObject;
			}
		}

		return $versionObjects;
	}

	public function makeImagePackage($original_path, $package_name){
		if(empty($original_path)){
			return false;
		}

		$versions = $this->getImagePackageVersions($package_name);

		return $this->makeImageVersions($original_path, $versions);
	}

	public function makeImageVersions($original_path, $versions){
		if(empty($versions) || empty($original_path) || !is_array($versions)){
			return false;
		}

		foreach ($versions as $version) {
			$this->makeImageVersion($original_path, $version);
		}

		return true;
	}

	public function makeImageVersion($original_path, $params){
		
		if(empty($params) || empty($original_path) || !is_array($params) || empty($params['method'])){
			return false;
		}

		
		$img = Image::make($original_path);

		if(!$img){
			return false;
		}

		if(!empty($params['directory'])){
			$directory = public_path() . DIRECTORY_SEPARATOR . $params['directory'];
		}else{
			$directory = $img->dirname;
		}
		$directory = rtrim($directory, DIRECTORY_SEPARATOR);
		$filename = $img->filename;
		if(!empty($params['prefix'])){
			$filename = $params['prefix'] . $filename;
		}
		if(!empty($params['sufix'])){
			$filename = $filename . $params['sufix'];
		}

		$basename = $filename . '.' . $img->extension;
		$full_path = $directory . DIRECTORY_SEPARATOR . $basename;

		if(file_exists($full_path)){
			return true;
		}

		$method = $params['method'];

		if(!empty($params['width'])){
			$width = intval($params['width']);
		}else{
			$width = null;
		}

		if(!empty($params['height'])){
			$height = intval($params['height']);
		}else{
			$height = null;
		}

		switch ($method) {
			case 'crop':
				if(!empty($params['x'])){
					$x = $params['x'];
				}else{
					$x = null;
				}

				if(!empty($params['y'])){
					$y = $params['y'];
				}else{
					$y = null;
				}
				$img->crop($width, $height, $x, $y);
				break;
			case 'fit':
				$img->fit($width, $height);
				break;
			case 'resize':
			default:
				$constraints = false;
				if(array_key_exists('constraints', $params)){
					$constraints = $params['constraints'];
					if(empty($constraints)){
						$constraints = false;
					}
				}
				if($constraints){
					$callback = function($constraint) use($constraints){
						foreach ($constraints as $c) {
							$constraint->$c();
						}
					};
				}else{
					$callback = null;
				}

				$img->resize($width, $height, $callback);
				break;
		}


		// -------------------------------------------------------------
		// Apply filter(s)
		// -------------------------------------------------------------
		if ( isset($params['filter']) && !empty($params['filter'])){
			$filter_classname = $params['filter'];
			if (class_exists($filter_classname)){
				// apply filter
				$img->filter(new $filter_classname);
			}
		}

		$img->save($full_path);
		return true;
	}
}