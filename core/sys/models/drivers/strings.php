<?php

class StringsModelDriver extends ModelDriver
{
	private $rootElement;

	public function StringsModelDriver()
	{
		parent::ModelDriver();

		$this->rootElement = $this->createElementNS( "http://www.xmvc.org/ns/xmvc/1.0", "xmvc:strings" );
		$this->appendChild( $this->rootElement );
	}

	public function Add( $key, $value )
	{
		$node = $this->createElementNS( "http://www.xmvc.org/ns/xmvc/1.0", "xmvc:" . $key, ( string )$value );

		$this->rootElement->appendChild( $node );
	}
}

?>