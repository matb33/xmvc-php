<?php

namespace Modules\SVN\Controllers;

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

	public function index()
	{
		phpinfo();

		Debug::varDump( Config::$data );
	}

	public function update()
	{
		$this->svn->update();
	}

	public function checkout()
	{
		$this->svn->checkout();
	}

	public function lg()
	{
		Debug::write( "Logging", $this );
		$this->svn->lg();
	}
}