<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xmvc wd php"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0"
	xmlns:wd="http://www.wiredoc.org/ns/wiredoc/2.0"
	xmlns:php="http://php.net/xsl">

	<xsl:template match="wd:form//wd:field[ @type = 'text' or @type = 'password' or @type = 'file' ]" priority="0">
		<xsl:variable name="name" select="@name" />
		<label for="{ @name }" class="{ @name } { @type }">
			<xsl:apply-templates select="wd:label[ not( @position ) or @position = 'before' ]" mode="lang-check" />
			<input type="{ @type }" id="{ @name }" name="{ @name }" class="{ @name } { @type }">
				<xsl:choose>
					<xsl:when test="//xmvc:strings/xmvc:*[ @key = $name ]">
						<xsl:attribute name="value"><xsl:value-of select="//xmvc:strings/xmvc:*[ @key = $name ]" /></xsl:attribute>
					</xsl:when>
					<xsl:when test="wd:value">
						<xsl:attribute name="value"><xsl:value-of select="wd:value[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" /></xsl:attribute>
					</xsl:when>
				</xsl:choose>
			</input>
			<xsl:apply-templates select="wd:info" mode="lang-check" />
			<xsl:apply-templates select="wd:label[ @position = 'after' ]" mode="lang-check" />
			<xsl:for-each select=".//wd:math-captcha">
				<input type="hidden" id="{ @name }" name="{ @name }" value="{ @answer-md5 }" />
			</xsl:for-each>
			<xsl:apply-templates select="wd:constraint" mode="lang-check" />
		</label>
	</xsl:template>

	<xsl:template match="wd:form//wd:field[ @type = 'textarea' ]" priority="0">
		<xsl:variable name="name" select="@name" />
		<label for="{ @name }" class="{ @name } { @type }">
			<xsl:apply-templates select="wd:label[ not( @position ) or @position = 'before' ]" mode="lang-check" />
			<textarea id="{ @name }" name="{ @name }" class="{ @name } { @type }"><xsl:choose>
				<xsl:when test="//xmvc:strings/xmvc:*[ @key = $name ]"><xsl:value-of select="//xmvc:strings/xmvc:*[ @key = $name ]" /></xsl:when>
				<xsl:when test="wd:value"><xsl:value-of select="wd:value[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" /></xsl:when>
			</xsl:choose></textarea>
			<xsl:apply-templates select="wd:label[ @position = 'after' ]" mode="lang-check" />
			<xsl:apply-templates select="wd:info" mode="lang-check" />
			<xsl:apply-templates select="wd:constraint" mode="lang-check" />
		</label>
	</xsl:template>

	<xsl:template match="wd:form//wd:field[ @type = 'submit' or @type = 'reset' or @type = 'button' ]" priority="0">
		<input type="{ @type }" name="{ @name }" id="{ @name }" class="{ @name } { @type }">
			<xsl:if test="wd:label[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]">
				<xsl:attribute name="value"><xsl:value-of select="wd:label[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" /></xsl:attribute>
			</xsl:if>
		</input>
		<xsl:apply-templates select="wd:constraint" mode="lang-check" />
	</xsl:template>

	<xsl:template match="wd:form//wd:field[ @type = 'checkbox' or @type = 'radio' ]" priority="0">
		<input type="hidden" name="{ @name }" class="{ @name } { @type }" />
		<xsl:apply-templates select="wd:option" mode="lang-check" />
		<xsl:apply-templates select="wd:constraint" mode="lang-check" />
	</xsl:template>

	<xsl:template match="wd:form//wd:field//wd:option[ ancestor::wd:field[1]/@type = 'checkbox' or ancestor::wd:field[1]/@type = 'radio' ]" priority="0">
		<xsl:variable name="name" select="ancestor::wd:field[1]/@name" />
		<xsl:variable name="type" select="ancestor::wd:field[1]/@type" />
		<label for="{ $name }-{ position() }" class="{ $name } { $type }">
			<xsl:apply-templates select="wd:label[ @position = 'before' ]" mode="lang-check" />
			<input type="{ $type }" id="{ $name }-{ position() }" name="{ $name }[]" class="{ $name } { $type }">
				<xsl:if test="wd:value">
					<xsl:attribute name="value"><xsl:value-of select="wd:value[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" /></xsl:attribute>
					<xsl:choose>
						<xsl:when test="wd:checked">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:when>
						<xsl:when test="contains( //xmvc:strings/xmvc:*[ @key = $name ], concat( '|', wd:value[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ], '|' ) )">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:when>
						<xsl:otherwise />
					</xsl:choose>
				</xsl:if>
			</input>
			<xsl:apply-templates select="wd:info" mode="lang-check" />
			<xsl:apply-templates select="wd:label[ not( @position ) or @position = 'after' ]" mode="lang-check" />
		</label>
	</xsl:template>

	<xsl:template match="wd:form//wd:field[ @type = 'select' ]" priority="0">
		<label for="{ @name }" class="{ @name } { @type }">
			<xsl:apply-templates select="wd:label[ not( @position ) or @position = 'before' ]" mode="lang-check" />
			<select name="{ @name }" id="{ @name }" class="{ @name } { @type }">
				<xsl:apply-templates select="wd:*[ name() != 'wd:label' and name() != 'wd:info' and name() != 'wd:constraint' ]" mode="lang-check" />
			</select>
			<xsl:apply-templates select="wd:label[ @position = 'after' ]" mode="lang-check" />
			<xsl:apply-templates select="wd:info" mode="lang-check" />
			<xsl:apply-templates select="wd:constraint" mode="lang-check" />
		</label>
	</xsl:template>

	<xsl:template match="wd:form//wd:field[ @type = 'multi-select' ]" priority="0">
		<label for="{ @name }" class="{ @name } { @type }">
			<xsl:apply-templates select="wd:label[ not( @position ) or @position = 'before' ]" mode="lang-check" />
			<select name="{ @name }[]" id="{ @name }" multiple="true" class="{ @name } { @type }">
				<xsl:apply-templates select="wd:*[ name() != 'wd:label' and name() != 'wd:info' and name() != 'wd:constraint' ]" mode="lang-check" />
			</select>
			<xsl:apply-templates select="wd:label[ @position = 'after' ]" mode="lang-check" />
			<xsl:apply-templates select="wd:info" mode="lang-check" />
			<xsl:apply-templates select="wd:constraint" mode="lang-check" />
		</label>
	</xsl:template>

	<xsl:template match="wd:form//wd:field//wd:option[ ancestor::wd:field[1]/@type = 'select' or ancestor::wd:field[1]/@type = 'multi-select' ]" priority="0">
		<xsl:variable name="name" select="ancestor::wd:field[1]/@name" />
		<xsl:variable name="type" select="ancestor::wd:field[1]/@type" />
		<option>
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="last() = 1">first-child last-child</xsl:when>
					<xsl:when test="position() = 1">first-child</xsl:when>
					<xsl:when test="position() = last()">last-child</xsl:when>
					<xsl:otherwise>middle-child</xsl:otherwise>
				</xsl:choose>
				<xsl:text> item-</xsl:text><xsl:value-of select="position()" />
				<xsl:text> </xsl:text>
				<xsl:choose>
					<xsl:when test="position() mod 2 = 1">even</xsl:when>
					<xsl:otherwise>odd</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:apply-templates select="wd:label[ @position = 'before' ]" mode="lang-check" />
			<xsl:if test="wd:value">
				<xsl:attribute name="value"><xsl:value-of select="wd:value[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" /></xsl:attribute>
				<xsl:choose>
					<xsl:when test="$type = 'select'">
						<xsl:choose>
							<xsl:when test="wd:selected">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:when>
							<xsl:when test="//xmvc:strings/xmvc:*[ @key = $name ] = wd:value[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:when>
							<xsl:otherwise />
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="contains( //xmvc:strings/xmvc:*[ @key = $name ], concat( '|', wd:value[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ], '|' ) )">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
			<xsl:apply-templates select="wd:label[ not( @position ) or @position = 'after' ]" mode="lang-check" />
		</option>
	</xsl:template>

	<xsl:template match="wd:form//wd:field//wd:option-group" priority="0">
		<optgroup>
			<xsl:if test="wd:label[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]">
				<xsl:attribute name="label"><xsl:value-of select="wd:label[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', $lang, (ancestor-or-self::*/@xml:lang)[last()] ) ]" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="wd:*[ name() != 'wd:label' ]" mode="lang-check" />
		</optgroup>
	</xsl:template>

	<xsl:template match="wd:form//wd:field[ @type = 'checkbox' ]//wd:option/wd:label" priority="3">
		<span><xsl:apply-templates mode="lang-check" /></span>
	</xsl:template>

	<xsl:template match="wd:form//wd:field//wd:option/wd:label" priority="2">
		<xsl:apply-templates mode="lang-check" />
	</xsl:template>

	<xsl:template match="wd:form//wd:field//wd:label" priority="1">
		<span><xsl:apply-templates mode="lang-check" /></span>
	</xsl:template>

	<xsl:template match="wd:form//wd:field//wd:info" priority="1">
		<input type="hidden" class="info" value="{ text() }" />
	</xsl:template>

	<xsl:template match="wd:form//wd:fieldset" priority="0">
		<fieldset id="{ @name }" class="{ @name }">
			<xsl:apply-templates mode="lang-check" />
		</fieldset>
	</xsl:template>

	<xsl:template match="wd:form//wd:fieldset/wd:legend" priority="1">
		<legend><xsl:apply-templates mode="lang-check" /></legend>
	</xsl:template>

	<xsl:template match="wd:form//wd:field/wd:constraint" priority="0">
		<xsl:if test="substring( @type, 1, 6 ) = 'match-'">
			<input type="hidden" name="{ ../@name }--dependency[]" value="{ @against }" />
		</xsl:if>
		<xsl:apply-templates mode="lang-check" />
	</xsl:template>

	<xsl:template match="wd:*[ starts-with( local-name(), 'form' ) ]" priority="0">
		<form>
			<xsl:attribute name="action">
				<xsl:choose>
					<xsl:when test="@href"><xsl:value-of select="@href" /></xsl:when>
					<xsl:otherwise>./</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:attribute name="method">
				<xsl:choose>
					<xsl:when test="@method"><xsl:value-of select="@method" /></xsl:when>
					<xsl:otherwise>post</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:if test="@enctype">
				<xsl:attribute name="enctype"><xsl:value-of select="@enctype" /></xsl:attribute>
			</xsl:if>
			<xsl:choose>
				<xsl:when test="@wd:name">
					<xsl:attribute name="class"><xsl:value-of select="@wd:name" /></xsl:attribute>
				</xsl:when>
				<xsl:when test="starts-with( local-name(), 'form.' )">
					<xsl:attribute name="class"><xsl:value-of select="substring( local-name(), 6 )" /></xsl:attribute>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<div class="form">
				<xsl:apply-templates mode="lang-check" />
			</div>
		</form>
	</xsl:template>

</xsl:stylesheet>