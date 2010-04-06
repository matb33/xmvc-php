<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\Core;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\StringsModelDriver;
use xMVC\Sys\View;
use xMVC\Sys\Routing;
use xMVC\Sys\Config;

use xMVC\Mod\Language\Language;

class CC
{
	public static function RegisterNamespaces( $model )
	{
		foreach( Config::$data[ "ccNamespaces" ] as $prefix => $namespace )
		{
			$model->xPath->registerNamespace( $prefix, $namespace );
		}

		return( $model );
	}

	public static function InjectDependencies( $model )
	{
		$model = self::RegisterNamespaces( $model );

		while( self::DependencyCount( $model, $dependencies ) > 0 )
		{
			$node = $dependencies->item( 0 );
			$type = $node->localName;
			$instance = $node->getAttribute( "instance" );

			$modelName = Core::namespaceApp . $type . "/" . $instance . ".xwf";

			$instanceModel = new XMLModelDriver( $modelName );
			$instanceModel = self::RegisterNamespaces( $instanceModel );

			$originalNode = $node->cloneNode( true );

			$externalNode = $model->importNode( $instanceModel->xPath->query( "//wireframe:*" )->item( 0 ), true );
			$node->parentNode->replaceChild( $externalNode, $node );

			$childRefNodeList = $model->xPath->query( "//child:reference", $node );

			if( $childRefNodeList->length > 0 )
			{
				$childRefNode = $childRefNodeList->item( 0 );
				$importedNode = $model->importNode( $originalNode, true );
				self::ReplaceNodeWithChildren( $model, $childRefNode, $importedNode );
			}
		}

		return( $model );
	}

	private static function DependencyCount( $model, &$dependencies )
	{
		$dependencies = $model->xPath->query( "//dependency:*[ @instance ]" );

		return( $dependencies->length );
	}

	private static function ReplaceNodeWithChildren( &$model, &$refNode, &$node )
	{
		for( $i = 0; $i < $node->childNodes->length; $i++ )
		{
			$childNode = $node->childNodes->item( $i );

			if( !( $childNode instanceof \DOMText ) )
			{
				$refNode->parentNode->insertBefore( $childNode, $refNode );
			}
		}

		$refNode->parentNode->removeChild( $refNode );
	}

	public static function InjectRSSFeed( $model )
	{
		$model = self::RegisterNamespaces( $model );

		foreach( $model->xPath->query( "//inject:rss-feed" ) as $node )
		{
			$rssModel = new XMLModelDriver( $node->getAttribute( "url" ) );
			$rssNodes = $rssModel->xPath->query( "//rss" );

			if( $rssNodes->length > 0 )
			{
				$node->setAttribute( "xmlns", "http://www.w3.org/2005/Atom" );

				$rssNode = $rssNodes->item( 0 );
				$newNode = $model->importNode( $rssNode, true );
				$node->appendChild( $newNode );
			}
		}

		return( $model );
	}

	public static function InjectStrings( $model, $stringModel )
	{
		$model = self::RegisterNamespaces( $model );

		foreach( $model->xPath->query( "//inject:get-string" ) as $stringNode )
		{
			$currentKey = $stringNode->getAttribute( "name" );
			$targetStringNode = $stringModel->xPath->query( "//xmvc:" . $currentKey );

			if( $targetStringNode->length > 0 )
			{
				$textNode = $model->createTextNode( $targetStringNode->item( 0 )->nodeValue );
				$stringNode->parentNode->replaceChild( $textNode, $stringNode );
			}
		}

		return( $model );
	}

	public static function InjectLinkNextToPageName( &$view )
	{
		$models = $view->GetModels();

		foreach( $models as $model )
		{
			$model = self::RegisterNamespaces( $model );

			foreach( $model->xPath->query( "//*[ inject:href != '' ]" ) as $itemNode )
			{
				$pageNameNode = $model->xPath->query( "inject:href", $itemNode )->item( 0 );
				$pageName = $pageNameNode->nodeValue;

				$path = Sitemap::GetPathByPageNameAndLanguage( $pageName, Language::GetLang() );

				$linkNode = $model->createElementNS( Config::$data[ "ccNamespaces" ][ "link" ], "link:href", $path );
				$itemNode->appendChild( $linkNode );
			}

			foreach( $model->xPath->query( "//*[ @inject:href != '' ]" ) as $itemNode )
			{
				$pageName = $itemNode->getAttribute( "inject:href" );

				$path = Sitemap::GetPathByPageNameAndLanguage( $pageName, Language::GetLang() );

				$linkNode = $model->createAttribute( "href" );
				$linkNode->value = $path;
				$itemNode->appendChild( $linkNode );

				$itemNode->removeAttribute( "inject:href" );
			}
		}
	}

	public static function InjectLinkNextToLangSwap( &$view )
	{
		$currentPageName = Sitemap::GetCurrentPageName();

		$models = $view->GetModels();

		foreach( $models as $model )
		{
			$model = self::RegisterNamespaces( $model );

			foreach( $model->xPath->query( "//*[ inject:lang-swap != '' ]" ) as $itemNode )
			{
				$langSwapNode = $model->xPath->query( "inject:lang-swap[ @lang = '" . Language::GetLang() . "' ]", $itemNode )->item( 0 );
				$targetLang = $langSwapNode->nodeValue;
				$suffix = $langSwapNode->getAttribute( "suffix" );

				$path = Sitemap::GetPathByPageNameAndLanguage( $currentPageName, $targetLang ) . $suffix;

				$linkNode = $model->createElementNS( Config::$data[ "ccNamespaces" ][ "link" ], "link:href", $path );
				$itemNode->appendChild( $linkNode );
			}
		}
	}

	public static function InjectMathCaptcha( $model )
	{
		$model = self::RegisterNamespaces( $model );

		foreach( $model->xPath->query( "//inject:math-captcha" ) as $mathCaptchaNode )
		{
			$type = $mathCaptchaNode->getAttribute( "type" );

			switch( $type )
			{
				case "subtraction":
					$formula = rand( 1, 9 ) . " - " . rand( 1, 9 );
				break;

				case "multiplication":
					$formula = rand( 1, 9 ) . " x " . rand( 1, 9 );
				break;

				case "division":
					$formula = rand( 1, 9 ) . " / " . rand( 1, 9 );
				break;

				case "addition":
				default:
					$formula = rand( 1, 9 ) . " + " . rand( 1, 9 );
			}

			eval( "\$answer = (" . str_replace( "x", "*", $formula ) . ");" );

			$answerAttribute = $model->createAttribute( "answer" );
			$answerAttribute->value = $answer;
			$mathCaptchaNode->appendChild( $answerAttribute );

			$answerMD5Attribute = $model->createAttribute( "answer-md5" );
			$answerMD5Attribute->value = md5( $answer );
			$mathCaptchaNode->appendChild( $answerMD5Attribute );

			$formulaTextNode = $model->createTextNode( $formula );
			$mathCaptchaNode->appendChild( $formulaTextNode );
		}

		return( $model );
	}
}

?>