<?php

$applicationClass = "";		// IMPORTANT: This must be set in Application/Config!
$componentNamespace = "";	// IMPORTANT: This must be set in Application/Config!

$defaultView = "Modules\\WebWiredoc\\Views\\wiredoc-2.0-xhtml-1.0";

$componentCacheFilePattern = "Application/Cache/components/#type#/#name#/#hash#.txt";
$xliffFilePattern = "Application/Components/#component#/xliff/#instance#.#lang#.xliff";

$componentLookupFilePattern = "Application/Cache/components/lookup.xml";
$componentLookupCrawlFolder = "Application/Components/";
$componentLookupCrawlFolderRegExp = "/[^\.svn]/";
$componentLookupCrawlFileRegExp = "/.*\.xml|.*\.xsl/";

// Deprecated
$wiredocNamespaces[ "component" ] = "urn:wirekit:component";
$wiredocNamespaces[ "container" ] = "urn:wirekit:container";
$wiredocNamespaces[ "group" ] = "urn:wirekit:group";
$wiredocNamespaces[ "nav" ] = "urn:wirekit:nav";
$wiredocNamespaces[ "reference" ] = "urn:wirekit:reference";
$wiredocNamespaces[ "inject" ] = "urn:wirekit:inject";
$wiredocNamespaces[ "loc" ] = "urn:wirekit:loc";
$wiredocNamespaces[ "form" ] = "urn:wirekit:form";
$wiredocNamespaces[ "interact" ] = "urn:wirekit:interact";

// Active
$wiredocNamespaces[ "lookup" ] = "urn:wirekit:lookup";
$wiredocNamespaces[ "sitemap" ] = "urn:wirekit:sitemap";
$wiredocNamespaces[ "wd" ] = "http://www.wiredoc.org/ns/wiredoc/2.0";
$wiredocNamespaces[ "meta" ] = "http://www.wiredoc.org/ns/metadoc/1.0";
$wiredocNamespaces[ "doc" ] = "http://www.docbook.org/schemas/simplified";