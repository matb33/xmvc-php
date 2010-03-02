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

	<xsl:include href="common.xsl" />

	<xsl:template match="/xmvc:root">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{ //xmvc:lang }" lang="{ //xmvc:lang }">
			<head>
				<title>Editor: <xsl:value-of select="//xmvc:content" /> | CCMS</title>
				<meta http-equiv="content-type" content="text/html; charset=utf-8" />
				<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/reset-0.0.7.css" />
				<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/typography.css" />
				<link rel="stylesheet" type="text/css" media="screen" href="/cc/inc/styles/ccms.css" />
				<script type="text/javascript" src="/cc/inc/scripts/jquery-1.4.1.tools.min.js" />
				<script type="text/javascript" src="/cc/inc/scripts/tiny_mce/jquery.tinymce.js" />
				<script type="text/javascript">
					<xsl:comment>

						var CCMS = new function()
						{
							this.Write = function( rootElement )
							{
								var content = $( "&lt;" + rootElement[ 0 ].localName + "/&gt;" ).append( rootElement.clone() ).remove().html();
								var contentEncoded	= urlencode( ( content.length > 0 ? base64_encode( content ) : "" ) );

								if( "<xsl:value-of select="//xmvc:container" />" == "" )
								{
									alert( "No container name specified, can't write!" );
								}
								else if( "<xsl:value-of select="//xmvc:content" />" == "" )
								{
									alert( "No content name specified, can't write!" );
								}
								else
								{
									$.ajax(
									{
										type: "POST",
										url: "/ccms/write/<xsl:value-of select="//xmvc:container" />/<xsl:value-of select="//xmvc:content" />/",
										dataType: "text",
										processData: false,
										data: "d=" + contentEncoded,
										success: function( data, textStatus ) { CCMS.OnWriteCompleted( data, textStatus ); }
									});
								}
							};

							this.OnWriteCompleted = function( data, textStatus )
							{
								//alert( "textStatus: " + textStatus );
							};

							this.InitializeTinyMCE = function()
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
							};

							this.HackSecondaryAttributes = function()
							{
								// We are forced to use attributes like value2 and selected2 because when jQuery
								// pulls the html, the fields such as value and selected are not updated with what
								// the user entered/selected. We synchronize value2 and selected2 with the
								// following event handlers:

								$( "input" ).change( function()
								{
									$( this ).attr( "value2", $( this ).val() );
								});

								$( "select" ).change( function()
								{
									$( this ).children( "option:selected" ).attr( "selected2", "true" );
									$( this ).children( "option:not( :selected )" ).removeAttr( "selected2" );
								});
							};
						}

						$( document ).ready( function()
						{
							CCMS.InitializeTinyMCE();
							CCMS.HackSecondaryAttributes();
						});

					</xsl:comment>
				</script>
				<xsl:call-template name="script" />
				<style type="text/css">
					<xsl:comment>
						div.lang
						{
							width: <xsl:value-of select="//xmvc:proportion" />%;
						}
					</xsl:comment>
				</style>
				<xsl:call-template name="style" />
			</head>
			<body>
				<div id="ccms">
					<h1>Editor: <span><xsl:value-of select="//xmvc:container" /> / <xsl:value-of select="//xmvc:content" /></span></h1>
					<xsl:apply-templates select="cc:root" />
				</div>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>