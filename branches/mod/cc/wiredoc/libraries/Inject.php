<?php

namespace xMVC\Mod\CC;

use xMVC\Mod\Language\Language;

class Inject
{
	public static function Href( &$view )
	{
		$models = $view->GetModels();

		foreach( $models as $model )
		{
			CC::RegisterNamespaces( $model );

			foreach( $model->xPath->query( "//*[ @inject:href != '' ]" ) as $itemNode )
			{
				$fullyQualifiedName = $itemNode->getAttribute( "inject:href" );
				$prefix = $itemNode->hasAttribute( "inject:href-prefix" ) ? $itemNode->getAttribute( "inject:href-prefix" ) : "";
				$suffix = $itemNode->hasAttribute( "inject:href-suffix" ) ? $itemNode->getAttribute( "inject:href-suffix" ) : "";

				$path = Sitemap::GetPathByFullyQualifiedNameAndLanguage( $fullyQualifiedName, Language::GetLang() );

				$linkNode = $model->createAttribute( "href" );
				$linkNode->value = $prefix . $path . $suffix;
				$itemNode->appendChild( $linkNode );

				$itemNode->removeAttribute( "inject:href" );
			}
		}
	}

	public static function RSSFeed( $model )
	{
		CC::RegisterNamespaces( $model );

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

	public static function Strings( $model, $stringModel )
	{
		CC::RegisterNamespaces( $model );

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

	public static function Lang( &$view, $lang )
	{
		$models = $view->GetModels();

		foreach( $models as $model )
		{
			CC::RegisterNamespaces( $model );

			foreach( $model->xPath->query( "//*[ @inject:lang != '' ]" ) as $itemNode )
			{
				$attributeName = $itemNode->getAttribute( "inject:lang" );

				$langNode = $model->createAttribute( $attributeName );
				$langNode->value = $lang;
				$itemNode->appendChild( $langNode );

				$itemNode->removeAttribute( "inject:lang" );
			}
		}
	}

	public static function MathCaptcha( $model )
	{
		CC::RegisterNamespaces( $model );

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