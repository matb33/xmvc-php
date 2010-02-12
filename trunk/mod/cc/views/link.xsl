<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc page wireframe child head container dependency list item doc link inject custom config form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:page="urn:cc:page" xmlns:wireframe="urn:cc:wireframe" xmlns:child="urn:cc:child" xmlns:config="urn:cc:config" xmlns:head="urn:cc:head" xmlns:container="urn:cc:container" xmlns:dependency="urn:cc:dependency" xmlns:list="urn:cc:list" xmlns:item="urn:cc:item" xmlns:doc="urn:cc:doc" xmlns:link="urn:cc:link" xmlns:inject="urn:cc:inject" xmlns:form="urn:cc:form" xmlns:custom="urn:cc:custom">

	<xsl:template match="link:*[ count( ./link:* ) &gt; 0 ]">
		<xsl:choose>
			<xsl:when test="link:href[ not( @lang ) or @lang = //xmvc:lang ]">
				<a id="{ local-name() }" href="{ link:href[ not( @lang ) or @lang = //xmvc:lang ] }">
					<xsl:if test="link:title[ not( @lang ) or @lang = //xmvc:lang ]">
						<xsl:attribute name="title"><xsl:value-of select="link:title[ not( @lang ) or @lang = //xmvc:lang ]" /></xsl:attribute>
					</xsl:if>
					<xsl:if test="link:target[ not( @lang ) or @lang = //xmvc:lang ]">
						<xsl:attribute name="target"><xsl:value-of select="link:target[ not( @lang ) or @lang = //xmvc:lang ]" /></xsl:attribute>
					</xsl:if>
					<xsl:apply-templates select="link:caption" />
				</a>
			</xsl:when>
			<xsl:otherwise>
				<span><xsl:apply-templates select="link:caption" /></span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="link:*/link:caption">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<xsl:apply-templates />
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>