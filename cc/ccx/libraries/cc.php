<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\Core;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\StringsModelDriver;
use xMVC\Sys\View;
use xMVC\Sys\Routing;
use xMVC\Sys\Config;

use Module\Language\Language;

class CC
{
	public static function PushDependencies( &$view )
	{
		$initialStackXML = $view->GetStackedModels();
		$dependencyModels = self::GetDependencies( $initialStackXML );

		foreach( $dependencyModels as $model )
		{
			$view->PushModel( $model );
		}
	}

	private static function GetDependencies( $stackXML, $dependencyModels = array() )
	{
		$model = new XMLModelDriver( $stackXML );
		$model->xPath->registerNamespace( "cc", "urn:cc:root" );
		$dependencies = $model->xPath->query( "//cc:config/cc:dependency" );

		if( count( $dependencies ) )
		{
			$subStackXML = "";

			foreach( $dependencies as $node )
			{
				$type = $node->getAttribute( "view" );
				$instance = $node->getAttribute( "model" );

				$modelName = Core::namespaceApp . $type . "/" . $instance . ".ccx";

				if( !isset( $dependencyModels[ $modelName ] ) )
				{
					$instanceModel = new XMLModelDriver( $modelName );

					$dependencyModels[ $modelName ] = $instanceModel;
					$subStackXML .= $instanceModel->GetXMLForStacking();
				}
			}

			if( strlen( $subStackXML ) > 0 )
			{
				$dependencyModels = self::GetDependencies( $subStackXML, $dependencyModels );
			}
		}

		return( $dependencyModels );
	}

	public static function InjectRSSFeed( $model )
	{
		$model->xPath->registerNamespace( "cc", "urn:cc:root" );

		foreach( $model->xPath->query( "//cc:rss-feed" ) as $node )
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
		$model->xPath->registerNamespace( "cc", "urn:cc:root" );

		foreach( $model->xPath->query( "//cc:get-string" ) as $stringNode )
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
			$model->xPath->registerNamespace( "cc", "urn:cc:root" );

			foreach( $model->xPath->query( "//*[ cc:page-name != '' ]" ) as $itemNode )
			{
				$pageNameNode = $model->xPath->query( "cc:page-name", $itemNode )->item( 0 );
				$pageName = $pageNameNode->nodeValue;

				$path = Sitemap::GetPathByPageNameAndLanguage( $pageName, Language::GetLang() );

				$linkNode = $model->createElementNS( Config::$data[ "ccNamespace" ], "cc:href", $path );
				$itemNode->appendChild( $linkNode );
			}

			foreach( $model->xPath->query( "//xhtml:a[ @cc:page-name != '' ]" ) as $itemNode )
			{
				$pageName = $itemNode->getAttribute( "cc:page-name" );

				$path = Sitemap::GetPathByPageNameAndLanguage( $pageName, Language::GetLang() );

				$linkNode = $model->createAttributeNS( "http://www.w3.org/1999/xhtml", "xhtml:href" );
				$linkNode->value = $path;
				$itemNode->appendChild( $linkNode );

				$itemNode->removeAttribute( "cc:page-name" );
			}

			foreach( $model->xPath->query( "//cc:*[ @cc:page-name != '' ]" ) as $itemNode )
			{
				$pageName = $itemNode->getAttribute( "cc:page-name" );

				$path = Sitemap::GetPathByPageNameAndLanguage( $pageName, Language::GetLang() );

				$linkNode = $model->createAttribute( "href" );
				$linkNode->value = $path;
				$itemNode->appendChild( $linkNode );

				$itemNode->removeAttribute( "cc:page-name" );
			}
		}
	}

	public static function InjectLinkNextToLangSwap( &$view )
	{
		$currentPageName = Sitemap::GetCurrentPageName();

		$models = $view->GetModels();

		foreach( $models as $model )
		{
			$model->xPath->registerNamespace( "cc", "urn:cc:root" );

			foreach( $model->xPath->query( "//cc:*[ cc:lang-swap != '' ]" ) as $itemNode )
			{
				$langSwapNode = $model->xPath->query( "cc:lang-swap[ @lang = '" . Language::GetLang() . "' ]", $itemNode )->item( 0 );
				$targetLang = $langSwapNode->nodeValue;
				$suffix = $langSwapNode->getAttribute( "suffix" );

				$path = Sitemap::GetPathByPageNameAndLanguage( $currentPageName, $targetLang ) . $suffix;

				$linkNode = $model->createElementNS( Config::$data[ "ccNamespace" ], "cc:href", $path );
				$itemNode->appendChild( $linkNode );
			}
		}
	}

	public static function InjectMathCaptcha( $model )
	{
		$model->xPath->registerNamespace( "cc", "urn:cc:root" );
		$model->xPath->registerNamespace( "form", "urn:cc:form" );

		foreach( $model->xPath->query( "//form:math-captcha" ) as $mathCaptchaNode )
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