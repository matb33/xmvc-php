<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:clab="http://clab.xmvc.org/ns/clab/1.0">

	<xsl:template match="//clab:instance[@clab:definition='navigation']">
		<xsl:variable name="clab-editable">clab <xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:definition" />/<xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:instance-name" /></xsl:variable>

		<div class="navigation">
			<div class="{$clab-editable}">
				<xsl:apply-templates select="clab:items" />
			</div>
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='navigation']//clab:items">
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