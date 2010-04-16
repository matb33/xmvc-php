<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container group nav reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:cc:component" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:group="urn:cc:group" xmlns:nav="urn:cc:nav" xmlns:reference="urn:cc:reference" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap" xmlns:form="urn:cc:form">

	<xsl:template name="meta">
		<xsl:for-each select="//meta:*">
			<xsl:if test="lang( $lang )">
				<xsl:choose>
					<xsl:when test="local-name() = 'title'">
						<title><xsl:value-of select="." /></title>
					</xsl:when>
					<xsl:when test="local-name() = 'link'">
						<xsl:element name="link">
							<xsl:copy-of select="@*" />
						</xsl:element>
					</xsl:when>
					<xsl:when test="local-name() = 'meta'">
						<xsl:element name="meta">
							<xsl:copy-of select="@*" />
						</xsl:element>
					</xsl:when>
					<xsl:when test="local-name() = 'script'">
						<xsl:element name="script">
							<xsl:copy-of select="@*" />
							<xsl:comment>
								<xsl:apply-templates />
							</xsl:comment>
						</xsl:element>
					</xsl:when>
					<xsl:when test="local-name() = 'style'">
						<xsl:element name="style">
							<xsl:copy-of select="@*" />
							<xsl:comment>
								<xsl:apply-templates />
							</xsl:comment>
						</xsl:element>
					</xsl:when>
					<xsl:otherwise />
				</xsl:choose>
			</xsl:if>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="meta:*" />

</xsl:stylesheet>