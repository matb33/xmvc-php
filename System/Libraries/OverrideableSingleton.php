<?php

namespace System\Libraries;

// Original author of Singleton class: Quentin Berlemont <quentinberlemont@gmail.com>, found in the PHP documentation online

abstract class OverrideableSingleton
{
	static $overrides = array();

	protected function __construct() {}
	final private function __clone() {}

	final static public function override( $override )
	{
		$calledClass = get_called_class();

		if( is_subclass_of( $override, $calledClass ) )
		{
			self::$overrides[ $calledClass ] = $override;
		}
	}

	final static public function getInstance()
	{
		static $instance = null;

		if( $instance )
		{
			return $instance;
		}
		else
		{
			$calledClass = get_called_class();

			if( isset( self::$overrides[ $calledClass ] ) )
			{
				return $instance = new self::$overrides[ $calledClass ];
			}
			else
			{
				return $instance = new static;
			}
		}
	}
}

?>