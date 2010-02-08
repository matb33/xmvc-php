<xsl:stylesheet version="1.0"
	exclude-result-prefixes="cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="urn:cc:root">

	<xsl:output
		method="xml"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="no"
	/>

	<xsl:template match="/">
		<cc:root>
			<xsl:apply-templates />
		</cc:root>
	</xsl:template>

	<xsl:template match="ul">
		<cc:navigation>
			<xsl:if test="preceding-sibling::input[ @name='name' ]">
				<xsl:attribute name="name"><xsl:value-of select="preceding-sibling::input[ @name='name' ]/@value" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="li/fieldset" />
		</cc:navigation>
	</xsl:template>

	<xsl:template match="li/fieldset">
		<cc:item>
			<xsl:if test="label/input[ @name='caption' ]">
				<xsl:for-each select="label/input[ @name='caption' ]/@class">
					<cc:caption lang="{ . }"><xsl:value-of select="../@value2" /></cc:caption>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="label/input[ @name='title' ]">
				<xsl:for-each select="label/input[ @name='title' ]/@class">
					<cc:title lang="{ . }"><xsl:value-of select="../@value2" /></cc:title>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="label/input[ @name='page-name' ] and label/input[ @name='page-name' ]/@value2 != ''">
				<cc:page-name><xsl:value-of select="label/input[ @name='page-name' ]/@value2" /></cc:page-name>
			</xsl:if>
			<xsl:if test="label/select[ @name='target' ]/option[ @selected2 != '' ]">
				<cc:target><xsl:value-of select="label/select[ @name='target' ]/option[ @selected2 != '' ]/@value" /></cc:target>
			</xsl:if>
			<xsl:if test="label/input[ @name='class' ]">
				<xsl:for-each select="label/input[ @name='class' ]/@class">
					<cc:class lang="{ . }"><xsl:value-of select="../@value2" /></cc:class>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="label/input[ @name='lang-swap' ]">
				<xsl:for-each select="label/input[ @name='lang-swap' ]/@class">
					<cc:lang-swap lang="{ . }" suffix="{ ../following-sibling::input[ @name='suffix' ]/@value2 }"><xsl:value-of select="../@value2" /></cc:lang-swap>
				</xsl:for-each>
			</xsl:if>
			<xsl:apply-templates select="../ul" />
		</cc:item>
	</xsl:template>

</xsl:stylesheet>