<?php

class StringsModelDriver extends ModelDriver
{
	private $rootElement;

	public function __construct()
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( xMVC::$namespace, "xmvc:strings" );
		$this->appendChild( $this->rootElement );
	}

	public function Add( $key, $value )
	{
		$node = $this->createElementNS( xMVC::$namespace, "xmvc:" . $key, ( string )$value );
		$this->rootElement->appendChild( $node );
	}
}

?>