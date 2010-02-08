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

	<xsl:include href="common.xsl" />

	<xsl:template match="/xmvc:root">
		<xsl:apply-templates select="cc:root/cc:xhtml1-strict" />
	</xsl:template>

	<xsl:template match="cc:xhtml1-strict">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{ //xmvc:lang }" lang="{ //xmvc:lang }">
			<head>
				<xsl:if test="cc:title">
					<title><xsl:value-of select="cc:title[ @lang = //xmvc:lang ]" /></title>
				</xsl:if>
				<xsl:apply-templates select="//cc:config" />
			</head>
			<body>
				<xsl:apply-templates />
			</body>
		</html>
	</xsl:template>

	<xsl:template match="cc:config">
		<xsl:for-each select="cc:metatag">
			<meta>
				<xsl:if test="@name">
					<xsl:attribute name="name"><xsl:value-of select="@name" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@http-equiv">
					<xsl:attribute name="http-equiv"><xsl:value-of select="@http-equiv" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@content">
					<xsl:attribute name="content"><xsl:value-of select="@content" /></xsl:attribute>
				</xsl:if>
			</meta>
		</xsl:for-each>
		<xsl:for-each select="cc:stylesheet">
			<link rel="stylesheet" type="text/css" media="{ @media }" href="{ @location }?{ //xmvc:random-md5 }" />
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

	<xsl:template match="cc:title" />

</xsl:stylesheet>