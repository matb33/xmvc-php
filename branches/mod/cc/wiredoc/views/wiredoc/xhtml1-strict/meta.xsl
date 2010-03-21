<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc instance meta container group reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:instance="urn:cc:instance" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:group="urn:cc:group" xmlns:reference="urn:cc:reference" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap" xmlns:form="urn:cc:form">

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

	<xsl:template match="meta:link">
		<xsl:element name="link">
			<xsl:copy-of select="@*" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="meta:meta">
		<xsl:element name="meta">
			<xsl:copy-of select="@*" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="meta:script">
		<xsl:element name="script">
			<xsl:copy-of select="@*" />
			<xsl:comment>
				<xsl:apply-templates />
			</xsl:comment>
		</xsl:element>
	</xsl:template>

	<xsl:template match="meta:href" />
	<xsl:template match="meta:view" />

</xsl:stylesheet>