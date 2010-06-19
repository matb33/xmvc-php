<?php

$combinerRewriteAdaptors = array( "|^(.+)/inc/(.*)|" => "./Module/$1/inc/$2", "|^[/]?inc/(.*)$|" => "./app/inc/$1" );

$combinerCacheWebFolder = "/inc/cache/";
$combinerCachePhysicalFolder = "./app/inc/cache/";