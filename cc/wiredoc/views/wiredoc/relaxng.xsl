<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:rng="http://relaxng.org/ns/structure/1.0"
	xmlns:component="urn:cc:component" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:xcontainer="urn:cc:xcontainer" xmlns:group="urn:cc:group" xmlns:reference="urn:cc:reference" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap">

	<xsl:output
		method="xml"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="no"
	/>

	<xsl:template match="rng:element">
		<xsl:element name="{ @name }" namespace="{ @ns }">
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>

	<xsl:template match="rng:attribute">
		<xsl:attribute name="{ @name }">
			<xsl:apply-templates />
		</xsl:attribute>
	</xsl:template>

	<xsl:template match="rng:zeroOrMore">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="rng:oneOrMore">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="rng:optional">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="rng:interleave">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="rng:choice">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="rng:group">
		<xsl:apply-templates />
	</xsl:template>

</xsl:stylesheet>