<xsl:stylesheet version="1.0"
	exclude-result-prefixes="cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="urn:cc:root">

	<xsl:template match="cc:navigation">
		<ul class="navigation layout">
			<xsl:apply-templates select="cc:item" />
		</ul>
	</xsl:template>

	<xsl:template match="cc:item">
		<li class="layout">
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="last() = 1">first-child last-child</xsl:when>
					<xsl:when test="position() = 1">first-child</xsl:when>
					<xsl:when test="position() = last()">last-child</xsl:when>
					<xsl:otherwise>middle-child</xsl:otherwise>
				</xsl:choose> item-<xsl:value-of select="position()" />
				<xsl:if test="cc:class"><xsl:text> </xsl:text><xsl:value-of select="cc:class[ not( @lang ) or @lang = //xmvc:lang ]" /></xsl:if>
			</xsl:attribute>
			<xsl:choose>
				<xsl:when test="cc:href">
					<a href="{ cc:href }">
						<xsl:if test="cc:title">
							<xsl:attribute name="title"><xsl:value-of select="cc:title[ not( @lang ) or @lang = //xmvc:lang ]" /></xsl:attribute>
						</xsl:if>
						<xsl:if test="cc:target">
							<xsl:attribute name="target"><xsl:value-of select="cc:target" /></xsl:attribute>
						</xsl:if>
						<xsl:value-of select="cc:caption[ not( @lang ) or @lang = //xmvc:lang ]" />
					</a>
				</xsl:when>
				<xsl:otherwise>
					<span><xsl:value-of select="cc:caption[ not( @lang ) or @lang = //xmvc:lang ]" /></span>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:apply-templates select="cc:navigation" />
		</li>
	</xsl:template>

</xsl:stylesheet>