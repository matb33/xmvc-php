<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="urn:cc:root">

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
				<xsl:apply-templates select="//cc:config" />
			</head>
			<body>
				<xsl:apply-templates select="*[ name() != 'cc:config' ]" />
			</body>
		</html>
	</xsl:template>

	<xsl:template match="cc:config">
		<xsl:for-each select="cc:title">
			<title><xsl:value-of select="." /></title>
		</xsl:for-each>
		<xsl:for-each select="cc:metatag">
			<meta name="{ @name }" content="{ @content }" />
		</xsl:for-each>
		<xsl:for-each select="cc:stylesheet">
			<link rel="stylesheet" type="text/css" media="{ @media }" href="{ @location }" />
		</xsl:for-each>
		<xsl:for-each select="cc:script">
			<script type="text/javascript" src="{ @location }" />
		</xsl:for-each>
		<xsl:for-each select="cc:icon">
			<link rel="icon" type="image/vnd.microsoft.icon" href="/{ @basename }.ico" />
			<link rel="icon" type="image/gif" href="/{ @basename }.gif" />
			<link rel="icon" type="image/png" href="/{ @basename }.png" />
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="xmvc:*" />

	<!-- Strip namespaces from XHTML using an identity template -->

	<xsl:template match="*[ @xhtml = '1' ]">
		<xsl:apply-templates select="node()" />
	</xsl:template>

	<xsl:template match="*[ @xhtml = '1' ]//*">
		<xsl:element name="{ local-name() }">
			<xsl:apply-templates select="@* | node()" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="*[ @xhtml = '1' ]//@*">
		<xsl:attribute name="{ local-name() }">
			<xsl:apply-templates />
		</xsl:attribute>
	</xsl:template>

</xsl:stylesheet>