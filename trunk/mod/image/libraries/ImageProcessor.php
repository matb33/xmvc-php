<?php

namespace xMVC\Mod\Image;

class ImageProcessor
{
	public static function Resize( $width, $height, $imageFile )
	{
		list( $fullSizeWidth, $fullSizeHeight, $mimeType, $lastModified, $b, $f, $e ) = self::GetImageData( $imageFile );
		list( $newWidth, $newHeight ) = self::DetermineNewWidthAndHeight( $width, $height, $fullSizeWidth, $fullSizeHeight );

		$fullSizeImage = self::GetImage( $imageFile );

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
					return( imagejpeg( $image, null, 100 ) );
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

	public static function WriteImage( $image, $mimeType, $filename )
	{
		if( is_resource( $image ) )
		{
			switch( $mimeType )
			{
				case "image/jpeg":
					return( imagejpeg( $image, $filename, 100 ) );
				break;
				case "image/gif":
					return( imagegif( $image, $filename ) );
				break;
				case "image/png":
					return( imagepng( $image, $filename ) );
				break;
				default:
					return( false );
			}
		}
		else
		{
			file_put_contents( $filename, file_get_contents( $image, FILE_BINARY ), FILE_BINARY );
		}
	}

	public static function GetImage( $imageFile )
	{
		list( $fullSizeWidth, $fullSizeHeight, $mimeType, $lastModified, $b, $f, $e ) = self::GetImageData( $imageFile );

		switch( $mimeType )
		{
			case "image/jpeg":
				return( imagecreatefromjpeg( $imageFile ) );
			break;
			case "image/gif":
				return( imagecreatefromgif( $imageFile ) );
			break;
			case "image/png":
				return( imagecreatefrompng( $imageFile ) );
			break;
			default:
				return( false );
		}
	}
}

?>