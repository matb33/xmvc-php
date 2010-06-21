<?php

namespace System\Drivers;

use System\Libraries\ModelDriver;
use System\Libraries\IModelDriver;
use System\Libraries\View;

class StringsModelDriver extends ModelDriver implements IModelDriver
{
	public function __construct()
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( View::namespaceXML, "xmvc:strings" );
		$this->appendChild( $this->rootElement );
	}

	public function add( $key, $value )
	{
		$this->transformForeignToXML( $key, $value );
	}

	public function transformForeignToXML()
	{
		$key = func_get_arg( 0 );
		$value = func_get_arg( 1 );

		if( is_array( $value ) )
		{
			$value = "|" . implode( "|", $value ) . "|";
		}

		$node = $this->createElementNS( View::namespaceXML, "xmvc:" . $key );
		$name = $this->createAttribute( "key" );
		$name->value = $key;
		$node->appendChild( $name );
		$data = $this->createCDATASection( ( string )$value );
		$node->appendChild( $data );
		$this->rootElement->appendChild( $node );

		parent::transformForeignToXML();
	}
}