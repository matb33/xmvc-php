<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container group nav reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:wirekit:component" xmlns:meta="urn:wirekit:meta" xmlns:container="urn:wirekit:container" xmlns:group="urn:wirekit:group" xmlns:nav="urn:wirekit:nav" xmlns:reference="urn:wirekit:reference" xmlns:inject="urn:wirekit:inject" xmlns:doc="urn:wirekit:doc" xmlns:sitemap="urn:wirekit:sitemap" xmlns:form="urn:wirekit:form">

	<xsl:template name="meta">
		<xsl:if test="//meta:title[ lang( $lang ) ]">
			<xsl:variable name="glue" select="//meta:title-options/@glue" />
			<xsl:variable name="sort-order" select="//meta:title-options/@sort-order" />
			<title>
				<xsl:for-each select="//meta:title[ lang( $lang ) ]">
					<xsl:sort select="position()" data-type="number" order="{ $sort-order }" />
					<xsl:value-of select="." />
					<xsl:if test="position() != last()">
						<xsl:value-of select="$glue" />
					</xsl:if>
				</xsl:for-each>
			</title>
		</xsl:if>
		<xsl:for-each select="//meta:*">
			<xsl:if test="lang( $lang )">
				<xsl:choose>
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