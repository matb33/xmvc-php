<?php

namespace Module\ContentLAB;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\Routing;

class Sitemap
{
	private static $model;

	public static function Generate()
	{
		// The sitemap file is what the site uses to determine how to load itself.  It also serves as a sitemaps.org compliant xml sitemap file.

		// TO-DO: Build this using DOMDocument instead of with strings

		$urlNodesIndexed	= array();
		$urlNodesFree		= array();

		$namespaceURI		= "http://clab.xmvc.org/ns/clab-sitemap/1.0";
		$instancesPath		= "mod/contentlab/components/instances/";
		$componentRelations	= array( "clab:child-for", "clab:visible-for", "clab:clickable-for" );

		$mainDir = dir( $instancesPath );

		while( ( $definition = $mainDir->read() ) !== false )
		{
			if( $definition != "." && $definition != ".." )
			{
				if( is_dir( $instancesPath . $definition ) )
				{
					$instanceDir = dir( $instancesPath . $definition );

					while( ( $entry = $instanceDir->read() ) !== false )
					{
						if( $entry != "." && $entry != ".." )
						{
							$instanceName = basename( $entry, ".xml" );

							$instance = new ComponentModelDriver( $definition . "/" . $instanceName );

							if( $instance->xPath->query( "//clab:metadata" )->length > 0 )
							{
								// We've determined that this is indeed a page that should be added to the sitemap, based on the fact that metadata is present.

								$instanceData = array();

								$metaData = array();
								$metaData[ "clab:protocol" ]	= Routing::URIProtocol();
								$metaData[ "clab:host" ]		= $_SERVER[ "HTTP_HOST" ];

								foreach( $instance->xPath->query( "//clab:metadata/*" ) as $node )
								{
									if( in_array( $node->nodeName, $componentRelations ) )
									{
										$components = $instance->xPath->query( "clab:component", $node );

										foreach( $components as $component )
										{
											$metaData[ $node->nodeName ][] = array(
												"clab:definition"		=> $instance->xPath->query( "@clab:definition", $component )->item( 0 )->nodeValue,
												"clab:instance-name"	=> $instance->xPath->query( "@clab:instance-name", $component )->item( 0 )->nodeValue
											);
										}
									}
									else
									{
										$metaData[ $node->nodeName ] = $instance->xPath->query( "//clab:metadata/" . $node->nodeName )->item( 0 )->nodeValue;
									}
								}

								$instanceData[ "url" ]			= $metaData[ "clab:protocol" ] . "://" . $metaData[ "clab:host" ] . $metaData[ "clab:urlpath" ];
								$instanceData[ "file" ]			= realpath( $instancesPath . $definition . "/" . $entry );
								$instanceData[ "modified" ]		= filemtime( $instanceData[ "file" ] );
								$instanceData[ "sortIndex" ]	= ( int )$metaData[ "clab:sort-index" ];

								$urlNodeXML = "";
								$urlNodeXML .= "	<url>\n";
								$urlNodeXML .= "		<loc>" . htmlentities( $instanceData[ "url" ], ENT_QUOTES, "UTF-8" ) . "</loc>\n";
								$urlNodeXML .= "		<lastmod>" . date( "Y-m-d", $instanceData[ "modified" ] ) . "</lastmod>\n";
								$urlNodeXML .= "		<clab:name>" . htmlentities( $metaData[ "clab:name" ], ENT_QUOTES, "UTF-8" ) . "</clab:name>\n";
								$urlNodeXML .= "		<clab:definition>" . $definition. "</clab:definition>\n";
								$urlNodeXML .= "		<clab:instance-name>" . $instanceName . "</clab:instance-name>\n";
								$urlNodeXML .= "		<clab:protocol>" . $metaData[ "clab:protocol" ] . "</clab:protocol>\n";
								$urlNodeXML .= "		<clab:host>" . $metaData[ "clab:host" ] . "</clab:host>\n";
								$urlNodeXML .= "		<clab:urlpath>" . htmlentities( $metaData[ "clab:urlpath" ], ENT_QUOTES, "UTF-8" ) . "</clab:urlpath>\n";

								foreach( $componentRelations as $relationTag )
								{
									if( isset( $metaData[ $relationTag ] ) )
									{
										$urlNodeXML .= "		<" . $relationTag . ">\n";

										foreach( $metaData[ $relationTag ] as $data )
										{
											$urlNodeXML .= "			<clab:component ";
											$urlNodeXML .= "clab:definition=\"" . htmlentities( $data[ "clab:definition" ], ENT_QUOTES, "UTF-8" ) . "\" ";
											$urlNodeXML .= "clab:instance-name=\"" . htmlentities( $data[ "clab:instance-name" ], ENT_QUOTES, "UTF-8" ) . "\" ";
											$urlNodeXML .= "/>\n";
										}

										$urlNodeXML .= "		</" . $relationTag . ">\n";
									}
								}

								$urlNodeXML .= "	</url>\n";

								if( $instanceData[ "sortIndex" ] > 0 )
								{
									$urlNodesIndexed[ $instanceData[ "sortIndex" ] ] = $urlNodeXML;
								}
								else
								{
									$urlNodesFree[] = $urlNodeXML;
								}
							}
						}
					}

					$instanceDir->close();
				}
			}
		}

		$mainDir->close();

		// Generate sitemap XML

		$xmlString  = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		$xmlString .= "<urlset\n";
		$xmlString .= "	xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
		$xmlString .= "	xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
		$xmlString .= "	url=\"http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\"\n";
		$xmlString .= "	xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
		$xmlString .= "	xmlns:clab=\"" . $namespaceURI . "\">\n";
		$xmlString .= "\n";

		ksort( $urlNodesIndexed );

		$urlNodes = array_merge( $urlNodesIndexed, $urlNodesFree );

		foreach( $urlNodes as $urlNodeXML )
		{
			$xmlString .= $urlNodeXML;
		}

		$xmlString .= "</urlset>";

		$success = file_put_contents( "mod/contentlab/" . Loader::modelFolder . "/sitemap.xml", $xmlString );

		return( $success );
	}

