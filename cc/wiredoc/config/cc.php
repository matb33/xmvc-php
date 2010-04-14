<?php

$componentCacheFilePattern = "app/inc/cache/#type#/#name#/#hash#.txt";
$componentFilePattern = "app/components/#component#/#component-only#.xsl";
$componentInstanceFilePattern = "app/components/#component#/#instance#.xml";
$xliffFilePattern = "app/components/#component#/xliff/#instance#.#lang#.xliff";

$ccNamespaces[ "component" ] = "urn:cc:component";
$ccNamespaces[ "meta" ] = "urn:cc:meta";
$ccNamespaces[ "container" ] = "urn:cc:container";
$ccNamespaces[ "group" ] = "urn:cc:group";
$ccNamespaces[ "nav" ] = "urn:cc:nav";
$ccNamespaces[ "reference" ] = "urn:cc:reference";
$ccNamespaces[ "inject" ] = "urn:cc:inject";
$ccNamespaces[ "doc" ] = "urn:cc:doc";
$ccNamespaces[ "sitemap" ] = "urn:cc:sitemap";
$ccNamespaces[ "loc" ] = "urn:cc:loc";

?>