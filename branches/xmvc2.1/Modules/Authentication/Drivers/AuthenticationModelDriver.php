<?php

namespace Modules\Authentication\Drivers;

use System\Libraries\View;
use System\Libraries\ModelDriver;
use System\Libraries\IModelDriver;
use System\Libraries\Config;
use Modules\Authentication\Libraries\Authenticator;

class AuthenticationModelDriver extends ModelDriver implements IModelDriver
{
	public function __construct( $username, $password )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( View::namespaceXML, "xmvc:authentication" );
		$this->appendChild( $this->rootElement );

		$this->transformForeignToXML( $username, $password );
	}

	public function transformForeignToXML()
	{
		$username = func_get_arg( 0 );
		$password = func_get_arg( 1 );

		if( strlen( $username ) == 0 && strlen( $password ) == 0 )
		{
			$state = "neutral";
		}
		else
		{
			$success = Authenticator::authenticate( $username, $password );

			if( $success )
			{
				$state = "success";
			}
			else
			{
				$state = "fail";
			}
		}

		$node = $this->createElementNS( View::namespaceXML, "xmvc:state" );
		$data = $this->createCDATASection( ( string )$state );
		$node->appendChild( $data );
		$this->rootElement->appendChild( $node );

		$node = $this->createElementNS( View::namespaceXML, "xmvc:username" );
		$data = $this->createCDATASection( ( string )$username );
		$node->appendChild( $data );
		$this->rootElement->appendChild( $node );

		parent::transformForeignToXML();
	}
}