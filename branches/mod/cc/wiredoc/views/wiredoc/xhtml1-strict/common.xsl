<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc instance meta container group child inject doc sitemap" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:instance="urn:cc:instance" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:group="urn:cc:group" xmlns:child="urn:cc:child" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap">

	<!-- XHTML 1.0 Strict wiredoc templates -->

	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="yes"
	/>

	<xsl:include href="../common.xsl" />
	<xsl:include href="doc.xsl" />
	<xsl:include href="container.xsl" />
	<xsl:include href="group.xsl" />
	<xsl:include href="form.xsl" />

	<xsl:variable name="lang">
		<xsl:value-of select="//instance:*[ not( ancestor::instance:* ) ]/@xml:lang" />
	</xsl:variable>

	<xsl:template match="instance:*[ not( ancestor::instance:* ) ]">
		<xsl:if test="lang( $lang )">
			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{ $lang }" lang="{ $lang }">
				<xsl:apply-templates />
			</html>
		</xsl:if>
	</xsl:template>

	<xsl:template match="meta:head">
		<xsl:if test="lang( $lang )">
			<head>
				<xsl:apply-templates />
			</head>
		</xsl:if>
	</xsl:template>

	<xsl:template match="meta:title">
		<xsl:if test="lang( $lang )">
			<title><xsl:value-of select="." /></title>
		</xsl:if>
	</xsl:template>

	<xsl:template match="meta:href" />

	<xsl:template match="instance:*[ preceding-sibling::meta:head ]">
		<body>
			<xsl:apply-templates />
		</body>
	</xsl:template>

</xsl:stylesheet>