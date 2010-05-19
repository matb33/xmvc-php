<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0">

	<xsl:template name="head">
		<xsl:if test="//wd:meta[ @wd:name='title' and lang( $lang ) ]">
			<xsl:variable name="default-glue" select="' | '" />
			<xsl:variable name="sort-order">
				<xsl:choose>
					<xsl:when test="//wd:meta[ @wd:name='title' ]/@sort-order"><xsl:value-of select="//wd:meta[ @wd:name='title' ]/@sort-order[ 1 ]" /></xsl:when>
					<xsl:otherwise>ascending</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<title>
				<xsl:for-each select="//wd:meta[ @wd:name='title' and lang( $lang ) ]">
					<xsl:sort select="position()" data-type="number" order="{ $sort-order }" />
					<xsl:value-of select="." />
					<xsl:if test="position() != last()">
						<xsl:choose>
							<xsl:when test="@glue"><xsl:value-of select="@glue" /></xsl:when>
							<xsl:otherwise><xsl:value-of select="$default-glue" /></xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</xsl:for-each>
			</title>
		</xsl:if>
		<xsl:for-each select="//wd:meta">
			<xsl:if test="lang( $lang )">
				<xsl:choose>
					<xsl:when test="@wd:name = 'link'">
						<xsl:element name="link">
							<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' ]" />
						</xsl:element>
					</xsl:when>
					<xsl:when test="@wd:name = 'meta'">
						<xsl:element name="meta">
							<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' ]" />
						</xsl:element>
					</xsl:when>
					<xsl:when test="@wd:name = 'script'">
						<xsl:element name="script">
							<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' ]" />
							<xsl:comment>
								<xsl:apply-templates />
							</xsl:comment>
						</xsl:element>
					</xsl:when>
					<xsl:when test="@wd:name = 'style'">
						<xsl:element name="style">
							<xsl:copy-of select="@*[ namespace-uri() != 'http://www.wiredoc.org/ns/wiredoc/2.0' ]" />
							<xsl:comment>
								<xsl:apply-templates />
							</xsl:comment>
						</xsl:element>
					</xsl:when>
					<xsl:otherwise />
				</xsl:choose>
			</xsl:if>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="wd:meta" />

</xsl:stylesheet>