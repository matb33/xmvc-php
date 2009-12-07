<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:str="http://www.xmvc.org/ns/str/1.0">

	<xsl:template name="header">
		<img src="{ //str:logo-base64encoded }" alt="{ //str:logo-caption }" />
		<ul>
			<xsl:for-each select="//str:navigation/str:items/str:item">
				<li>
					<a>
						<xsl:attribute name="href"><xsl:value-of select="str:link" /></xsl:attribute>
						<xsl:value-of select="str:caption" />
					</a>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>

</xsl:stylesheet>