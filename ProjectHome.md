## About xMVC ##

xMVC is a light-weight MVC framework for PHP 5.3+ that intimately connects XML, XSLT and PHP.  The concept behind xMVC is storing data in XML models, leveraging filenames and folder hierarchy to segregate data, and applying XSL transformations on only the XML models specified by the PHP controller.  In other words, the controller loads XML models, puts them in a stack, and applies an XSL transformation (view) on this stack of XML models.

## Controllers ##

xMVC controllers are defined as static PHP classes. Models and views are manipulated within the controllers.

## Models ##

Models are XML files. For non-XML data, such as data from a database, a model-driver converts this data to XML.  A select few model-drivers have been written to handle the most common non-XML formats (SQL and filesystem model-drivers so far).  In this way, the models are always consistently XML. They are subsequently pushed into a view's model-stack from within the controller.

Inline PHP is supported in the XML models; however, it is almost always possible to avoid its use.  It is also advisable to keep XML models PHP-free in order to allow third-party applications to cleanly read and write to the XML file.

## Views ##

Views are written in XSLT, and are invoked from within the controller. XSLTs are used to parse the various stacked models and output final results, usually as XHTML.

Client-side and server-side XSLT rendering are supported transparently by the framework.  Configuration options exist to force server- or client-side rendering.  Most development time for xMVC has been in the context of server-side XSLT rendering due to its guaranteed support regardless of client browser.  However, it is up to the developer to decide which path to take.

As with XML models, XSLT views also support inline PHP.  Although inline PHP in a view can come in handy, it is recommended to use the **strings** model driver to pass around variables originating from the controller.  You can also choose to enable registerPHPFunctions.

## xMVC vs other PHP MVC frameworks ##

This framework was not designed for the beginner in mind.  It was designed for PHP programmers who do not want bulky frameworks but still want to maintain the MVC pattern.  If your project deals in any way with XML, **xMVC makes it ridiculously easy**.

Because models are strictly XML and views are strictly XSLT, this can intimidate the average PHP coder.  The bundled named-template application structure is a good stepping stone to initiate XSLT novices to XSLT in general.  It makes heavy use of `xsl:call-template` in order to bridge the gap between the traditional PHP `include` function and XSLT.  In the majority of cases, this is sufficient for simple websites.  Using the CC module included in the download is recommended for larger projects (example code coming).

Those comfortable with storing data in XML will find the convenience of pushing XML data straight through to their XSLT views a major time-saver, especially when it comes to maintenance and localization.

It is also very small and makes a point to remain slim. Third-party libraries have thus far been avoided as core PHP has been able to provide all necessary functionality.

The disadvantages are lack of community support since no concerted effort has been put into publicizing this project.  There is also a lack of testing and debugging-related features.  There is an ongoing effort to resolve these issues.

## Getting started ##

  1. Download the featured zip
  1. Extract to a folder in your web environment

**NOTE:** With xMVC's default URL rewriting setup, the URL to access this folder must be by host-name only (no subfolders), such as `http://xmvctest.local/`.  If you prefer to use subfolders, you will have to make modifications to the URL rewriting rules in the included .htaccess file.

We will soon make available an alternate version of the application portion of xMVC that makes use of `xsl:apply-templates` instead.

## Examples ##

Below is a Controller, a few Models and a View to help illustrate what to expect.  Some files reference other external files, which aren't displayed in the examples below.  They can be found in the download package.

**Note that the models and view are setup in named-template fashion, making ample use of `xsl:call-template`. Be aware that this is only _one_ way of organizing your models and views, and you should not feel limited to this method. However, it has proven to work very well amongst groups of developers with varying experience levels due to its familiar similarity to PHP's include.**

### Controller (app/controllers/website.php) ###
```
namespace xMVC\App;

use xMVC\Sys\XMLModelDriver;

class Website
{
	protected $commonContent;

	public function __construct()
	{
		$this->commonContent = new XMLModelDriver( "content/en/common" );
	}
}
```
### Controller (app/controllers/home.php) ###
```
namespace xMVC\App;

use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\View;

class Home extends Website
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		$pageContent = new XMLModelDriver( "content/en/home" );

		$page = new View( "home" );
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->RenderAsHTML();
	}
}
```
### Common Content (app/models/content/en/common.xml) ###
_NOTE: The **str** namespace is a utility namespace that allows any element to be created in whichever hierarchy needed. This is useful to quickly put together content in a hierarchical manner without worrying about discrete validation._
```
<?xml version="1.0" encoding="utf-8" ?>
<str:strings xmlns:str="http://www.example.com/ns/str/1.0" xmlns="http://www.w3.org/1999/xhtml">
	<str:logo-caption>Example Website®</str:logo-caption>
	<str:copyright-notice>© 2009 Example Website Corporation. All rights reserved.</str:copyright-notice>
	<str:navigation>
		<str:items>
			<str:item>
				<str:caption>Home</str:caption>
				<str:link>/</str:link>
			</str:item>
			<str:item>
				<str:caption>Contact Us</str:caption>
				<str:link>/contact-us/</str:link>
			</str:item>
		</str:items>
	</str:navigation>
</str:strings>
```
### Page Content (app/models/content/en/home.xml) ###
```
<?xml version="1.0" encoding="utf-8" ?>
<str:strings xmlns:str="http://www.example.com/ns/str/1.0" xmlns="http://www.w3.org/1999/xhtml">
	<str:title>Home | Example Website</str:title>
	<str:intro>
		<p>Hello World</p>
		<p>Here is a list of search engines:</p>
	</str:intro>
	<str:search-engines>
		<str:search-engine>
			<str:caption>Google</str:caption>
			<str:link>http://www.google.com/</str:link>
		</str:search-engine>
		<str:search-engine>
			<str:caption>Yahoo</str:caption>
			<str:link>http://www.yahoo.com/</str:link>
		</str:search-engine>
		<str:search-engine>
			<str:caption>MSN</str:caption>
			<str:link>http://www.msn.com/</str:link>
		</str:search-engine>
	</str:search-engines>
</str:strings>
```
### View (app/views/home.xsl) ###
_NOTE: The view is incomplete as it is missing required external xsl files. This is only an example._

```
<xsl:stylesheet version="1.0"
	exclude-result-prefixes="str"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:str="http://www.example.com/ns/str/1.0">

	<xsl:include href="app/views/common/xhtml.xsl" />
	<xsl:include href="app/views/header.xsl" />
	<xsl:include href="app/views/footer.xsl" />

	<xsl:template name="title">
		<title><xsl:value-of select="//str:title" /></title>
	</xsl:template>

	<xsl:template name="metatags">
	</xsl:template>

	<xsl:template name="css">
		<link rel="stylesheet" type="text/css" media="screen" href="/inc/styles/layout-home.css" />
	</xsl:template>

	<xsl:template name="styles">
	</xsl:template>

	<xsl:template name="scripts">
	</xsl:template>

	<xsl:template name="body">
		<div id="page">
			<xsl:call-template name="header" />
			<div id="intro">
				<xsl:call-template name="copy-of"><xsl:with-param name="select" select="//str:intro" /></xsl:call-template>
			</div>
			<ul id="search-engines">
				<xsl:for-each select="//str:search-engines/str:search-engine">
					<li>
						<a>
							<xsl:attribute name="href"><xsl:value-of select="str:link" /></xsl:attribute>
							<xsl:value-of select="str:caption" />
						</a>
					</li>
				</xsl:for-each>
			</ul>
			<xsl:call-template name="footer" />
		</div>
	</xsl:template>

	<xsl:template match="str:*" />

</xsl:stylesheet>
```