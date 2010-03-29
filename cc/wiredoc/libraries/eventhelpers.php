<?php

namespace Module\CC;

use xMVC\Sys\Events\Event;
use xMVC\Sys\Delegate;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\FileSystem;

class EventHelpers
{
	public function __construct()
	{
	}

	protected function Listen( $eventName, Delegate $delegate )
	{
		CC::GetEventPump()->addEventListener( $eventName, $delegate );
	}

	protected function Talk( $sourceModel, Event &$event )
	{
		CC::GetEventPump()->dispatchEvent( new Event( "onComponentBuildComplete", array( "sourceModel" => $sourceModel, "data" => $event->arguments ) ) );
	}

	protected function GetCachedXMLModelDriver( $modelName, Event &$event, $updateCacheEveryXMinutes = 1440 )
	{
		$cacheFolder = "app/inc/cache/" .  $event->type . "/";
		$cacheFile = md5( $modelName . floor( time() / ( $updateCacheEveryXMinutes * 60 ) ) ) . ".xml";

		if( file_exists( $cacheFolder . $cacheFile ) )
		{
			$model = new XMLModelDriver( $cacheFolder . $cacheFile );
		}
		else
		{
			$model = new XMLModelDriver( $modelName );

			FileSystem::CreateFolderStructure( $cacheFolder );

			if( FileSystem::TestPermissions( $cacheFolder, FileSystem::FS_PERM_WRITE ) )
			{
				file_put_contents( $cacheFolder . $cacheFile, $model->saveXML() );
			}
		}

		return( $model );
	}
}

?>