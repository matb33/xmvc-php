<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:clab="http://clab.xmvc.org/ns/clab/1.0">

	<xsl:template match="//clab:instance[@clab:definition='blue-shadow']">

		<xsl:variable name="clab-editable">clab <xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:definition" />/<xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:instance-name" /></xsl:variable>

		<div class="box blue-shadow">
			<h4 class="{$clab-editable}">
				<xsl:value-of select="clab:heading" />
			</h4>
			<div class="{$clab-editable}">
				<xsl:call-template name="copy-of"><xsl:with-param name="select" select="clab:content" /></xsl:call-template>
			</div>
		</div>

	</xsl:template>

</xsl:stylesheet>