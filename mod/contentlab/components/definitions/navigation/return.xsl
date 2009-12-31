<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:clab="http://clab.xmvc.org/ns/clab/1.0">

	<xsl:output
		method="xml"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="no"
	/>

	<xsl:include href="mod/contentlab/views/functions.xsl" />

	<xsl:template match="/">
		<clab:instance-return>
			<xsl:apply-templates />
		</clab:instance-return>
	</xsl:template>

	<xsl:template match="ul">
		<clab:items>
			<xsl:apply-templates />
		</clab:items>
	</xsl:template>

	<xsl:template match="li">
		<clab:item>
			<clab:content>
				<xsl:call-template name="copy-of"><xsl:with-param name="select" select="." /></xsl:call-template>
				<xsl:if test="count( ul/* )">
					<xsl:apply-templates select="ul" />
				</xsl:if>
			</clab:content>
		</clab:item>
	</xsl:template>

</xsl:stylesheet>