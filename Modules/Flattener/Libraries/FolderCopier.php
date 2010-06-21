<?php

namespace Modules\Flattener\Libraries;

use System\Libraries\FileSystem;

class FolderCopier
{
	public static function copyFolder( $inputPath, $outputPath )
	{
		// The quickest way to get this working, which assumes we are working from Windows, is to borrow xcopy.

		FileSystem::createFolderStructure( $outputPath );
		self::executeCopy( $inputPath, $outputPath );
	}

	private static function executeCopy( $inputPath, $outputPath )
	{
		$copyCommand = self::getCopyCommand( $inputPath, $outputPath );

		echo( "<b>" . $copyCommand . "<br />\n" );

		echo( "<pre>" );
		system( $copyCommand );
		echo( "</pre>" );
	}

	private static function getCopyCommand( $inputPath, $outputPath )
	{
		return "xcopy \"" . str_replace( "/", "\\", $inputPath ) . "*.*\" \"" . str_replace( "/", "\\", $outputPath ) . "\" /d /e /c /i /g /r /y";
	}
}