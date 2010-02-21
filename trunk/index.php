<?php

namespace xMVC\Sys;

require_once( "sys/bootstrap.php" );

NamespaceMap::Register( "/^xMVC::Sys::(.*)$/", "./sys/%f/%1" );
NamespaceMap::Register( "/^xMVC::App::(.*)$/", "./app/%f/%1" );
NamespaceMap::Register( "/^Module::(.*?)::(.*)$/", "./mod/%1/%f/%2" );

Config::Load( "./sys" );
Config::Load( "./app" );
Config::Load( "./mod/*" );

Core::Load();

?>