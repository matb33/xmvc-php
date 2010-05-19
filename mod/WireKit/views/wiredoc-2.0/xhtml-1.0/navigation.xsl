<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0"
	xmlns:sitemap="urn:wirekit:sitemap">

	<xsl:template match="wd:navigation">
		<xsl:if test="lang( $lang )">
			<ul>
				<xsl:attribute name="class">
					<xsl:if test="@wd:name">
						<xsl:value-of select="@wd:name" /><xsl:text> </xsl:text>
					</xsl:if>
					<xsl:text>layout navigation</xsl:text>
				</xsl:attribute>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates select="*" />
			</ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="wd:navigation/wd:container">
		<xsl:if test="lang( $lang )">
			<xsl:variable name="this-uri" select=".//@href" />
			<li>
				<xsl:attribute name="class">
					<xsl:if test="@wd:name">
						<xsl:value-of select="@wd:name" /><xsl:text> </xsl:text>
					</xsl:if>
					<xsl:choose>
						<xsl:when test="last() = 1">first-child last-child</xsl:when>
						<xsl:when test="position() = 1">first-child</xsl:when>
						<xsl:when test="position() = last()">last-child</xsl:when>
						<xsl:otherwise>middle-child</xsl:otherwise>
					</xsl:choose>
					<xsl:text> item-</xsl:text><xsl:value-of select="position()" />
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="position() mod 2 = 1">even</xsl:when>
						<xsl:otherwise>odd</xsl:otherwise>
					</xsl:choose>
					<xsl:text> layout navigation</xsl:text>
					<xsl:if test="//sitemap:hierarchy/sitemap:path[ text() = $this-uri ]"><xsl:text> selected</xsl:text></xsl:if>
				</xsl:attribute>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</li>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>