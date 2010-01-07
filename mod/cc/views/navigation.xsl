<xsl:stylesheet version="1.0"
	exclude-result-prefixes="cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="urn:cc:root">

	<xsl:template match="cc:navigation">
		<ul class="navigation layout">
			<xsl:apply-templates select="cc:item" />
		</ul>
	</xsl:template>

	<xsl:template match="cc:item">
		<li>
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="last() = 1">first-child last-child</xsl:when>
					<xsl:when test="position() = 1">first-child</xsl:when>
					<xsl:when test="position() = last()">last-child</xsl:when>
					<xsl:otherwise>middle-child</xsl:otherwise>
				</xsl:choose> item-<xsl:value-of select="position()" />
			</xsl:attribute>
			<a href="{ cc:link }"><xsl:value-of select="cc:caption" /></a>
			<xsl:apply-templates select="cc:navigation" />
		</li>
	</xsl:template>

</xsl:stylesheet>