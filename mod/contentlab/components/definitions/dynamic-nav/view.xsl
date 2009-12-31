<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:clab="http://clab.xmvc.org/ns/clab/1.0">

	<xsl:template match="//clab:instance[@clab:definition='dynamic-nav']">
		<div class="navigation">
			<xsl:apply-templates select="clab:items" />
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='dynamic-nav']//clab:items">
		<ul>
			<xsl:for-each select="clab:item">
				<li>
					<xsl:call-template name="copy-of"><xsl:with-param name="select" select="clab:content" /></xsl:call-template>
					<xsl:if test="count( clab:items/* )">
						<xsl:apply-templates select="clab:items" />
					</xsl:if>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>

</xsl:stylesheet>