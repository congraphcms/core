<?php
/*
 * This file is part of the cookbook/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\EAV\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * FileHelper class
 * 
 * Helping with file operations: trimming, regex, slugs, etc.
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class FileHelper
{

	/**
	 * Get uploads url from config and optionally concatonates
	 * given url to uploads url
	 *
	 * @param string $url - optional url
	 * @return string
	 */
	public static function uploadsUrl($url = ''){
		$url = Config::get('cookbook::cookbook.uploads_url') . '/' . $url;
		$rtrim = !empty($url);
		$url = url(self::normalizeUrl($url, $rtrim));

		return $url;
	}

	// /**
	//  * Gets uploads path from config and optionally concatonates
	//  * given path to uploads path
	//  *
	//  * There is one parameter,
	//  * string that is path wished to be concatonated to uploads path
	//  *
	//  * @param string $path - optional path
	//  * @return string uploads path.
	//  */
	// public function uploadsPath($path = ''){
	// 	$path = $this->upload_url . '/' . $path;
	// 	$rtrim = !empty($path);
	// 	$path = public_path() . '/' . $path;
	// 	$path = $this->normalizePath($path, $rtrim);

	// 	return $path;
	// }

	/**
	 * Normalizes path slashes when mixed between path and url conversion
	 *
	 * It takes string with mixed or doubled slashes and converts them
	 * to single slashes dependent on system beacose of use of DIRECTORY_SEPARATOR const, 
	 * and optionally trims right slash.
	 *
	 * There are two parameters,
	 * string that will be normalized and optional rtrim flag
	 *
	 * @param string $path - string to be normalized
	 * @param boolean $rtrim - flag for right trim option
	 * @return string normalized path.
	 */
	public static function normalizePath($path, $rtrim = true){
		$path = trim($path);
		// replace every back slash with forward slash
		$path = str_replace('\\', '/', $path);
		// replace all double slashes with single
		$path  = preg_replace( '/\/+/', '==DS==', $path );
		// trim slashes on begining
		$segments = explode('==DS==', $path);
		$path = implode(DIRECTORY_SEPARATOR, $segments);
		//$path = ltrim($path, DIRECTORY_SEPARATOR);

		if($rtrim){
			$path = rtrim($path, DIRECTORY_SEPARATOR);
		}

		return $path;
	}

	/**
	 * Normalizes url slashes when mixed between path and url conversion
	 *
	 * It takes string with mixed or doubled slashes and converts them
	 * to single forward slashes, and optionally trims right slash
	 *
	 * There are two parameters,
	 * string that will be normalized and optional rtrim flag
	 *
	 * @param string $url - string to be normalized
	 * @param boolean $rtrim - flag for right trim option
	 * @return string normalized url.
	 */
	public static function normalizeUrl($url, $rtrim = true){
		$url = trim($url);
		// replace every back slash with forward slash
		$url = str_replace('\\', '/', $url);
		// replace all double slashes with single
		$url  = preg_replace( '/\/+/', '==DS==', $url );
		// trim slashes on begining
		$segments = explode('==DS==', $url);
		$url = implode('/', $segments);
		//$url = ltrim($url, '/');
		if($rtrim){
			$url = rtrim($url, '/');
		}
		return $url;
	}

	/**
	 * Formats number of bytes to human readable string.
	 *
	 * Rounds bytes to kb, Mb, Gb, Tb. Also you can specify to how many decimals
	 * string will be formated.
	 *
	 * There are two parameters,
	 * number of bytes and number of decimals
	 *
	 * @param int $bytes - number of bytes
	 * @param int $decimals - number of decimals
	 * @return string formated file size.
	 */
	public static function formatFileSize( $bytes, $decimals = 0 ) {
		$quant = array(
			// ========================= Origin ====
			'Tb' => 1099511627776,  // pow( 1024, 4)
			'Gb' => 1073741824,     // pow( 1024, 3)
			'Mb' => 1048576,        // pow( 1024, 2)
			'kb' => 1024,           // pow( 1024, 1)
			'b ' => 1,              // pow( 1024, 0)
		);
		foreach ( $quant as $unit => $mag ) {
			if ( doubleval($bytes) >= $mag )
				return  number_format ( ($bytes / $mag), $decimals ). ' ' . $unit;	
		}
		return false;
	}

	/**
	 * Get a filename that is sanitized and unique for the given directory.
	 *
	 * If the filename is not unique, then a number will be added to the filename
	 * before the extension, and will continue adding numbers until the filename is
	 * unique.
	 *
	 * @param string $path
	 * 
	 * @return string
	 */
	public static function uniqueFilename($path) {
		// get directory
		$dir = self::getDirectory($path);

		// get filename
		$filename = self::getFileName($path);

		// sanitize the file name before we begin processing
		$filename = self::sanitizeFileName($filename);

		// separate the filename into a name and extension
		$info = pathinfo($filename);
		$ext = !empty($info['extension']) ? '.' . $info['extension'] : '';
		$name = basename($filename, $ext);

		// edge case: if file is named '.ext', treat as an empty name
		if ( $name === $ext ) $name = '';

		// Increment the file number until we have a unique file to save in $dir.
		
		$number = '';
		// change '.ext' to lower case
		if ( $ext && strtolower($ext) != $ext ) {
			$ext2 = strtolower($ext);
			$filename2 = preg_replace( '|' . preg_quote($ext) . '$|', $ext2, $filename );
			// check for both lower and upper case extension or image sub-sizes may be overwritten
			while ( Storage::has($dir . DIRECTORY_SEPARATOR . $filename) || Storage::has($dir . DIRECTORY_SEPARATOR . $filename2) ) {
				$new_number = $number + 1;
				$filename = str_replace( "$number$ext", "$new_number$ext", $filename );
				$filename2 = str_replace( "$number$ext2", "$new_number$ext2", $filename2 );
				$number = $new_number;
			}
			return $filename2;
		}
		while ( Storage::has( $dir . DIRECTORY_SEPARATOR . $filename ) ) {
			if ( '' == "$number$ext" )
				$filename = $filename . ++$number . $ext;
			else
				$filename = str_replace( "$number$ext", ++$number . $ext, $filename );
		}
		return $filename;
	}


	/**
	 * Sanitizes a filename replacing whitespace with dashes
	 *
	 * Removes special characters that are illegal in filenames on certain
	 * operating systems and special characters requiring special escaping
	 * to manipulate at the command line. Replaces spaces and consecutive
	 * dashes with a single dash. Trim period, dash and underscore from beginning
	 * and end of filename.
	 *
	 * @param string $filename The filename to be sanitized
	 * @return string The sanitized filename
	 */
	public static function sanitizeFileName( $filename ) {
		$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", chr(0));
		$filename = str_replace($special_chars, '', $filename);
		$filename = preg_replace('/[\s-]+/', '-', $filename);
		$filename = trim($filename, '.-_');

		return $filename;
		
	}

	/**
	 * Extracts filename from path, with or without extension
	 *
	 * You can optionally specify if you want extensino to be included
	 * Defaults to extension included
	 *
	 * @param string $path from wich filename will be extracted
	 * @param boolean $includeExtension flag for including extension
	 * @return string extracted filename
	 */
	public static function getFileName( $path, $includeExtension = true )
	{
		$option = ($includeExtension)?PATHINFO_BASENAME:PATHINFO_FILENAME;
		return pathinfo($path, $option);
	}

	/**
	 * Extracts directory from path
	 *
	 * @param string $path from wich filename will be extracted
	 * 
	 * @return string
	 */
	public static function getDirectory($path)
	{
		return dirname($path);
	}
}
