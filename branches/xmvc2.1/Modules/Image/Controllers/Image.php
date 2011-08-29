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

		$imagePath = implode( "/", $args );
		$imageFile = StringUtils::replaceTokensInPattern( Config::$data[ "imageProcessorFolderPattern" ], array( "image" => $imagePath ) );

		if( $this->verifyResizeParameters( $width, $height, $imageFile ) )
		{
			$this->resizeImage( $width, $height, $imageFile );
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

	private function resizeImage( $width, $height, $imageFile )
	{
		list( $fullSizeWidth, $fullSizeHeight, $mimeType, $lastModified, $basename, $filename, $extension, $basePath ) = ImageProcessor::getImageData( $imageFile );
		list( $newWidth, $newHeight ) = ImageProcessor::determineNewWidthAndHeight( $width, $height, $fullSizeWidth, $fullSizeHeight );

		$filename = $basePath . $filename;

		$cacheID = $newWidth . "x" . $newHeight;
		$cacheFile = StringUtils::replaceTokensInPattern( Config::$data[ "imageCacheFilePattern" ], array( "filename" => $filename, "cacheid" => $cacheID, "extension" => $extension ) );

		if( $newWidth == $fullSizeWidth && $newHeight == $fullSizeHeight )
		{
			$image = $imageFile;
		}
		else
		{
			$image = ImageProcessor::resize( $width, $height, $imageFile );
			ImageProcessor::writeImage( $image, $mimeType, $cacheFile );
		}

		ImageProcessor::outputImage( $image, $mimeType );
	}
}