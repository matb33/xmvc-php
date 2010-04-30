<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container group nav reference inject doc sitemap form goto" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:wirekit:component" xmlns:meta="urn:wirekit:meta" xmlns:container="urn:wirekit:container" xmlns:group="urn:wirekit:group" xmlns:nav="urn:wirekit:nav" xmlns:reference="urn:wirekit:reference" xmlns:inject="urn:wirekit:inject" xmlns:doc="urn:wirekit:doc" xmlns:sitemap="urn:wirekit:sitemap" xmlns:form="urn:wirekit:form" xmlns:goto="urn:wirekit:goto">

	<xsl:template match="group:*">
		<xsl:if test="lang( $lang )">
			<ul>
				<xsl:attribute name="class">
					<xsl:if test="local-name() != 'group'">
						<xsl:value-of select="local-name()" /><xsl:text> </xsl:text>
					</xsl:if>
					<xsl:text>layout</xsl:text>
				</xsl:attribute>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates select="*" />
			</ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="group:*/container:*">
		<xsl:if test="lang( $lang )">
			<li>
				<xsl:attribute name="class">
					<xsl:if test="local-name() != 'container'">
						<xsl:value-of select="local-name()" /><xsl:text> </xsl:text>
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