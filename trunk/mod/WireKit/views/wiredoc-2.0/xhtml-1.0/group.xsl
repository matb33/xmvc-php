<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0">

	<xsl:template match="wd:*[ starts-with( local-name(), 'group' ) ]">
		<xsl:if test="lang( $lang )">
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
					<xsl:text>layout</xsl:text>
				</xsl:attribute>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates select="*" />
			</ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="wd:*[ starts-with( local-name(), 'group' ) ]/wd:*[ starts-with( local-name(), 'container' ) ]">
		<xsl:if test="lang( $lang )">
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
					<xsl:text> layout</xsl:text>
				</xsl:attribute>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</li>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>