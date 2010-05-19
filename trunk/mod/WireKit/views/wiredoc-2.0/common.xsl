<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0">

	<xsl:include href="../common.xsl" />

	<!-- Global wiredoc templates for xMVC -->

	<xsl:template match="/xmvc:root">
		<xsl:apply-templates select="wd:component" />
	</xsl:template>

	<xsl:template match="wd:component">
		<xsl:if test="lang( $lang )">
			<xsl:apply-templates />
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>