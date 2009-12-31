<xsl:stylesheet version="1.0"
	exclude-result-prefixes="cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="http://www.xmvc.org/ns/cc/1.0">

	<xsl:include href="mod/cc/views/inside.xsl" />

	<xsl:template match="cc:standard">
		<div id="standard">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="cc:heading">
		<h1><xsl:value-of select="." /></h1>
	</xsl:template>

	<xsl:template match="cc:content">
		<xsl:apply-templates select="@* | node()" />
	</xsl:template>

	<xsl:template match="cc:content//*">
		<xsl:element name="{ local-name() }">
			<xsl:apply-templates select="@* | node()" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="cc:content//@*">
		<xsl:attribute name="{ local-name() }">
			<xsl:apply-templates />
		</xsl:attribute>
	</xsl:template>

</xsl:stylesheet>