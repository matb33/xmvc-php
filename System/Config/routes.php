<?php

$defaultController = "";
$useQueryInRoutes = false;

$priorityRoutes[ "/^\/load(.*)/" ] = "System\\Controllers\\Load%1";