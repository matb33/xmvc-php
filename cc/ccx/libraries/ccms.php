<?php

namespace Module\CC;

use xMVC\Sys\Core;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\View;
use xMVC\Sys\Config;

class CCMS
{
	public static $container;
	public static $content;

	public static function SetContainer( $container )
	{
		self::$container = $container;
	}

	public static function SetContent( $content )
	{
		self::$content = $content;
	}

	public static function TransformHTML( $data )
	{
		$config = array( "indent" => true, "input-html" => true, "output-xml" => true, "wrap" => false );
		$tidy = tidy_parse_string( $data, $config, "UTF8" );
		$tidy->cleanRepair();

		$transformer = new View( Core::namespaceApp . self::$container . ".write" );
		$transformer->PushModel( new XMLModelDriver( $tidy->value ) );

		return( new XMLModelDriver( $transformer->ProcessAsXML() ) );
	}

	public static function ImportTransformation( $transformedModel, $contentModel )
	{
		$contentModel = new XMLModelDriver( self::GetCCXFile() );

		$nodeToImport = $transformedModel->xPath->query( "/xmvc:root/cc:root" )->item( 0 );
		$targetNode = $contentModel->xPath->query( "/xmvc:root/cc:root" )->item( 0 );
		$newNode = $contentModel->importNode( $nodeToImport, true );
		$targetNode->parentNode->replaceChild( $newNode, $targetNode );

		return( $contentModel );
	}

	public static function SaveCCX( $contentModel )
	{
		$contentModel->normalizeDocument();

		$ccxFile = self::GetCCXFile();
		$rootNode = $contentModel->xPath->query( "/xmvc:root/cc:root" )->item( 0 );

		$xmlDeclaration = View::GetXMLHead( null, true );

		return( file_put_contents( $ccxFile, $xmlDeclaration . $contentModel->saveXML( $rootNode ) ) );
	}

	public static function GetCCXFile()
	{
		return( Config::$data[ "svnWorkingFolder" ] . self::$container . "/" . self::$content . ".ccx" );
	}
}

?>