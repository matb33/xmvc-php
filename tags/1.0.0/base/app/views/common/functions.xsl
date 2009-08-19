<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template name="copy-of">
		<xsl:param name="select" />
		<xsl:for-each select="$select">
			<xsl:for-each select="node()|text()">
				<xsl:choose>
					<xsl:when test="self::*">
						<!-- Node -->
						<xsl:element name="{local-name()}">
							<xsl:copy-of select="@*" />
							<xsl:call-template name="copy-of">
								<xsl:with-param name="select" select="." />
							</xsl:call-template>
						</xsl:element>
					</xsl:when>
					<xsl:otherwise>
						<!-- Text -->
						<xsl:value-of select="." />
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
		</xsl:for-each>
	</xsl:template>

</xsl:stylesheet>