<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container group nav reference inject doc sitemap form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:wirekit:component" xmlns:meta="urn:wirekit:meta" xmlns:container="urn:wirekit:container" xmlns:group="urn:wirekit:group" xmlns:nav="urn:wirekit:nav" xmlns:reference="urn:wirekit:reference" xmlns:inject="urn:wirekit:inject" xmlns:doc="urn:wirekit:doc" xmlns:sitemap="urn:wirekit:sitemap" xmlns:form="urn:wirekit:form" xmlns:loc="urn:wirekit:loc" xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">

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