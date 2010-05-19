<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0">

	<xsl:template match="wd:container[ @href and not( ../wd:group ) ]" priority="1">
		<xsl:if test="lang( $lang )">
			<iframe src="{ @href }" frameborder="0">
				<xsl:if test="@wd:name">
					<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</iframe>
		</xsl:if>
	</xsl:template>

	<xsl:template match="wd:container[ not( ../wd:group ) ]">
		<xsl:if test="lang( $lang )">
			<div>
				<xsl:if test="@wd:name">
					<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</div>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>