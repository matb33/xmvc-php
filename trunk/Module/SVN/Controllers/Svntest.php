<?php

namespace Module\SVN\Controllers;

use System\Libraries\Config;
use System\Libraries\Debug;

class Svntest
{
	private $svn;

	public function __construct()
	{
		$repositoryURL = "https://mcmillan.springloops.com/source/akimbo";
		$repositoryPath = "/trunk/web/app/models/";
		$repositoryUsername = "akimbo";
		$repositoryPassword = "n%9873h$25";
		$svnWorkingFolder = "mod/SVN/work/";

		$this->svn = new SVN( $repositoryURL, $repositoryPath, $repositoryUsername, $repositoryPassword, $svnWorkingFolder );
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