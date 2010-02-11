<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc page wireframe child head container dependency list item doc link inject custom config form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:page="urn:cc:page" xmlns:wireframe="urn:cc:wireframe" xmlns:child="urn:cc:child" xmlns:config="urn:cc:config" xmlns:head="urn:cc:head" xmlns:container="urn:cc:container" xmlns:dependency="urn:cc:dependency" xmlns:list="urn:cc:list" xmlns:item="urn:cc:item" xmlns:doc="urn:cc:doc" xmlns:link="urn:cc:link" xmlns:inject="urn:cc:inject" xmlns:form="urn:cc:form" xmlns:custom="urn:cc:custom">

	<xsl:include href="common.xsl" />
	<xsl:include href="container.xsl" />
	<xsl:include href="doc.xsl" />
	<xsl:include href="form.xsl" />
	<xsl:include href="link.xsl" />
	<xsl:include href="list.xsl" />

	<xsl:template match="/xmvc:root">
		<xsl:apply-templates select="page:*" />
	</xsl:template>

	<xsl:template match="wireframe:*">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="config:*" />

</xsl:stylesheet>