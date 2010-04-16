<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container group nav reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:cc:component" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:group="urn:cc:group" xmlns:nav="urn:cc:nav" xmlns:reference="urn:cc:reference" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap" xmlns:form="urn:cc:form">

	<xsl:template match="container:*[ @href and not( ../group:* ) ]" priority="1">
		<xsl:if test="lang( $lang )">
			<iframe src="{ @href }" frameborder="0">
				<xsl:if test="local-name() != 'container'">
					<xsl:attribute name="class"><xsl:value-of select="local-name()" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</iframe>
		</xsl:if>
	</xsl:template>

	<xsl:template match="container:*[ not( ../group:* ) ]">
		<xsl:if test="lang( $lang )">
			<div>
				<xsl:if test="local-name() != 'container'">
					<xsl:attribute name="class"><xsl:value-of select="local-name()" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@id">
					<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
				</xsl:if>
				<xsl:apply-templates />
			</div>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>