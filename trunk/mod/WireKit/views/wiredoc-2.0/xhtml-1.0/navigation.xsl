<xsl:stylesheet version="1.0"
	exclude-result-prefixes="wd sitemap"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0"
	xmlns:sitemap="urn:wirekit:sitemap">

	<xsl:template match="wd:*[ starts-with( local-name(), 'navigation' ) ]">
		<ul>
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="@wd:name">
						<xsl:value-of select="@wd:name" /><xsl:text> </xsl:text>
					</xsl:when>
					<xsl:when test="starts-with( local-name(), 'navigation.' )">
						<xsl:value-of select="substring( local-name(), 12 )" /><xsl:text> </xsl:text>
					</xsl:when>
					<xsl:otherwise />
				</xsl:choose>
				<xsl:text>layout navigation</xsl:text>
			</xsl:attribute>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="*" mode="lang-check" />
		</ul>
	</xsl:template>

	<xsl:template match="wd:*[ starts-with( local-name(), 'container' ) and parent::wd:*[ starts-with( local-name(), 'navigation' ) ] ]">
		<xsl:param name="position" select="position()" />
		<xsl:param name="last" select="last()" />
		<xsl:variable name="this-uri" select=".//@href" />
		<li>
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="@wd:name">
						<xsl:value-of select="@wd:name" /><xsl:text> </xsl:text>
					</xsl:when>
					<xsl:when test="starts-with( local-name(), 'container.' )">
						<xsl:value-of select="substring( local-name(), 11 )" /><xsl:text> </xsl:text>
					</xsl:when>
					<xsl:otherwise />
				</xsl:choose>
				<xsl:choose>
					<xsl:when test="$last = 1">first-child last-child</xsl:when>
					<xsl:when test="$position = 1">first-child</xsl:when>
					<xsl:when test="$position = $last">last-child</xsl:when>
					<xsl:otherwise>middle-child</xsl:otherwise>
				</xsl:choose>
				<xsl:text> item-</xsl:text><xsl:value-of select="$position" />
				<xsl:text> </xsl:text>
				<xsl:choose>
					<xsl:when test="$position mod 2 = 1">even</xsl:when>
					<xsl:otherwise>odd</xsl:otherwise>
				</xsl:choose>
				<xsl:text> layout navigation</xsl:text>
				<xsl:if test="//sitemap:hierarchy/sitemap:path[ text() = $this-uri ]"><xsl:text> selected</xsl:text></xsl:if>
			</xsl:attribute>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates mode="lang-check" />
		</li>
	</xsl:template>

</xsl:stylesheet>