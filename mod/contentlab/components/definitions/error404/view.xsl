<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:clab="http://clab.xmvc.org/ns/clab/1.0">

	<xsl:template name="stylesheets">
		<link rel="stylesheet" type="text/css" href="/inc/styles/error404.css" />
		<link rel="stylesheet" type="text/css" href="/inc/styles/contentlab.css" />
		<xsl:call-template name="instance-stylesheets" />
	</xsl:template>

	<xsl:template name="scripts">
		<script type="text/javascript" src="/inc/scripts/jquery-1.3.2.min.js"></script>
		<script type="text/javascript" src="/inc/scripts/base.js"></script>
		<script type="text/javascript" src="/inc/scripts/common.js"></script>
		<script type="text/javascript" src="/inc/scripts/contentlab.js"></script>
		<script type="text/javascript">
			<xsl:comment>

			var CLAB = new ContentLAB;

			$( document ).ready( function()
			{
				CLAB.definition = "error404";
				CLAB.instanceName = "//clab:instance[@clab:definition='error404']/@clab:instance-name";
			});

			</xsl:comment>
		</script>
		<xsl:call-template name="instance-scripts" />
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='error404']">
		<div id="error404">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='error404']/clab:content">
		<xsl:variable name="clab-editable">clab <xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:definition" />/<xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:instance-name" /></xsl:variable>

		<div id="content">
			<div class="{$clab-editable}">
				<xsl:call-template name="copy-of"><xsl:with-param name="select" select="." /></xsl:call-template>
			</div>
		</div>
	</xsl:template>

</xsl:stylesheet>