<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:str="http://www.xmvc.org/ns/str/1.0">

	<xsl:template name="footer">
		<p><xsl:value-of select="//str:copyright-notice" /></p>
	</xsl:template>

</xsl:stylesheet>