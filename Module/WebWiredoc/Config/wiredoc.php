<?php

$applicationClass = "";		// IMPORTANT: This must be set in app/config!
$componentNamespace = "";	// IMPORTANT: This must be set in app/config!

$defaultView = "Module\\WebWiredoc\\Views\\wiredoc-2.0-xhtml-1.0";

$componentCacheFilePattern = "app/cache/components/#type#/#name#/#hash#.txt";
$xliffFilePattern = "app/components/#component#/xliff/#instance#.#lang#.xliff";

$componentLookupFilePattern = "app/cache/components/lookup.xml";
$componentLookupCrawlFolder = "app/components/";
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