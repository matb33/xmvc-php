<xsl:stylesheet version="1.0"
	exclude-result-prefixes="cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="urn:cc:root">

	<xsl:include href="mod/cc/views/xhtml1-strict.xsl" />
	<xsl:include href="mod/cc/views/navigation.xsl" />

	<xsl:template match="cc:home">

		<div id="home">
			<div id="header">
				<xsl:apply-templates select="//cc:navigation[ @name = 'top-nav' ]" />
			</div>

			<xsl:apply-templates />
		</div>

	</xsl:template>

	<xsl:template match="cc:heading">
		<h1><xsl:value-of select="." /></h1>
	</xsl:template>

</xsl:stylesheet>