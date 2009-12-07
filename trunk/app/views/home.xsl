<xsl:stylesheet version="1.0"
	exclude-result-prefixes="str"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:str="http://www.xmvc.org/ns/str/1.0">

	<xsl:include href="app/views/common/xhtml.xsl" />
	<xsl:include href="app/views/header.xsl" />
	<xsl:include href="app/views/footer.xsl" />

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

			<div id="intro">
				<xsl:call-template name="copy-of"><xsl:with-param name="select" select="//str:intro" /></xsl:call-template>
			</div>

			<ul id="search-engines">
				<xsl:for-each select="//str:search-engines/str:search-engine">
					<li>
						<a>
							<xsl:attribute name="href"><xsl:value-of select="str:link" /></xsl:attribute>
							<xsl:value-of select="str:caption" />
						</a>
					</li>
				</xsl:for-each>
			</ul>

			<xsl:call-template name="footer" />
		</div>

	</xsl:template>

	<xsl:template match="str:*" />
	<xsl:template match="xmvc:*" />

</xsl:stylesheet>