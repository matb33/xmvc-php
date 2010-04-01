<?php

namespace xMVC\Mod\Image;

class ImageProcessor
{
	public static function Resize( $width, $height, $imageFile )
	{
		list( $fullSizeWidth, $fullSizeHeight, $mimeType, $lastModified, $b, $f, $e ) = self::GetImageData( $imageFile );
		list( $newWidth, $newHeight ) = self::DetermineNewWidthAndHeight( $width, $height, $fullSizeWidth, $fullSizeHeight );

		switch( $mimeType )
		{
			case "image/jpeg":
				$fullSizeImage = imagecreatefromjpeg( $imageFile );
			break;
			case "image/gif":
				$fullSizeImage = imagecreatefromgif( $imageFile );
			break;
			case "image/png":
				$fullSizeImage = imagecreatefrompng( $imageFile );
			break;
			default:
				return( false );
		}

		$resizedImage = imagecreatetruecolor( $newWidth, $newHeight );
		$whiteColor = imagecolorallocate( $resizedImage, 255, 255, 255 );

		imagefilledrectangle( $resizedImage, 0, 0, $newWidth, $newHeight, $whiteColor );
		imagecopyresampled( $resizedImage, $fullSizeImage, 0, 0, 0, 0, $newWidth, $newHeight, $fullSizeWidth, $fullSizeHeight );

		return( $resizedImage );
	}

	public static function GetImageData( $imageFile )
	{
		$imageSizeData = getimagesize( $imageFile );
		$imageFileInfo = pathinfo( $imageFile );

		return(
			array(
				( int )$imageSizeData[ 0 ],
				( int )$imageSizeData[ 1 ],
				$imageSizeData[ "mime" ],
				filemtime( $imageFile ),
				$imageFileInfo[ "basename" ],
				$imageFileInfo[ "filename" ],
				$imageFileInfo[ "extension" ]
			)
		);
	}

	public static function DetermineNewWidthAndHeight( $requestedWidth, $requestedHeight, $currentWidth, $currentHeight )
	{
		if( $requestedWidth != "auto" && $requestedHeight != "auto" )
		{
			$newWidth = $requestedWidth;
			$newHeight = $requestedHeight;
		}
		else
		{
			if( $requestedWidth == "auto" && $requestedHeight != "auto" )
			{
				$newWidth = round( $requestedHeight / $currentHeight * $currentWidth );
				$newHeight = $requestedHeight;
			}
			elseif( $requestedWidth != "auto" && $requestedHeight == "auto" )
			{
				$newWidth = $requestedWidth;
				$newHeight = round( $requestedWidth / $currentWidth * $currentHeight );
			}
			else
			{
				$newWidth = $currentWidth;
				$newHeight = $currentHeight;
			}
		}

		return( array( ( int )$newWidth, ( int )$newHeight ) );
	}

	public static function OutputImage( $image, $mimeType )
	{
		header( "Content-Type: " . $mimeType );

		if( is_resource( $image ) )
		{
			switch( $mimeType )
			{
				case "image/jpeg":
					return( imagejpeg( $image ) );
				break;
				case "image/gif":
					return( imagegif( $image ) );
				break;
				case "image/png":
					return( imagepng( $image ) );
				break;
				default:
					return( false );
			}
		}
		else
		{
			readfile( $image );
		}
	}

	public static function WriteImage( $imageData, $mimeType, $filename )
	{
		switch( $mimeType )
		{
			case "image/jpeg":
				return( imagejpeg( $imageData, $filename ) );
			break;
			case "image/gif":
				return( imagegif( $imageData, $filename ) );
			break;
			case "image/png":
				return( imagepng( $imageData, $filename ) );
			break;
			default:
				return( false );
		}
	}
}

?>