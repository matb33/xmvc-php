<xsl:stylesheet version="1.0"
	exclude-result-prefixes="cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="http://www.xmvc.org/ns/cc/1.0">

	<xsl:template match="cc:navigation">
		<ul class="navigation layout">
			<xsl:for-each select="cc:item">
				<li><a href="{ cc:link }"><xsl:value-of select="cc:caption" /></a></li>
			</xsl:for-each>
		</ul>
	</xsl:template>

</xsl:stylesheet>