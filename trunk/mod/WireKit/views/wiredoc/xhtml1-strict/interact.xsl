<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container group nav reference inject doc sitemap form interact" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:wirekit:component" xmlns:meta="urn:wirekit:meta" xmlns:container="urn:wirekit:container" xmlns:group="urn:wirekit:group" xmlns:nav="urn:wirekit:nav" xmlns:reference="urn:wirekit:reference" xmlns:inject="urn:wirekit:inject" xmlns:doc="urn:wirekit:doc" xmlns:sitemap="urn:wirekit:sitemap" xmlns:form="urn:wirekit:form" xmlns:interact="urn:wirekit:interact">

	<xsl:template match="interact:navigate">
		<xsl:if test="lang( $lang )">
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
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</a>
		</xsl:if>
	</xsl:template>

	<xsl:template match="interact:action">
		<xsl:if test="lang( $lang )">
			<button>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<span class="button">
					<xsl:apply-templates />
				</span>
			</button>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>