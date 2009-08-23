<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0">

	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="yes"
	/>

	<xsl:include href="http://<?php echo( $_SERVER[ "HTTP_HOST" ] ); ?>/load/view/error.xsl<?php echo( isset( $encodedData ) ? $encodedData : "" ); ?>" />
	<xsl:include href="http://<?php echo( $_SERVER[ "HTTP_HOST" ] ); ?>/load/view/common/functions.xsl<?php echo( isset( $encodedData ) ? $encodedData : "" ); ?>" />
	<xsl:include href="http://<?php echo( $_SERVER[ "HTTP_HOST" ] ); ?>/load/view/common/head.xsl<?php echo( isset( $encodedData ) ? $encodedData : "" ); ?>" />

	<xsl:template match="/xmvc:root">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo( "en" ); ?>" lang="<?php echo( "en" ); ?>">

			<head>
				<xsl:call-template name="title" />
				<xsl:call-template name="commonmetatags" />
				<xsl:call-template name="metatags" />
				<xsl:call-template name="commoncss" />
				<xsl:call-template name="css" />
				<xsl:call-template name="commonscripts" />
				<xsl:call-template name="scripts" />
				<xsl:call-template name="commonstyles" />
				<xsl:call-template name="styles" />
			</head>

			<body>
				<xsl:call-template name="body" />
				<xsl:apply-templates />
			</body>

		</html>
	</xsl:template>

</xsl:stylesheet>