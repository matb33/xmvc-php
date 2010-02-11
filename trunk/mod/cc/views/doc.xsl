<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc page wireframe child head container dependency list item doc link inject custom config form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:page="urn:cc:page" xmlns:wireframe="urn:cc:wireframe" xmlns:child="urn:cc:child" xmlns:config="urn:cc:config" xmlns:head="urn:cc:head" xmlns:container="urn:cc:container" xmlns:dependency="urn:cc:dependency" xmlns:list="urn:cc:list" xmlns:item="urn:cc:item" xmlns:doc="urn:cc:doc" xmlns:link="urn:cc:link" xmlns:inject="urn:cc:inject" xmlns:form="urn:cc:form" xmlns:custom="urn:cc:custom">

	<xsl:template match="doc:heading1">
		<h1><xsl:apply-templates /></h1>
	</xsl:template>

	<xsl:template match="doc:heading2">
		<h2><xsl:apply-templates /></h2>
	</xsl:template>

	<xsl:template match="doc:heading3">
		<h3><xsl:apply-templates /></h3>
	</xsl:template>

	<xsl:template match="doc:heading4">
		<h4><xsl:apply-templates /></h4>
	</xsl:template>

	<xsl:template match="doc:heading5">
		<h5><xsl:apply-templates /></h5>
	</xsl:template>

	<xsl:template match="doc:heading6">
		<h6><xsl:apply-templates /></h6>
	</xsl:template>

	<xsl:template match="doc:para">
		<p><xsl:apply-templates /></p>
	</xsl:template>

	<xsl:template match="doc:itemizedlist">
		<ul><xsl:apply-templates /></ul>
	</xsl:template>

	<xsl:template match="doc:listitem">
		<li><xsl:apply-templates /></li>
	</xsl:template>

	<xsl:template match="doc:span">
		<span><xsl:apply-templates /></span>
	</xsl:template>

</xsl:stylesheet>