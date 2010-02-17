<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc page wireframe child head container dependency list item doc link inject c config form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:page="urn:cc:page" xmlns:wireframe="urn:cc:wireframe" xmlns:child="urn:cc:child" xmlns:config="urn:cc:config" xmlns:head="urn:cc:head" xmlns:container="urn:cc:container" xmlns:dependency="urn:cc:dependency" xmlns:list="urn:cc:list" xmlns:item="urn:cc:item" xmlns:doc="urn:cc:doc" xmlns:link="urn:cc:link" xmlns:inject="urn:cc:inject" xmlns:form="urn:cc:form" xmlns:c="urn:cc:custom">

	<xsl:template match="doc:heading1">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h1><xsl:apply-templates /></h1>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading2">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h2><xsl:apply-templates /></h2>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading3">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h3><xsl:apply-templates /></h3>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading4">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h4><xsl:apply-templates /></h4>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading5">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h5><xsl:apply-templates /></h5>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:heading6">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<h6><xsl:apply-templates /></h6>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:para">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<p><xsl:if test="@class"><xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute></xsl:if><xsl:apply-templates /></p>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:itemizedlist">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<ul><xsl:apply-templates /></ul>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:orderedlist">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<ol><xsl:apply-templates /></ol>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:listitem">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<li><xsl:apply-templates /></li>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:span">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span><xsl:if test="@class"><xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute></xsl:if><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:imagedata">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<img>
				<xsl:if test="@fileref">
					<xsl:attribute name="src"><xsl:value-of select="@fileref" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="@alt">
					<xsl:attribute name="alt"><xsl:value-of select="@alt" /></xsl:attribute>
				</xsl:if>
			</img>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:emphasis">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<em><xsl:apply-templates /></em>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:strong">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<strong><xsl:apply-templates /></strong>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:address">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<address><xsl:apply-templates /></address>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:copyright">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span class="copyright"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:phone">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span class="phone"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:fax">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span class="fax"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:postcode">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span class="postcode"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:street">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span class="street"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:city">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span class="city"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:country">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span class="country"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:date">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span class="date"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:year">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span class="year"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:email">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<span class="email"><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:abbrev">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<abbr><xsl:apply-templates /></abbr>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:acronym">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<acronym><xsl:apply-templates /></acronym>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:quote">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<q><xsl:apply-templates /></q>
		</xsl:if>
	</xsl:template>

	<xsl:template match="doc:linebreak">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<br />
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>