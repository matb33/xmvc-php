<?php

namespace xMVC\Mod\SVN;

use xMVC\Sys\Config;
use xMVC\Sys\Debug;

class Svntest
{
	private $svn;

	public function __construct()
	{
		$this->svn = new SVN( Config::$data[ "repositoryURL"], Config::$data[ "repositoryPath"], Config::$data[ "repositoryUsername"], Config::$data[ "repositoryPassword"] );
		$this->svn = new SVN( Config::$data[ "repositoryURL"], Config::$data[ "repositoryPath"], Config::$data[ "repositoryUsername"], Config::$data[ "repositoryPassword"] );
	}

	public function Index()
	{
		phpinfo();

		Debug::VarDump( Config::$data );
	}

	public function Update()
	{
		$this->svn->Update();
	}

	public function Checkout()
	{
		$this->svn->Checkout();
	}

	public function Lg()
	{
		Debug::Write( "Logging", $this );
		$this->svn->Lg();
	}
}