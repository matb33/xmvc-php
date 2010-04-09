<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container xcontainer group nav reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:cc:component" xmlns:meta="urn:cc:meta" xmlns:container="urn:cc:container" xmlns:xcontainer="urn:cc:xcontainer" xmlns:group="urn:cc:group" xmlns:nav="urn:cc:nav" xmlns:reference="urn:cc:reference" xmlns:inject="urn:cc:inject" xmlns:doc="urn:cc:doc" xmlns:sitemap="urn:cc:sitemap" xmlns:form="urn:cc:form" xmlns:loc="urn:cc:loc" xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">

	<xsl:include href="../common.xsl" />

	<!-- Global wiredoc templates for xMVC -->

	<xsl:template match="/xmvc:root">
		<xsl:apply-templates select="component:definition" />
	</xsl:template>

	<xsl:template match="component:definition">
		<xsl:if test="lang( $lang )">
			<xsl:apply-templates />
		</xsl:if>
	</xsl:template>

	<!-- XLIFF for wiredoc -->

	<xsl:template match="loc:*">
		<xsl:variable name="id1">&lt;<xsl:value-of select="name()" /> /&gt;</xsl:variable>
		<xsl:variable name="id2">&lt;<xsl:value-of select="name()" />/&gt;</xsl:variable>
		<xsl:variable name="id3">&lt;<xsl:value-of select="name()" />&gt;&lt;/<xsl:value-of select="name()" />&gt;</xsl:variable>
		<xsl:value-of select="//xliff:body[ ../xliff:header/xliff:skl/xliff:external-file[ @href = //xmvc:strings/xmvc:instance-file ] ]/xliff:trans-unit[ @resname = $id1 or @resname = $id2 or @resname = $id3 ]/xliff:source[ lang( $lang ) ]/text()" />
	</xsl:template>

</xsl:stylesheet>