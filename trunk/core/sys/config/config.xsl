<?xml version="1.0" encoding="utf-8" ?>

<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0">

	<xsl:output method="text" encoding="utf-8" indent="no" />

	<xsl:template match="/">
		$config = array();
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="/xmvc:root/xmvc:section">

		<xsl:if test="@uri-match">
		if( strpos( xMVC::URIProtocol() . "//" . xMVC::URI(), "<xsl:value-of select="@uri-match"/>" ) !== false )
		{
		</xsl:if>

		<xsl:apply-templates select="xmvc:var" />

		<xsl:if test="@uri-match">
		}
		</xsl:if>

	</xsl:template>

	<xsl:template match="xmvc:var">
		<xsl:choose>

			<xsl:when test="not(xmvc:item)">
				$config[ "<xsl:value-of select="@xmvc:name" />" ] = "<xsl:value-of select="." />";
			</xsl:when>

			<xsl:otherwise>
				$config[ "<xsl:value-of select="@xmvc:name" />" ] = array( <xsl:apply-templates select="xmvc:item" /> );
			</xsl:otherwise>

		</xsl:choose>
	</xsl:template>

	<xsl:template match="xmvc:item">
		<xsl:if test="xmvc:key">"<xsl:value-of select="xmvc:key" />" =&gt; </xsl:if><xsl:apply-templates select="xmvc:value" /><xsl:if test="not( position() = last() )">, </xsl:if>
	</xsl:template>

	<xsl:template match="xmvc:value">
		<xsl:choose>
			<xsl:when test="not(xmvc:item)">"<xsl:value-of select="." />"</xsl:when>
			<xsl:otherwise>array( <xsl:apply-templates select="xmvc:item" /> )</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>