<?php

namespace Module\Authentication;

use System\Libraries\Core;
use System\Libraries\ModelDriver;
use System\Libraries\IModelDriver;
use System\Libraries\Config;

class AuthenticationModelDriver extends ModelDriver implements IModelDriver
{
	public function __construct( $username, $password )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Core::namespaceXML, "xmvc:authentication" );
		$this->appendChild( $this->rootElement );

		$this->TransformForeignToXML( $username, $password );
	}

	public function TransformForeignToXML()
	{
		$username = func_get_arg( 0 );
		$password = func_get_arg( 1 );

		if( strlen( $username ) == 0 && strlen( $password ) == 0 )
		{
			$state = "neutral";
		}
		else
		{
			$success = Authenticator::Authenticate( $username, $password );

			if( $success )
			{
				$state = "success";
			}
			else
			{
				$state = "fail";
			}
		}

		$node = $this->createElementNS( Core::namespaceXML, "xmvc:state" );
		$data = $this->createCDATASection( ( string )$state );
		$node->appendChild( $data );
		$this->rootElement->appendChild( $node );

		$node = $this->createElementNS( Core::namespaceXML, "xmvc:username" );
		$data = $this->createCDATASection( ( string )$username );
		$node->appendChild( $data );
		$this->rootElement->appendChild( $node );

		parent::TransformForeignToXML();
	}
}