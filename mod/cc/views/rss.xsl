<xsl:stylesheet version="1.0"
	exclude-result-prefixes="cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="urn:cc:root"
	xmlns:r="http://cyber.law.harvard.edu/rss/rss.html">

	<xsl:include href="mod/cc/views/inside.xsl" />

	<xsl:template match="cc:rss">
		<div id="rss">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="cc:heading">
		<h1><xsl:value-of select="." /></h1>
	</xsl:template>

	<xsl:template match="cc:rss-feed">
		<ul>
			<xsl:for-each select="r:rss/r:channel/r:item">
				<li>
					<h2><xsl:value-of select="r:title" /></h2>
					<p>
						<xsl:value-of select="r:description" /><br />
						<a href="{ r:link }"><xsl:value-of select="r:link" /></a>
					</p>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>

</xsl:stylesheet>