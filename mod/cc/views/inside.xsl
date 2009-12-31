<xsl:stylesheet version="1.0"
	exclude-result-prefixes="cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="http://www.xmvc.org/ns/cc/1.0">

	<xsl:include href="xhtml1-strict.xsl" />
	<xsl:include href="navigation.xsl" />

	<xsl:template match="cc:inside">

		<div id="inside">
			<div id="header">
				<xsl:apply-templates select="//cc:navigation[ @cc:name = 'top-nav' ]" />
			</div>

			<xsl:apply-templates />

			<div id="footer">
				<xsl:apply-templates select="//cc:navigation[ @cc:name = 'footer-nav' ]" />
			</div>
		</div>

	</xsl:template>

</xsl:stylesheet>