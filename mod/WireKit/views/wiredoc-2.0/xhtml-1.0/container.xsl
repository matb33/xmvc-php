<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0">

	<xsl:template match="wd:*[ starts-with( local-name(), 'container' ) and @href and not( ../wd:group ) ]" priority="1">
		<iframe src="{ @href }" frameborder="0">
			<xsl:choose>
				<xsl:when test="@wd:name">
					<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
				</xsl:when>
				<xsl:when test="starts-with( local-name(), 'container.' )">
					<xsl:attribute name="class"><xsl:value-of select="substring( local-name(), 11 )" /></xsl:attribute>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</iframe>
	</xsl:template>

	<xsl:template match="wd:*[ starts-with( local-name(), 'container' ) and not( ../wd:group ) ]">
		<div>
			<xsl:choose>
				<xsl:when test="@wd:name">
					<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
				</xsl:when>
				<xsl:when test="starts-with( local-name(), 'container.' )">
					<xsl:attribute name="class"><xsl:value-of select="substring( local-name(), 11 )" /></xsl:attribute>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</div>
	</xsl:template>

</xsl:stylesheet>