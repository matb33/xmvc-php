<xsl:stylesheet version="1.0"
	exclude-result-prefixes="str"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:str="http://www.xmvc.org/ns/str/1.0">

	<xsl:include href="common/xhtml.xsl" />
	<xsl:include href="header.xsl" />
	<xsl:include href="footer.xsl" />

	<xsl:template name="title">
		<title><xsl:value-of select="//str:title" /></title>
	</xsl:template>

	<xsl:template name="metatags">
	</xsl:template>

	<xsl:template name="css">
	</xsl:template>

	<xsl:template name="styles">
	</xsl:template>

	<xsl:template name="scripts">
	</xsl:template>

	<xsl:template name="body">

		<div id="page">
			<xsl:call-template name="header" />

			<p><xsl:value-of select="//str:access-granted" /> [<a href="logout/"><xsl:value-of select="//xmvc:strings/xmvc:logged-in-user" /></a>]</p>

			<xsl:call-template name="footer" />
		</div>

	</xsl:template>

	<xsl:template match="str:*" />
	<xsl:template match="xmvc:*" />

</xsl:stylesheet>