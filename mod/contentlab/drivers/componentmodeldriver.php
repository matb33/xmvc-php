<?php

namespace Module\ContentLAB;

use xMVC\Sys\ModelDriver;
use xMVC\Sys\ModelDriverInterface;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\View;

class ComponentModelDriver extends ModelDriver implements ModelDriverInterface
{
	private $instances;

	public function __construct( $instanceName, $data = null )
	{
		parent::__construct();

		$this->TransformForeignToXML( $instanceName, $data, 0 );
	}

	public function TransformForeignToXML()
	{
		$instanceName = func_get_arg( 0 );
		$data = func_get_arg( 1 );
		$depth = func_get_arg( 2 );

		$this->instances[] = $instanceName;

		$instance = $this->LoadInstanceXML( $instanceName, $data );

		// Iterate through all instances with the load attribute, indicating that they should be loaded

		$loads = array();

		foreach( $instance->xPath->query( "//clab:instance-load" ) as $node )
		{
			$loads[] = $node;
		}

		if( count( $loads ) )
		{
			$loads = array_reverse( $loads );

			foreach( $loads as $node )
			{
				$instanceToLoad = $node->getAttribute( "clab:definition" ) . "/" . $node->getAttribute( "clab:instance-name" );

				$loadedInstance = $this->TransformForeignToXML( $instanceToLoad, $data, $depth + 1 );

				$nodeList = $loadedInstance->xPath->query( "//clab:instance" );

				if( $nodeList !== false )
				{
					$newNode = $instance->importNode( $nodeList->item( 0 ), true );

					$node->parentNode->replaceChild( $newNode, $node );
				}
			}
		}

		if( $depth == 0 )
		{
			$this->SetXML( $instance->GetXML( true ) );
		}

		return( $instance );
	}

	private function LoadInstanceXML( $instanceName, $data )
	{
		$xmlModelFile = "mod/contentlab/components/instances/" . $instanceName . ".xml";

		$instance = new XMLModelDriver( $this->LoadModelXML( $xmlModelFile, $data ) );

		return( $instance );
	}

	private function SaveInstanceXML( $instanceName, $instance )
	{
		$xmlModelFile = "mod/contentlab/components/instances/" . $instanceName . ".xml";

		$rootNode = $instance->xPath->query( "/xmvc:root/clab:instance" )->item( 0 );

		$success = file_put_contents( $xmlModelFile, $instance->saveXML( $rootNode ) );

		return( $success );
	}

	public function ProcessReturn( $definition, $instanceName, $content )
	{
		$success			= false;
		$errorMessage		= "";
		$fullInstanceName	= $definition . "/" . $instanceName;
		$mashView			= new View();

		$xslViewName		= $definition;
		$xslViewFile		= "mod/contentlab/components/definitions/" . $xslViewName . "/return.xsl";

		$xslBody			= $mashView->ImportXSL( null, $xslViewFile );

		if( ! is_null( $xslBody ) )
		{
			$xslHead = $mashView->GetXMLHead( null, true );
			$xslFoot = $mashView->GetXMLFoot( true );

			$mashView->SetXMLData( $content );
			$mashView->SetXSLData( $xslHead . $xslBody . $xslFoot );

			$changes = new XMLModelDriver( $mashView->ProcessView( true, null ) );

			$nodes = $changes->xPath->query( "//clab:instance-return/clab:*" );

			if( $nodes->length > 0 )
			{
				$this->WalkReturnTree( $nodes, $changes, $results );
				$instance = $this->LoadInstanceXML( $fullInstanceName, null );
				$this->ModifyInstance( $instance, $results );
				$success = $this->SaveInstanceXML( $fullInstanceName, $instance );
			}
			else
			{
				$errorMessage = "return.xsl doesn't take into account this particular instance";
			}
		}
		else
		{
			$errorMessage = "A return.xsl file has not been written for this definition";
		}

		return( array( $success, $errorMessage, $fullInstanceName, $mashView, $results ) );
	}

	private function WalkReturnTree( $nodes, &$changes, &$results, $path = "/" )
	{
		foreach( $nodes as $index => $node )
		{
			$newPath	= ( $path . "/" . $node->nodeName . "[" . ( $index + 1 ) . "]" );
			$subNodes	= $changes->xPath->query( "clab:*", $node );

			if( $subNodes->length > 0 )
			{
				$this->WalkReturnTree( $subNodes, $changes, $results, $newPath );
			}
			else
			{
				// Reached innermost content
				$results[ $newPath ] = $node;
			}
		}
	}

	private function ModifyInstance( $instance, $results )
	{
		foreach( $results as $path => $changedNode )
		{
			$targetNode = $instance->xPath->query( $path )->item( 0 );
			$newNode	= $instance->importNode( $changedNode, true );

			$targetNode->parentNode->replaceChild( $newNode, $targetNode );
		}

		return( $instance );
	}
}

?>