<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc cc"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:cc="urn:cc:root">

	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
		encoding="UTF-8"
		indent="yes"
		omit-xml-declaration="yes"
	/>

	<xsl:template match="/xmvc:root">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<title>Instance Selection | CCMS</title>
				<meta http-equiv="content-type" content="text/html; charset=utf-8" />
				<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/reset-0.0.7.css" />
				<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/typography.css" />
				<link rel="stylesheet" type="text/css" media="screen" href="/cc/inc/styles/ccms.css" />
			</head>
			<body>
				<div id="ccms">
					<h1>Instance Selection</h1>
					<p>Start editing content by selecting an instance from the list below:</p>
					<xsl:apply-templates select="cc:editable-instances" />
				</div>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="cc:editable-instances">
		<xsl:apply-templates select="cc:container[ count( ./cc:instance ) &gt; 0 ]" />
	</xsl:template>

	<xsl:template match="cc:container">
		<fieldset class="container">
			<legend><strong>Container: </strong><em><xsl:apply-templates select="@name" /></em></legend>
			<ul>
				<xsl:apply-templates select="cc:instance" />
			</ul>
		</fieldset>
	</xsl:template>

	<xsl:template match="cc:instance">
		<li>
			<a href="/ccms/edit/{ ../@name }/{ @name }/"><xsl:value-of select="@name" /></a>
		</li>
	</xsl:template>

</xsl:stylesheet>