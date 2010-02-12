<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc page wireframe child head container dependency list item doc link inject custom config form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:page="urn:cc:page" xmlns:wireframe="urn:cc:wireframe" xmlns:child="urn:cc:child" xmlns:config="urn:cc:config" xmlns:head="urn:cc:head" xmlns:container="urn:cc:container" xmlns:dependency="urn:cc:dependency" xmlns:list="urn:cc:list" xmlns:item="urn:cc:item" xmlns:doc="urn:cc:doc" xmlns:link="urn:cc:link" xmlns:inject="urn:cc:inject" xmlns:form="urn:cc:form" xmlns:custom="urn:cc:custom">

	<xsl:template match="doc:heading1">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h1><xsl:apply-templates /></h1>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading2">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h2><xsl:apply-templates /></h2>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading3">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h3><xsl:apply-templates /></h3>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading4">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h4><xsl:apply-templates /></h4>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading5">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h5><xsl:apply-templates /></h5>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading6">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h6><xsl:apply-templates /></h6>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:para">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<p><xsl:if test="@class"><xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute></xsl:if><xsl:apply-templates /></p>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:itemizedlist">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<ul><xsl:apply-templates /></ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:orderedlist">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<ol><xsl:apply-templates /></ol>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:listitem">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<li><xsl:apply-templates /></li>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:span">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span><xsl:if test="@class"><xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute></xsl:if><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:imagedata">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<img>
				<xsl:if test="@fileref">
					<xsl:attribute name="src"><xsl:value-of select="@fileref" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@alt">
					<xsl:attribute name="alt"><xsl:value-of select="@alt" /></xsl:attribute>
				</xsl:if>
			</img>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>