<?php

namespace xMVC\Mod\Image;

use xMVC\Sys\Config;
use xMVC\Sys\FileSystem;
use xMVC\Mod\Utils\StringUtils;
use xMVC\Mod\CC\Cache;

class Image
{
	public function Resize()
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
		$imageFile = StringUtils::ReplaceTokensInPattern( Config::$data[ "imageProcessorFolderPattern" ], array( "image" => $imagePath ) );

		if( $this->VerifyResizeParameters( $width, $height, $imageFile ) )
		{
			$this->ResizeImage( $width, $height, $imageFile, $force );
		}
		else
		{
			trigger_error( "Incorrect parameters or invalid image file.", E_USER_ERROR );
		}
	}

	private function VerifyResizeParameters( $width, $height, $imageFile )
	{
		$widthTest = ( ( int )$width > 0 || $width == "auto" );
		$heightTest = ( ( int )$height > 0 || $height == "auto" );
		$imageFileTest = file_exists( $imageFile );

		return( $widthTest && $heightTest && $imageFileTest );
	}

	private function ResizeImage( $width, $height, $imageFile, $force )
	{
		list( $fullSizeWidth, $fullSizeHeight, $mimeType, $lastModified, $basename, $filename, $extension ) = ImageProcessor::GetImageData( $imageFile );

		$cacheid = $width . "x" . $height . "-" . $fullSizeWidth . "x" . $fullSizeHeight . "-" . $imageFile . "-" . $lastModified;
		$cacheFile = StringUtils::ReplaceTokensInPattern( Config::$data[ "imageCacheFilePattern" ], array( "basename" => $basename, "filename" => $filename, "hash" => md5( $cacheid ), "extension" => $extension ) );

		if( file_exists( $cacheFile ) && !$force )
		{
			$image = $cacheFile;
		}
		else
		{
			$image = ImageProcessor::Resize( $width, $height, $imageFile );

			if( Cache::PrepCacheFolder( $cacheFile ) )
			{
				ImageProcessor::WriteImage( $image, $mimeType, $cacheFile );
			}
		}

		ImageProcessor::OutputImage( $image, $mimeType );
	}
}

?>