<?php

namespace Module\CC;

use xMVC\Sys\Routing;
use xMVC\Sys\Config;

use Module\SVN\SVN;

class Ccms_root
{
	private $svn;

	public function __construct()
	{
		$this->svn = new SVN( Config::$data[ "repositoryURL"], Config::$data[ "repositoryPath"], Config::$data[ "repositoryUsername"], Config::$data[ "repositoryPassword"], Config::$data[ "svnWorkingFolder"] );

		if( !$this->svn->IsCheckedOut() )
		{
			$this->svn->Checkout();
		}
		else
		{
			return;

			$output = $this->svn->Update();

			preg_match_all( "/C  (.*?)\n/", $output, $matches, PREG_PATTERN_ORDER );

			if( count( $matches[ 1 ] ) )
			{
				foreach( $matches[ 1 ] as $conflictedFile )
				{
					$this->svn->Revert( $conflictedFile );
					$this->svn->Update( "HEAD", $conflictedFile );
				}
			}
		}
	}
}

?>