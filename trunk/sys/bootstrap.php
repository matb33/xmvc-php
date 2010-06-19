<?php

require_once( "sys/libraries/AutoLoad.php" );
require_once( "sys/libraries/Loader.php" );
require_once( "sys/libraries/NamespaceMap.php" );
require_once( "sys/libraries/Normalize.php" );

spl_autoload_extensions( ".php" );
spl_autoload_register( "spl_autoload" );
spl_autoload_register( "xMVC\\Sys\\AutoLoad::Callback" );