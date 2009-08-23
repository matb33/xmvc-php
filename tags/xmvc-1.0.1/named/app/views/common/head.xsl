<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template name="commonmetatags">
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="robots" content="index, follow" />
		<meta name="generator" content="xMVC.org" />
	</xsl:template>

	<xsl:template name="commoncss">
		<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/reset-0.0.1.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/common.css?<?php echo( md5( filemtime( realpath( "./inc/styles/common.css" ) ) ) ); ?>" />
		<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/typography.css?<?php echo( md5( filemtime( realpath( "./inc/styles/typography.css" ) ) ) ); ?>" />
		<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/layout-common.css?<?php echo( md5( filemtime( realpath( "./inc/styles/layout-common.css" ) ) ) ); ?>" />
		<link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />
		<link rel="icon" type="image/gif" href="/favicon.gif" />
		<link rel="icon" type="image/png" href="/favicon.png" />
	</xsl:template>

	<xsl:template name="commonscripts">
		<script type="text/javascript" src="/inc/scripts/jquery-1.3.2.min.js"></script>
		<script type="text/javascript" src="/inc/scripts/common-0.0.1.js"></script>
		<script type="text/javascript" src="/inc/scripts/lightbox-0.2.0.js"></script>
		<script type="text/javascript" src="/inc/scripts/validate-0.1.0.js"></script>
		<script type="text/javascript" src="/inc/scripts/common.js?<?php echo( md5( filemtime( realpath( "./inc/scripts/common.js" ) ) ) ); ?>"></script>
	</xsl:template>

	<xsl:template name="commonstyles">
	</xsl:template>

</xsl:stylesheet>