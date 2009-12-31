<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:clab="http://clab.xmvc.org/ns/clab/1.0">

	<xsl:template name="stylesheets">
		<link rel="stylesheet" type="text/css" href="/inc/styles/common.css" />
		<link rel="stylesheet" type="text/css" href="/inc/styles/inside.css" />
		<link rel="stylesheet" type="text/css" href="/inc/styles/contentlab.css" />
		<xsl:call-template name="instance-stylesheets" />
	</xsl:template>

	<xsl:template name="scripts">
		<script type="text/javascript" src="/inc/scripts/jquery-1.3.2.min.js"></script>
		<script type="text/javascript" src="/inc/scripts/base.js"></script>
		<script type="text/javascript" src="/inc/scripts/common.js"></script>
		<script type="text/javascript" src="/inc/scripts/validate.js"></script>
		<script type="text/javascript" src="/inc/scripts/lightbox.js"></script>
		<script type="text/javascript" src="/inc/scripts/contentlab.js"></script>
		<script type="text/javascript">
			<xsl:comment>

			var CLAB = new ContentLAB;

			$( document ).ready( function()
			{
				CLAB.definition = "inside";
				CLAB.instanceName = "<xsl:value-of select="//clab:instance[@clab:definition='inside']/@clab:instance-name" />";
			});

			</xsl:comment>
		</script>
		<xsl:call-template name="instance-scripts" />
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']">
		<div id="inside">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<!-- MAST AREA -->

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:mast">
		<div id="mast">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:mast/clab:logo">
		<xsl:variable name="clab-editable">clab <xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:definition" />/<xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:instance-name" /></xsl:variable>

		<a id="logo" href="/" class="{$clab-editable}">
			<xsl:value-of select="." />
		</a>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:mast/clab:mast-title">
		<div id="mast-title">
			<xsl:value-of select="." />
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:mast/clab:short-nav">
		<div id="short-nav">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:mast/clab:top-nav">
		<div id="top-nav">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<!-- PAGE AREA -->

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:page">
		<div id="page">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:page/clab:heading">
		<xsl:variable name="clab-editable">clab <xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:definition" />/<xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:instance-name" /></xsl:variable>

		<div id="heading-block">
			<h1 class="heading-caption {$clab-editable}">
				<xsl:value-of select="clab:heading-caption" />
			</h1>
			<div>
				<xsl:attribute name="class"><xsl:value-of select="clab:heading-deco" /></xsl:attribute>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:page/clab:content">
		<div id="content">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:page/clab:content/clab:column1">
		<div id="column1">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:page/clab:content/clab:column1/clab:deco-stub">
		<div class="box deco-stub" />
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:page/clab:content/clab:column1/clab:item-list">
		<xsl:variable name="clab-editable">clab <xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:definition" />/<xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:instance-name" /></xsl:variable>

		<div class="box items {$clab-editable}">
			<ul>
				<xsl:for-each select="clab:items/clab:item">
					<li>
						<xsl:call-template name="copy-of"><xsl:with-param name="select" select="clab:content" /></xsl:call-template>
					</li>
				</xsl:for-each>
			</ul>
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:page/clab:content/clab:body">
		<xsl:variable name="clab-editable">clab <xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:definition" />/<xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:instance-name" /></xsl:variable>

		<div id="body" class="{$clab-editable}">
			<xsl:call-template name="copy-of"><xsl:with-param name="select" select="." /></xsl:call-template>
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:page/clab:content/clab:column2">
		<div id="column2">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:page/clab:content/clab:column2/clab:phone-cta">
		<div id="phone-cta">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:page/clab:content/clab:column2/clab:mission-statement">
		<div id="mission-statement">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<!-- FOOTER AREA -->

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:footer">
		<div id="footer">
			<div id="footer-block">
				<xsl:apply-templates />
			</div>
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:footer/clab:copyright">
		<xsl:variable name="clab-editable">clab <xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:definition" />/<xsl:value-of select="ancestor-or-self::clab:instance[1]/@clab:instance-name" /></xsl:variable>

		<div id="copyright">
			<p class="copyright {$clab-editable}">
				<xsl:value-of select="." />
			</p>
		</div>
	</xsl:template>

	<xsl:template match="//clab:instance[@clab:definition='inside']/clab:footer/clab:footer-nav">
		<div id="footer-nav">
			<xsl:apply-templates />
		</div>
	</xsl:template>

</xsl:stylesheet>