	public static function GetInstanceName( $args )
	{
		$instanceName = null;

		self::LoadSiteMap();

		if( ! is_null( self::$model ) )
		{
			$completeURL = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . "/" . ( count( $args ) ? implode( "/", $args ) . "/" : "" );

			$instanceName  = self::$model->xPath->query( "//s:url[s:loc[.='" . $completeURL . "']]/clab:definition" )->item( 0 )->nodeValue . "/";
			$instanceName .= self::$model->xPath->query( "//s:url[s:loc[.='" . $completeURL . "']]/clab:instance-name" )->item( 0 )->nodeValue;
		}

		if( is_null( $instanceName ) || $instanceName == "/" )
		{
			$instanceName = "error404/error404";
		}

		return( $instanceName );
	}

	public static function LoadSiteMap()
	{
		self::$model = null;

		$sitemapFile = "mod/contentlab/" . Loader::modelFolder . "/sitemap.xml";

		if( ! file_exists( $sitemapFile ) )
		{
			self::Generate();
		}

		if( file_exists( $sitemapFile ) )
		{
			self::$model = new XMLModelDriver( file_get_contents( $sitemapFile ) );
			self::$model->xPath->registerNamespace( "s", "http://www.sitemaps.org/schemas/sitemap/0.9" );
		}

		return( self::$model );
	}

	public static function GetHierarchy( $callerDefinition, $callerInstanceName, $parentInstanceName = "" )
	{
		$hierarchy = array();

		if( $parentInstanceName == "" )
		{
			$xPathQuery = "//s:url[count(clab:child-for/clab:component)=0]";
		}
		else
		{
			$xPathQuery = "//s:url[clab:child-for/clab:component[@clab:instance-name='" . $parentInstanceName . "']]";
		}

		foreach( self::$model->xPath->query( $xPathQuery ) as $node )
		{
			$instanceName = $node->getElementsByTagName( "instance-name" )->item( 0 )->nodeValue;
			$childNodes = self::GetHierarchy( $callerDefinition, $callerInstanceName, $instanceName );

			$data = array();
			$data[ "loc" ]					= $node->getElementsByTagName( "loc" )->item( 0 )->nodeValue;
			$data[ "lastmod" ]				= $node->getElementsByTagName( "lastmod" )->item( 0 )->nodeValue;
			$data[ "name" ]					= $node->getElementsByTagName( "name" )->item( 0 )->nodeValue;
			$data[ "definition" ]			= $node->getElementsByTagName( "definition" )->item( 0 )->nodeValue;
			$data[ "instance-name" ]		= $instanceName;
			$data[ "protocol" ]				= $node->getElementsByTagName( "protocol" )->item( 0 )->nodeValue;
			$data[ "host" ]					= $node->getElementsByTagName( "host" )->item( 0 )->nodeValue;
			$data[ "urlpath" ]				= $node->getElementsByTagName( "urlpath" )->item( 0 )->nodeValue;
			$data[ "visible" ]				= false;
			$data[ "clickable" ]			= false;

			if( self::$model->xPath->query( "clab:visible-for", $node )->length > 0 )
			{
				$data[ "visible" ] = ( self::$model->xPath->query( "clab:visible-for/clab:component[@clab:definition='" . $callerDefinition . "' and @clab:instance-name='" . $callerInstanceName . "']", $node )->length > 0 );
			}

			if( self::$model->xPath->query( "clab:clickable-for", $node )->length > 0 )
			{
				$data[ "clickable" ] = ( self::$model->xPath->query( "clab:clickable-for/clab:component[@clab:definition='" . $callerDefinition . "' and @clab:instance-name='" . $callerInstanceName . "']", $node )->length > 0 );
			}

			if( count( $childNodes ) )
			{
				$data[ "child_nodes" ] = $childNodes;
			}

			$hierarchy[] = $data;
		}

		return( $hierarchy );
	}
}

?>