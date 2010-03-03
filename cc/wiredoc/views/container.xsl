<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc page wireframe child head container dependency list item doc link inject c config form" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:page="urn:cc:page" xmlns:wireframe="urn:cc:wireframe" xmlns:child="urn:cc:child" xmlns:config="urn:cc:config" xmlns:head="urn:cc:head" xmlns:container="urn:cc:container" xmlns:dependency="urn:cc:dependency" xmlns:list="urn:cc:list" xmlns:item="urn:cc:item" xmlns:doc="urn:cc:doc" xmlns:link="urn:cc:link" xmlns:inject="urn:cc:inject" xmlns:form="urn:cc:form" xmlns:c="urn:cc:custom">

	<xsl:template match="container:*">
		<xsl:if test="not( @lang ) or @lang = //xmvc:lang">
			<div id="{ local-name() }">
				<xsl:apply-templates />
			</div>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>