<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc instance meta container group child inject doc sitemap" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:instance="urn:cc:instance" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:group="urn:cc:group" xmlns:child="urn:cc:child" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap">

	<xsl:include href="../common.xsl" />

	<!-- Global wiredoc templates for xMVC -->

	<xsl:template match="/xmvc:root">
		<xsl:apply-templates select="instance:*" />
	</xsl:template>

	<xsl:template match="instance:*">
		<xsl:if test="lang( $lang )">
			<xsl:apply-templates />
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>