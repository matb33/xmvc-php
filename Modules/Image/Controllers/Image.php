<?php

namespace Modules\Image\Controllers;

use System\Libraries\Config;
use System\Libraries\FileSystem;
use Modules\Utils\Libraries\StringUtils;
use Modules\Cache\Libraries\Cache;
use Modules\Image\Libraries\ImageProcessor;

class Image
{
	public function resize()
	{
		$args = func_get_args();

		$width = array_shift( $args );
		$height = array_shift( $args );
		$force = false;

		if( $args[ count( $args ) - 1 ] == "force" )
		{
			$force = true;
			array_pop( $args );
		}

		$imagePath = implode( "/", $args );
		$imageFile = StringUtils::replaceTokensInPattern( Config::$data[ "imageProcessorFolderPattern" ], array( "image" => $imagePath ) );

		if( $this->verifyResizeParameters( $width, $height, $imageFile ) )
		{
			$this->resizeImage( $width, $height, $imageFile, $force );
		}
		else
		{
			trigger_error( "Incorrect parameters or invalid image file.", E_USER_ERROR );
		}
	}

	private function verifyResizeParameters( $width, $height, $imageFile )
	{
		$widthTest = ( ( int )$width > 0 || $width == "auto" );
		$heightTest = ( ( int )$height > 0 || $height == "auto" );
		$imageFileTest = file_exists( $imageFile );

		return $widthTest && $heightTest && $imageFileTest;
	}

	private function resizeImage( $width, $height, $imageFile, $force )
	{
		list( $fullSizeWidth, $fullSizeHeight, $mimeType, $lastModified, $basename, $filename, $extension ) = ImageProcessor::getImageData( $imageFile );
		list( $newWidth, $newHeight ) = ImageProcessor::determineNewWidthAndHeight( $width, $height, $fullSizeWidth, $fullSizeHeight );

		$cacheID = md5( $newWidth . "x" . $newHeight . "-" . $fullSizeWidth . "x" . $fullSizeHeight . "-" . $imageFile . "-" . $lastModified );
		$cache = new Cache( Config::$data[ "imageCacheFilePattern" ], array( "basename" => $basename, "filename" => $filename, "hash" => md5( $cacheID ), "extension" => $extension ), $cacheID, false );
		$cacheFile = $cache->filename;

		if( file_exists( $cacheFile ) && !$force )
		{
			$image = $cacheFile;
		}
		else
		{
			if( $newWidth == $fullSizeWidth && $newHeight == $fullSizeHeight )
			{
				$image = $imageFile;
			}
			else
			{
				$image = ImageProcessor::resize( $width, $height, $imageFile );

				if( $cache->prepCacheFolder() )
				{
					ImageProcessor::writeImage( $image, $mimeType, $cacheFile );
				}
			}
		}

		ImageProcessor::outputImage( $image, $mimeType );
	}
}