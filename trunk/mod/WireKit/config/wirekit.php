<?php

$applicationClass = "";		// IMPORTANT: This must be set in app/config!
$componentNamespace = "";	// IMPORTANT: This must be set in app/config!

$componentCacheFilePattern = "app/cache/components/#type#/#name#/#hash#.txt";
$xliffFilePattern = "app/components/#component#/xliff/#instance#.#lang#.xliff";

$componentLookupFilePattern = "app/cache/components/lookup.xml";
$componentLookupCrawlFolder = "app/components/";
$componentLookupCrawlFolderRegExp = "/[^\.svn]/";
$componentLookupCrawlFileRegExp = "/.*\.xml|.*\.xsl/";

$wirekitNamespaces[ "component" ] = "urn:wirekit:component";
$wirekitNamespaces[ "meta" ] = "urn:wirekit:meta";
$wirekitNamespaces[ "container" ] = "urn:wirekit:container";
$wirekitNamespaces[ "group" ] = "urn:wirekit:group";
$wirekitNamespaces[ "nav" ] = "urn:wirekit:nav";
$wirekitNamespaces[ "reference" ] = "urn:wirekit:reference";
$wirekitNamespaces[ "inject" ] = "urn:wirekit:inject";
$wirekitNamespaces[ "doc" ] = "urn:wirekit:doc";
$wirekitNamespaces[ "sitemap" ] = "urn:wirekit:sitemap";
$wirekitNamespaces[ "loc" ] = "urn:wirekit:loc";
$wirekitNamespaces[ "lookup" ] = "urn:wirekit:lookup";
$wirekitNamespaces[ "form" ] = "urn:wirekit:form";
$wirekitNamespaces[ "interact" ] = "urn:wirekit:interact";

?>