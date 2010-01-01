<xsl:stylesheet version="1.0"
	exclude-result-prefixes="cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="http://www.xmvc.org/ns/cc/1.0">

	<xsl:include href="mod/cc/views/inside.xsl" />

	<xsl:template match="cc:contact">
		<div id="contact">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="cc:heading">
		<h1><xsl:value-of select="." /></h1>
	</xsl:template>

	<xsl:template match="cc:form">
		<form method="post" action="send">
			<label for="firstname">
				<xsl:value-of select="cc:firstname" />
				<input type="text" id="firstname" name="firstname" />
			</label>
			<label for="lastname">
				<xsl:value-of select="cc:lastname" />
				<input type="text" id="lastname" name="lastname" />
			</label>
			<label for="email">
				<xsl:value-of select="cc:email" />
				<input type="text" id="email" name="email" />
			</label>
			<input type="submit" id="contact-button" value="{ cc:contact-button }" />
		</form>
	</xsl:template>

</xsl:stylesheet>