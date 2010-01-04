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

	protected function PushDependencies( &$view )
	{
		$initialStackXML = $view->GetStackedModels();
		$dependencyModels = $this->GetDependencies( $initialStackXML );

		foreach( $dependencyModels as $model )
		{
			$view->PushModel( $model );
		}
	}

	private function GetDependencies( $stackXML, $dependencyModels = array() )
	{
		$model = new XMLModelDriver( $stackXML );
		$dependencies = $model->xPath->query( "//cc:config/cc:dependency" );

		if( count( $dependencies ) )
		{
			$subStackXML = "";

			foreach( $dependencies as $node )
			{
				$type = $node->getAttribute( "cc:type" );
				$instance = $node->getAttribute( "cc:instance" );

				$modelName = __NAMESPACE__ . "\\" . $type . "/" . $instance;

				if( !isset( $dependencyModels[ $modelName ] ) )
				{
					$instanceModel = new XMLModelDriver( $modelName );

					$dependencyModels[ $modelName ] = $instanceModel;
					$subStackXML .= $instanceModel->GetXMLForStacking();
				}
			}

			if( strlen( $subStackXML ) > 0 )
			{
				$dependencyModels = $this->GetDependencies( $subStackXML, $dependencyModels );
			}
		}

		return( $dependencyModels );
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