<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\StringsModelDriver;

use Module\Language\Language;

class Website
{
	protected $lang;
	protected $stringData;

	public function __construct()
	{
		$this->lang = Language::GetLang();

		$this->stringData = new StringsModelDriver();
		$this->stringData->Add( "lang", $this->lang );
	}

	protected function PushDependencies( $page, $model )
	{
		foreach( $model->xPath->query( "//cc:config/cc:dependency" ) as $node )
		{
			$type = $node->getAttribute( "cc:type" );
			$instance = $node->getAttribute( "cc:instance" );

			$page->PushModel( new XMLModelDriver( __NAMESPACE__ . "\\" . $type . "/" . $instance ) );
		}

		return( $page );
	}

	protected function ExpandRSSFeeds( $model )
	{
		foreach( $model->xPath->query( "//cc:rss-feed" ) as $node )
		{
			$rssModel = new XMLModelDriver( $node->getAttribute( "cc:url" ) );
			$rssNodes = $rssModel->xPath->query( "//rss" );

			if( $rssNodes->length > 0 )
			{
				$node->setAttribute( "xmlns", "http://cyber.law.harvard.edu/rss/rss.html" );

				$rssNode = $rssNodes->item( 0 );
				$newNode = $model->importNode( $rssNode, true );
				$node->appendChild( $newNode );
			}
		}

		return( $model );
	}

	protected function ExpandGetStrings( $model, $stringModel )
	{
		foreach( $model->xPath->query( "//cc:get-string" ) as $stringNode )
		{
			$currentKey = $stringNode->getAttribute( "cc:name" );
			$targetStringNode = $stringModel->xPath->query( "//xmvc:" . $currentKey );

			if( $targetStringNode->length > 0 )
			{
				$textNode = $model->createTextNode( $targetStringNode->item( 0 )->nodeValue );
				$stringNode->parentNode->replaceChild( $textNode, $stringNode );
			}
		}

		return( $model );
	}
}

?>