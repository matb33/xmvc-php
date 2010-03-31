<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\ModelDriver;
use xMVC\Sys\ModelDriverInterface;
use xMVC\Sys\Core;
use xMVC\Sys\Loader;
use xMVC\Sys\Config;
use xMVC\Sys\XMLModelDriver;

class EditableInstancesModelDriver extends ModelDriver implements ModelDriverInterface
{
	public function __construct()
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Config::$data[ "ccNamespace" ], "cc:editable-instances" );
		$this->appendChild( $this->rootElement );

		$this->TransformForeignToXML();
	}

	public function TransformForeignToXML()
	{
		foreach( glob( "app/views/*.edit.xsl" ) as $container )
		{
			$filename = basename( $container );
			$containerName = str_replace( ".edit.xsl", "", $filename );

			$containerNode = $this->createElementNS( Config::$data[ "ccNamespace" ], "cc:container" );
			$nameAttribute = $this->createAttribute( "name" );
			$nameAttribute->value = $containerName;
			$containerNode->appendChild( $nameAttribute );
			$this->rootElement->appendChild( $containerNode );

			foreach( glob( Config::$data[ "svnWorkingFolder" ] . $containerName . "/*.ccx" ) as $ccxFile )
			{
				$node = $this->createElementNS( Config::$data[ "ccNamespace" ], "cc:instance" );
				$nameAttribute = $this->createAttribute( "name" );
				$nameAttribute->value = basename( $ccxFile, ".ccx" );
				$node->appendChild( $nameAttribute );
				$containerNode->appendChild( $node );
			}
		}

		parent::TransformForeignToXML();
	}
}

?>