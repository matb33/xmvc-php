<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc clab"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:clab="http://clab.xmvc.org/ns/clab/1.0">

	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="yes"
	/>

	<xsl:include href="sys/views/error.xsl" />
	<xsl:include href="mod/contentlab/views/functions.xsl" />

	<?php

	if( isset( $definitions ) && is_array( $definitions ) )
	{
		foreach( $definitions as $definition )
		{
			?><xsl:include href="mod/contentlab/components/definitions/<?php echo( $definition ); ?>/view.xsl" />
			<?php
		}
	}

	?>

	<xsl:template match="/xmvc:root">
		<html xml:lang="en" lang="en">

			<head>

				<title><xsl:value-of select="//clab:head/clab:title" /></title>

				<meta http-equiv="content-type" content="text/html; charset=utf-8" />
				<meta name="robots" content="index, follow" />
				<meta name="generator" content="xMVC.org" />

				<xsl:for-each select="//clab:metatags/clab:metatag">
					<meta>
						<xsl:attribute name="name"><xsl:value-of select="clab:name" /></xsl:attribute>
						<xsl:attribute name="content"><xsl:value-of select="clab:content" /></xsl:attribute>
					</meta>
				</xsl:for-each>

				<xsl:call-template name="stylesheets" />
				<xsl:call-template name="scripts" />

			</head>

			<body>
				<xsl:apply-templates select="clab:instance" />
				<xsl:apply-templates select="xmvc:errors" />
			</body>

		</html>
	</xsl:template>

	<xsl:template match="//clab:metadata" />
	<xsl:template match="//clab:head" />

	<xsl:template name="instance-stylesheets">
		<xsl:for-each select="//clab:stylesheets/clab:stylesheet">
			<link>
				<xsl:attribute name="rel">stylesheet</xsl:attribute>
				<xsl:attribute name="type">text/css</xsl:attribute>
				<xsl:attribute name="href"><xsl:value-of select="clab:location" /></xsl:attribute>
			</link>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="instance-scripts">
		<xsl:for-each select="//clab:scripts/clab:script">
			<link>
				<xsl:attribute name="type">text/javascript</xsl:attribute>
				<xsl:attribute name="src"><xsl:value-of select="clab:location" /></xsl:attribute>
			</link>
		</xsl:for-each>
	</xsl:template>

</xsl:stylesheet>