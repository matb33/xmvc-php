<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0">

	<xsl:template match="wd:*[ starts-with( local-name(), 'action' ) ]">
		<xsl:if test="lang( $lang )">
			<button>
				<xsl:choose>
					<xsl:when test="@handle">
						<xsl:attribute name="id"><xsl:value-of select="@handle" /></xsl:attribute>
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
					<xsl:when test="starts-with( local-name(), 'action.' )">
						<xsl:attribute name="class"><xsl:value-of select="substring( local-name(), 8 )" /></xsl:attribute>
					</xsl:when>
					<xsl:otherwise />
				</xsl:choose>
				<span class="button">
					<xsl:apply-templates />
				</span>
			</button>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>