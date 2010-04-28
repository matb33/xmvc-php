<?php

namespace xMVC\Sys;

class StringsModelDriver extends ModelDriver implements IModelDriver
{
	public function __construct()
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Core::namespaceXML, "xmvc:strings" );
		$this->appendChild( $this->rootElement );
	}

	public function Add( $key, $value )
	{
		$this->TransformForeignToXML( $key, $value );
	}

	public function TransformForeignToXML()
	{
		$key = func_get_arg( 0 );
		$value = func_get_arg( 1 );

		if( is_array( $value ) )
		{
			$value = "|" . implode( "|", $value ) . "|";
		}

		$node = $this->createElementNS( Core::namespaceXML, "xmvc:" . $key );
		$name = $this->createAttribute( "key" );
		$name->value = $key;
		$node->appendChild( $name );
		$data = $this->createCDATASection( ( string )$value );
		$node->appendChild( $data );
		$this->rootElement->appendChild( $node );

		parent::TransformForeignToXML();
	}
}

?>