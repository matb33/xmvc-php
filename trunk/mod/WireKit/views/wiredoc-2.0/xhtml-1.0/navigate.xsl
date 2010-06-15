<xsl:stylesheet version="1.0"
	exclude-result-prefixes="wd"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0">

	<xsl:template match="wd:*[ starts-with( local-name(), 'navigate' ) ]">
		<a>
			<xsl:attribute name="href">
				<xsl:choose>
					<xsl:when test="@url"><xsl:value-of select="@url" /></xsl:when>
					<xsl:otherwise><xsl:value-of select="@href" /></xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:choose>
				<xsl:when test="@rel">
					<xsl:attribute name="rel"><xsl:value-of select="@rel" /></xsl:attribute>
				</xsl:when>
				<xsl:when test="@target">
					<xsl:attribute name="rel"><xsl:value-of select="@target" /></xsl:attribute>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			<xsl:choose>
				<xsl:when test="@wd:name">
					<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
				</xsl:when>
				<xsl:when test="starts-with( local-name(), 'navigate.' )">
					<xsl:attribute name="class"><xsl:value-of select="substring( local-name(), 10 )" /></xsl:attribute>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:choose>
				<xsl:when test="@wd:description">
					<xsl:attribute name="title"><xsl:value-of select="@wd:description" /></xsl:attribute>
				</xsl:when>
				<xsl:when test="wd:description">
					<xsl:attribute name="title"><xsl:value-of select="wd:description" /></xsl:attribute>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			<xsl:apply-templates mode="lang-check" />
		</a>
	</xsl:template>

	<xsl:template match="wd:description" />

</xsl:stylesheet>