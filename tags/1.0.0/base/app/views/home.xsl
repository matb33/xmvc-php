<xsl:stylesheet version="1.0"
	exclude-result-prefixes="str"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:str="http://[WEBSITE HOST]/ns/str/1.0">

	<xsl:include href="http://<?php echo( $_SERVER[ "HTTP_HOST" ] ); ?>/load/view/common/xhtml.xsl<?php echo( isset( $encodedData ) ? $encodedData : "" ); ?>" />
	<xsl:include href="http://<?php echo( $_SERVER[ "HTTP_HOST" ] ); ?>/load/view/header.xsl<?php echo( isset( $encodedData ) ? $encodedData : "" ); ?>" />
	<xsl:include href="http://<?php echo( $_SERVER[ "HTTP_HOST" ] ); ?>/load/view/footer.xsl<?php echo( isset( $encodedData ) ? $encodedData : "" ); ?>" />

	<xsl:template name="title">
		<title><xsl:value-of select="//str:title" /></title>
	</xsl:template>

	<xsl:template name="metatags">
	</xsl:template>

	<xsl:template name="css">
		<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/layout-home.css?<?php echo( md5( filemtime( realpath( "./inc/styles/layout-home.css" ) ) ) ); ?>" />
	</xsl:template>

	<xsl:template name="styles">
	</xsl:template>

	<xsl:template name="scripts">
	</xsl:template>

	<xsl:template name="body">

		<div id="page">

			<xsl:call-template name="header" />

			<xsl:call-template name="footer" />

		</div>

	</xsl:template>

	<xsl:template match="str:*" />

</xsl:stylesheet>