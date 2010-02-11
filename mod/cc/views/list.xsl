<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc page wireframe child head container dependency list item doc link inject custom config form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:page="urn:cc:page" xmlns:wireframe="urn:cc:wireframe" xmlns:child="urn:cc:child" xmlns:config="urn:cc:config" xmlns:head="urn:cc:head" xmlns:container="urn:cc:container" xmlns:dependency="urn:cc:dependency" xmlns:list="urn:cc:list" xmlns:item="urn:cc:item" xmlns:doc="urn:cc:doc" xmlns:link="urn:cc:link" xmlns:inject="urn:cc:inject" xmlns:form="urn:cc:form" xmlns:custom="urn:cc:custom">

	<xsl:template match="list:*">
		<ul id="{ local-name() }" class="layout">
			<xsl:for-each select="item:*">
				<li>
					<xsl:attribute name="class">
						<xsl:choose>
							<xsl:when test="last() = 1">first-child last-child</xsl:when>
							<xsl:when test="position() = 1">first-child</xsl:when>
							<xsl:when test="position() = last()">last-child</xsl:when>
							<xsl:otherwise>middle-child</xsl:otherwise>
						</xsl:choose><xsl:text> </xsl:text>item-<xsl:value-of select="position()" /><xsl:text> </xsl:text>layout<xsl:if test="item:class[ not( @lang ) or @lang = //xmvc:lang ]"><xsl:text> </xsl:text><xsl:value-of select="item:class[ not( @lang ) or @lang = //xmvc:lang ]" /></xsl:if>
					</xsl:attribute>
					<xsl:apply-templates />
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>

	<!-- Running item in a for-each because otherwise the position() function returns an incorrect number -->
	<xsl:template match="item:*" />

</xsl:stylesheet>