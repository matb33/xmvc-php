<?php

namespace Module\ContentLAB;

use xMVC\Sys\ModelDriver;
use xMVC\Sys\ModelDriverInterface;

class DefinitionModelDriver extends ModelDriver implements ModelDriverInterface
{
	public function __construct( $definitions )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( "http://clab.xmvc.org/ns/clab/1.0", "clab:load-definitions" );
		$this->appendChild( $this->rootElement );

		$this->TransformForeignToXML( $definitions );
	}

	public function TransformForeignToXML()
	{
		$definitions = func_get_arg( 0 );

		foreach( $definitions as $definition )
		{
			$node = $this->createElementNS( "http://clab.xmvc.org/ns/clab/1.0", "clab:definition", ( string )$definition );
			$this->rootElement->appendChild( $node );
		}

		parent::TransformForeignToXML();
	}
}

?>