<xsl:stylesheet version="1.0"
	exclude-result-prefixes="wd"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0">

	<xsl:template match="wd:container[ @href and not( parent::wd:group ) ]" priority="1">
		<iframe src="{ @href }" frameborder="0">
			<xsl:choose>
				<xsl:when test="@wd:name">
					<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</iframe>
	</xsl:template>

	<xsl:template match="wd:container[ not( parent::wd:group ) ]">
		<div>
			<xsl:choose>
				<xsl:when test="@wd:name">
					<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
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