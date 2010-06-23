<xsl:stylesheet version="1.0"
	exclude-result-prefixes="wd"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0">

	<xsl:template match="wd:action">
		<button>
			<xsl:choose>
				<xsl:when test="@wd:handle">
					<xsl:attribute name="id"><xsl:value-of select="@wd:handle" /></xsl:attribute>
				</xsl:when>
				<xsl:when test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			<xsl:choose>
				<xsl:when test="@wd:name">
					<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			<span class="button">
				<xsl:apply-templates mode="lang-check" />
			</span>
		</button>
	</xsl:template>

</xsl:stylesheet>