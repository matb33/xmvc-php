<?php

namespace Module\ContentLAB;

use xMVC\Sys\Loader;
use xMVC\Sys\View;
use xMVC\Sys\Normalize;
use xMVC\Sys\OutputHeaders;

class Contentlab
{
	public function Root()
	{
		$args = func_get_args();

		$instanceName = Sitemap::GetInstanceName( $args );

		// ============ TEMP ==============================
		if( isset( $args[ 0 ] ) && $args[ 0 ] == "refresh" )
		{
			echo "Generating sitemap.xml...";
			Sitemap::Generate();
			echo "done.";
			exit();
		}
		// ================================================

		if( ! is_null( $instanceName ) )
		{
			// Load instance for this particular page. Related external instances will be
			// internalized into this single instance.

			$instance = new ComponentModelDriver( $instanceName );

			list( $definitions, $instanceNames ) = $this->GetDefinitionsAndInstanceNames( $instance );

			$mainView = new View( __NAMESPACE__ . "\\xhtml" );

			// Load any controllers present for each definition involved. They can tie
			// themselves into the main view since it is being passed down.

			$this->LoadControllers( $definitions, $instanceNames, $instance, $mainView );

			// Load a special view that handles the basics of XHTML markup (html, head, body)
			// It also takes care of referencing each components's XSL view.

			$mainView->PushModel( $instance );
			$mainView->RenderAsHTML( array( "definitions" => $definitions ) );
		}
	}

	private function GetDefinitionsAndInstanceNames( $instance )
	{
		// All definitions have been exposed after LoadInstance was called.  Thus, this function does not need to
		// iterate through multiple files.  All definitions will be present in the current instance.

		$definitions = array();
		$instanceNames = array();

		$query = $instance->xPath->query( "//clab:instance" );

		foreach( $query as $node )
		{
			$definitions[] = $node->getAttribute( "clab:definition" );
			$instanceNames[] = $node->getAttribute( "clab:instance-name" );
		}

		return( array( $definitions, $instanceNames ) );
	}

	private function LoadControllers( $definitions, $instanceNames, &$instance, &$view )
	{
		$controllers = array();

		if( is_array( $definitions ) )
		{
			foreach( array_keys( $definitions ) as $path )
			{
				$definition = $definitions[ $path ];
				$instanceName = $instanceNames[ $path ];

				$controllerFile = "mod/contentlab/components/definitions/" . $definition . "/controller.php";

				if( file_exists( $controllerFile ) )
				{
					include_once( $controllerFile );

					$controllerClassName = __NAMESPACE__ . "\\" . Normalize::MethodOrClassName( $definition );

					$controllers[] = new $controllerClassName( $instance, $view, $definition, $instanceName );
				}
			}
		}

		return( $controllers );
	}

	// Process requests from javascript

	public function Request( $action, $definition, $instanceName )
	{
		$success = false;

		$instance = new ComponentModelDriver( $definition . "/" . $instanceName );

		if( ! is_null( $action ) )
		{
			$content = strlen( $_REQUEST[ "d" ] ) > 0 ? base64_decode( $_REQUEST[ "d" ] ) : null;

			if( ! is_null( $content ) )
			{
				switch( $action )
				{
					case "write":
						list( $success, $errorMessage, $fullInstanceName, $mashView, $results ) = $instance->ProcessReturn( $definition, $instanceName, $content );
					break;
				}
			}
		}

		$xmlResults = "";
		$xmlDebug = "";

		$xmlDebug .= "<clab:debug>\n";
		$xmlDebug .= "<clab:error-message><![CDATA[\n" . $errorMessage . "\n]]></clab:error-message>\n";
		$xmlDebug .= "<clab:full-instance-name><![CDATA[\n" . $fullInstanceName . "\n]]></clab:full-instance-name>\n";
		$xmlDebug .= "<clab:xml-data><![CDATA[\n" . $mashView->GetXMLData() . "\n]]></clab:xml-data>\n";
		$xmlDebug .= "<clab:xsl-data><![CDATA[\n" . $mashView->GetXSLData() . "\n]]></clab:xsl-data>\n";
		$xmlDebug .= "<clab:results><![CDATA[\n" . print_r( $results, true ) . "\n]]></clab:results>\n";
		$xmlDebug .= "</clab:debug>\n";

		if( $success )
		{
			$xmlResults .= "<clab:response xmlns:clab=\"http://clab.xmvc.org/ns/clab/1.0\">\n";
			$xmlResults .= "<clab:success>1</clab:success>\n";
			$xmlResults .= "</clab:response>";
		}
		else
		{
			$xmlResults .= "<clab:response xmlns:clab=\"http://clab.xmvc.org/ns/clab/1.0\">\n";
			$xmlResults .= "<clab:success>0</clab:success>\n";
			$xmlResults .= $xmlDebug;
			$xmlResults .= "</clab:response>";
		}

		OutputHeaders::XML();

		echo( $xmlResults );
	}
}

?>