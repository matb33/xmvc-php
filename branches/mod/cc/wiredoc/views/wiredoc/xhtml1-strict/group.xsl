<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc instance meta container xcontainer group reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:instance="urn:cc:instance" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:xcontainer="urn:cc:xcontainer" xmlns:group="urn:cc:group" xmlns:reference="urn:cc:reference" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap" xmlns:form="urn:cc:form">

	<xsl:template match="group:*">
		<xsl:if test="lang( $lang )">
			<ul class="layout">
				<xsl:if test="local-name() != 'group'">
					<xsl:attribute name="id"><xsl:value-of select="local-name()" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates select="*" />
			</ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="group:*/container:*">
		<xsl:if test="lang( $lang )">
			<li>
				<xsl:if test="local-name() != 'container'">
					<xsl:attribute name="id"><xsl:value-of select="local-name()" /></xsl:attribute>
				</xsl:if>
				<xsl:attribute name="class">
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
				<xsl:apply-templates />
			</li>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>