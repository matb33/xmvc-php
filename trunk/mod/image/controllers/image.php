<?php

namespace xMVC\Mod\Image;

use xMVC\Sys\Config;
use xMVC\Sys\FileSystem;

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
		$imageFile = str_replace( "#image#", $imagePath, Config::$data[ "imageProcessorFolderPattern" ] );

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
		$cacheFile = Config::$data[ "imageCacheFilePattern" ];
		$cacheFile = str_replace( "#basename#", $basename, $cacheFile );
		$cacheFile = str_replace( "#filename#", $filename, $cacheFile );
		$cacheFile = str_replace( "#hash#", md5( $cacheid ), $cacheFile );
		$cacheFile = str_replace( "#extension#", $extension, $cacheFile );
		$cacheFolder = dirname( $cacheFile ) . "/";

		if( file_exists( $cacheFile ) && !$force )
		{
			$image = $cacheFile;
		}
		else
		{
			$image = ImageProcessor::Resize( $width, $height, $imageFile );

			FileSystem::CreateFolderStructure( $cacheFolder );

			if( FileSystem::TestPermissions( $cacheFolder, FileSystem::FS_PERM_WRITE ) )
			{
				ImageProcessor::WriteImage( $image, $mimeType, $cacheFile );
			}
			else
			{
				trigger_error( "Write permissions are needed on " . $cacheFolder . " in order to use the image caching feature.", E_USER_NOTICE );
			}
		}

		ImageProcessor::OutputImage( $image, $mimeType );
	}
}

?>