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

	<xsl:template match="//a[@id='logo']">
		<clab:mast>
			<clab:logo><xsl:value-of select="." /></clab:logo>
		</clab:mast>
	</xsl:template>

	<xsl:template match="//h1[substring(@class,1,15)='heading-caption']">
		<clab:page>
			<clab:heading>
				<clab:heading-caption><xsl:value-of select="." /></clab:heading-caption>
			</clab:heading>
		</clab:page>
	</xsl:template>

	<xsl:template match="//div[substring(@class,1,9)='box items']">
		<clab:page>
			<clab:content>
				<clab:column1>
					<clab:item-list>
						<xsl:apply-templates />
					</clab:item-list>
				</clab:column1>
			</clab:content>
		</clab:page>
	</xsl:template>

	<xsl:template match="//div[substring(@class,1,9)='box items']/ul">
		<clab:items>
			<xsl:for-each select="li">
				<clab:item>
					<clab:content>
						<xsl:call-template name="copy-of"><xsl:with-param name="select" select="." /></xsl:call-template>
					</clab:content>
				</clab:item>
			</xsl:for-each>
		</clab:items>
	</xsl:template>

	<xsl:template match="//div[@id='body']">
		<clab:page>
			<clab:content>
				<clab:body>
					<xsl:call-template name="copy-of"><xsl:with-param name="select" select="." /></xsl:call-template>
				</clab:body>
			</clab:content>
		</clab:page>
	</xsl:template>

	<xsl:template match="//p[substring(@class,1,9)='copyright']">
		<clab:footer>
			<clab:copyright><xsl:value-of select="." /></clab:copyright>
		</clab:footer>
	</xsl:template>

</xsl:stylesheet>