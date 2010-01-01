<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="http://www.xmvc.org/ns/cc/1.0">

	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="yes"
	/>

	<xsl:include href="../../../sys/views/error.xsl" />

	<xsl:template match="/xmvc:root">
		<xsl:apply-templates select="//cc:xhtml1-strict" />
	</xsl:template>

	<xsl:template match="cc:xhtml1-strict">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{ //xmvc:strings/xmvc:lang }" lang="{ //xmvc:strings/xmvc:lang }">
			<head>
				<xsl:apply-templates select="//cc:head" />
			</head>
			<body>
				<xsl:apply-templates select="*[ name() != 'cc:head' ]" />
			</body>
		</html>
	</xsl:template>

	<xsl:template match="cc:head">
		<xsl:for-each select="cc:title">
			<title><xsl:value-of select="." /></title>
		</xsl:for-each>
		<xsl:for-each select="cc:metatag">
			<meta name="{ @cc:name }" content="{ @cc:content }" />
		</xsl:for-each>
		<xsl:for-each select="cc:stylesheet">
			<link rel="stylesheet" type="text/css" href="{ @cc:location }" />
		</xsl:for-each>
		<xsl:for-each select="cc:script">
			<script type="text/javascript" src="{ @cc:location }" />
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="xmvc:*" />

	<!-- Strip namespaces from XHTML using an identity template -->

	<xsl:template match="*[ @cc:xhtml = '1' ]">
		<xsl:apply-templates select="node()" />
	</xsl:template>

	<xsl:template match="*[ @cc:xhtml = '1' ]//*">
		<xsl:element name="{ local-name() }">
			<xsl:apply-templates select="@* | node()" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="*[ @cc:xhtml = '1' ]//@*">
		<xsl:attribute name="{ local-name() }">
			<xsl:apply-templates />
		</xsl:attribute>
	</xsl:template>

</xsl:stylesheet>