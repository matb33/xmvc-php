<?php

$combinerRewriteAdaptors = array( "|^[/]?(.+)/inc/(.*)|" => "./Modules/$1/Public/$2", "|^[/]?inc/(.*)$|"  => "./Application/Public/$1");
$combinerCacheWebFolder = "/inc/cache/";
$combinerCachePhysicalFolder = "./Application/Public/cache/";