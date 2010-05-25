<?php

class FolderCopier
{
	public static function CopyFolder( $inputPath, $outputPath )
	{
		// The quickest way to get this working, which assumes we are working from Windows, is to borrow xcopy.

		self::CreateFolder( $outputPath );
		self::ExecuteCopy( $inputPath, $outputPath );
	}

	private static function CreateFolder( $path )
	{
		@mkdir( $path );
	}

	private static function ExecuteCopy( $inputPath, $outputPath )
	{
		$copyCommand = self::GetCopyCommand( $inputPath, $outputPath );

		echo( "<b>" . $copyCommand . "<br />\n" );

		echo( "<pre>" );
		system( $copyCommand );
		echo( "</pre>" );
	}

	private static function GetCopyCommand( $inputPath, $outputPath )
	{
		return( "xcopy \"" . $inputPath . "*.*\" \"" . $outputPath . "\" /d /e /c /i /g /r /y" );
	}
}

?>