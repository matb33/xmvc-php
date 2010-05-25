<?php

class Flattener
{
	private static $outputPath;

	public static function SetOutputPath( $outputPath )
	{
		self::$outputPath = $outputPath;
	}

	public static function FlattenURL( $url )
	{
		$completeURL = "http://" . $_SERVER[ "HTTP_HOST" ] . $url;

		echo( "Flattening: " . $completeURL . "<br />\n" );

		$contents = self::GetContentsAtURL( $completeURL );

		if( $contents !== false )
		{
			self::WriteContents( $url, $contents );
		}
		else
		{
			echo( "<b>URL doesn't exist. SKIPPED.</b><br />\n" );
		}

		echo "<br />\n";
	}

	private static function GetContentsAtURL( $url )
	{
		return( file_get_contents( $url ) );
	}

	private static function WriteContents( $url, $contents )
	{
		$destinationFolder = self::ConvertURLToFolderStructure( $url );

		self::CreateFolderStructure( $destinationFolder );
		self::WriteContentsToDestinationFolder( $contents, $destinationFolder );
	}

	private static function ConvertURLToFolderStructure( $url )
	{
		$path = str_replace( "\\", "/", self::$outputPath );

		if( substr( $path, -1 ) == "/" )
		{
			$path = substr( $path, 0, -1 );
		}

		$path .= $url;

		return( $path );
	}

	private static function CreateFolderStructure( $folder )
	{
		$folderParts = explode( "/", preg_replace( "/^[A-Z]{1}:/i", "", $folder ) );

		for( $i = 2; $i < count( $folderParts ); $i++ )
		{
			$builtPath = implode( "/", array_slice( $folderParts, 0, $i ) );

			if( self::PathDoesntExist( $builtPath ) )
			{
				echo( "<i>Creating folder " . $builtPath . "</i><br />\n" );

				mkdir( $builtPath );
			}
		}
	}

	private static function PathDoesntExist( $path )
	{
		if( $path !== false )
		{
			if( ! file_exists( $path ) )
			{
				return( true );
			}
		}

		return( false );
	}

	private static function WriteContentsToDestinationFolder( $contents, $folder )
	{
		echo( "<i>Writing contents to " . $folder . "index.jsf</i><br />\n" );

		file_put_contents( $folder . "index.jsf", $contents );
	}
}

?>