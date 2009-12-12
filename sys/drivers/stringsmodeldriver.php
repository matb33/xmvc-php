<?php

namespace xMVC;

class StringsModelDriver extends ModelDriver implements ModelDriverInterface
{
	public function __construct()
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Core::$namespace, "xmvc:strings" );
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

		$node = $this->createElementNS( Core::$namespace, "xmvc:" . $key, ( string )$value );
		$this->rootElement->appendChild( $node );

		parent::TransformForeignToXML();
	}
}

?>