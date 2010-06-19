<?php

require_once( "libraries/AutoLoad.php" );
require_once( "libraries/Loader.php" );
require_once( "libraries/NamespaceMap.php" );
require_once( "libraries/Normalize.php" );

spl_autoload_extensions( ".php" );
spl_autoload_register( "spl_autoload" );
spl_autoload_register( "System\\Libraries\\AutoLoad::Callback" );