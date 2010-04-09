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
		indent="no"
		omit-xml-declaration="yes"
	/>

	<xsl:template match="/xmvc:root">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<title>Instance Editor: <xsl:value-of select="//xmvc:content" /> | CCMS</title>
				<meta http-equiv="content-type" content="text/html; charset=utf-8" />
				<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/reset-0.0.7.css" />
				<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/typography.css" />
				<link rel="stylesheet" type="text/css" media="screen" href="/CC/inc/styles/ccms.css" />
				<script type="text/javascript" src="/CC/inc/scripts/jquery-1.4.1.tools.min.js" />
				<script type="text/javascript" src="/CC/inc/scripts/tiny_mce/jquery.tinymce.js" />
				<script type="text/javascript">
					$( document ).ready( function()
					{
						$( "textarea.xhtml" ).tinymce( {
							script_url : '/inc/scripts/tiny_mce/tiny_mce.js',
							theme : "advanced",
							plugins : "safari,style,save,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars",
							theme_advanced_buttons1 : "save,|,bold,italic,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote",
							theme_advanced_buttons2 : "undo,redo,|,link,unlink,anchor,cleanup,help,code,|,insertdate,inserttime,preview,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,|,print,|,ltr,rtl,|,fullscreen,|,attribs,|,visualchars",
							theme_advanced_buttons3 : "",
							theme_advanced_toolbar_location : "top",
							theme_advanced_toolbar_align : "left",
							theme_advanced_statusbar_location : "bottom",
							theme_advanced_resizing : false,
							content_css : "/inc/styles/typography.css",
							valid_elements : "h1,h2,h3,h4,h5,h6,p,a,strong/b,em/i,address,ul,ol,li,br,span,sup,sub,cc:page-name"
						});
					});
				</script>
				<style type="text/css">
					div.lang
					{
						width: <xsl:value-of select="//xmvc:proportion" />%;
					}
				</style>
			</head>
			<body>
				<div id="ccms">
					<h1>Instance Editor: <span><xsl:value-of select="//xmvc:container" /> / <xsl:value-of select="//xmvc:content" /></span></h1>
					<xsl:apply-templates select="cc:editable-nodes" />
				</div>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="cc:editable-nodes">
		<xsl:variable name="scope" select="." />
		<xsl:for-each select="//xmvc:defined-lang">
			<xsl:variable name="lang" select="." />
			<div class="lang">
				<h2>Language: <span><xsl:value-of select="$lang" /></span></h2>
				<xsl:apply-templates select="$scope/cc:node[ @lang = $lang or @lang = '' ]" />
			</div>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="cc:node">
		<fieldset class="node">
			<legend><xsl:value-of select="@friendly-path" /></legend>
			<xsl:choose>
				<xsl:when test="@is-xhtml = '1'">
					<textarea class="xhtml">
						<xsl:apply-templates select="./*|text()/*" />
					</textarea>
				</xsl:when>
				<xsl:otherwise>
					<textarea class="string">
						<xsl:apply-templates select="./*" />
					</textarea>
					<input type="button" class="string-save" value="Save" />
				</xsl:otherwise>
			</xsl:choose>
		</fieldset>
	</xsl:template>

	<xsl:template match="xmvc:*" />

	<!-- Strip namespaces from XHTML using an identity template -->

	<xsl:template match="*[ @xhtml = '1' ]">
		<xsl:apply-templates select="node()" />
	</xsl:template>

	<xsl:template match="*[ @xhtml = '1' ]//*">
		<xsl:element name="{ local-name() }">
			<xsl:apply-templates select="@* | node()" />
		</xsl:element>
	</xsl:template>

	<xsl:template match="*[ @xhtml = '1' ]//@*">
		<xsl:attribute name="{ local-name() }">
			<xsl:apply-templates />
		</xsl:attribute>
	</xsl:template>

</xsl:stylesheet>