<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container xcontainer group nav reference inject doc sitemap form s" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:component="urn:cc:component" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:xcontainer="urn:cc:xcontainer" xmlns:group="urn:cc:group" xmlns:nav="urn:cc:nav" xmlns:reference="urn:cc:reference" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap" xmlns:form="urn:cc:form">

	<xsl:template match="nav:*">
		<xsl:if test="lang( $lang )">
			<ul>
				<xsl:attribute name="class">
					<xsl:if test="local-name() != 'nav'">
						<xsl:value-of select="local-name()" /><xsl:text> </xsl:text>
					</xsl:if>
					<xsl:text>layout</xsl:text>
				</xsl:attribute>
				<xsl:apply-templates select="*" />
			</ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="nav:*/container:*">
		<xsl:if test="lang( $lang )">
			<xsl:variable name="current-uri" select="//xmvc:strings/xmvc:uri" />
			<xsl:variable name="current-path" select="concat( //s:url/sitemap:component[ ../sitemap:path = $current-uri ], '/', //s:url/sitemap:instance-name[ ../sitemap:path = $current-uri ] )" />
			<xsl:variable name="current-parent" select="//s:url/sitemap:parent[ ../sitemap:path = $current-uri ]" />
			<xsl:variable name="this-uri" select="doc:ulink/@href" />
			<xsl:variable name="this-path" select="concat( //s:url/sitemap:component[ ../sitemap:path = $this-uri ], '/', //s:url/sitemap:instance-name[ ../sitemap:path = $this-uri ] )" />
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
					<xsl:if test="$this-path = $current-path or $this-path = $current-parent"><xsl:text> selected</xsl:text></xsl:if>
				</xsl:attribute>
				<xsl:apply-templates />
			</li>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>