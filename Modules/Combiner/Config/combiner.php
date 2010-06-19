<?php

$combinerRewriteAdaptors = array( "|^[/]?(.+)/inc/(.*)|" => "./Modules/$1/inc/$2", "|^[/]?inc/(.*)$|"  => "./Application/inc/$1");
$combinerCacheWebFolder = "/inc/cache/";
$combinerCachePhysicalFolder = "./Application/inc/cache/";