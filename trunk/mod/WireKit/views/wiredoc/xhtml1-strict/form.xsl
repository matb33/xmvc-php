<xsl:stylesheet version="1.0" exclude-result-prefixes="xhtml xmvc component meta container group nav reference inject doc sitemap form interact" xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0" xmlns:component="urn:wirekit:component" xmlns:meta="urn:wirekit:meta" xmlns:container="urn:wirekit:container" xmlns:group="urn:wirekit:group" xmlns:nav="urn:wirekit:nav" xmlns:reference="urn:wirekit:reference" xmlns:inject="urn:wirekit:inject" xmlns:doc="urn:wirekit:doc" xmlns:sitemap="urn:wirekit:sitemap" xmlns:form="urn:wirekit:form" xmlns:interact="urn:wirekit:interact">

	<xsl:template match="form:field[ @type = 'text' or @type = 'password' or @type = 'file' ]" priority="0">
		<xsl:variable name="name" select="@name" />
		<label for="{ @name }" class="{ @name } { @type }">
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'before' ]" />
			<input type="{ @type }" id="{ @name }" name="{ @name }" class="{ @name } { @type }">
				<xsl:choose>
					<xsl:when test="//xmvc:strings/xmvc:*[ @key = $name ]">
						<xsl:attribute name="value"><xsl:value-of select="//xmvc:strings/xmvc:*[ @key = $name ]" /></xsl:attribute>
					</xsl:when>
					<xsl:when test="form:value">
						<xsl:attribute name="value"><xsl:value-of select="form:value[ lang( $lang ) ]" /></xsl:attribute>
					</xsl:when>
				</xsl:choose>
			</input>
			<xsl:apply-templates select="form:info" />
			<xsl:apply-templates select="form:label[ @position = 'after' ]" />
		</label>
		<xsl:for-each select=".//form:math-captcha">
			<input type="hidden" id="{ @name }" name="{ @name }" value="{ @answer-md5 }" />
		</xsl:for-each>
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field[ @type = 'textarea' ]" priority="0">
		<xsl:variable name="name" select="@name" />
		<label for="{ @name }" class="{ @name } { @type }">
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'before' ]" />
			<textarea id="{ @name }" name="{ @name }" class="{ @name } { @type }"><xsl:choose>
				<xsl:when test="//xmvc:strings/xmvc:*[ @key = $name ]"><xsl:value-of select="//xmvc:strings/xmvc:*[ @key = $name ]" /></xsl:when>
				<xsl:when test="form:value"><xsl:value-of select="form:value[ lang( $lang ) ]" /></xsl:when>
			</xsl:choose></textarea>
			<xsl:apply-templates select="form:label[ @position = 'after' ]" />
			<xsl:apply-templates select="form:info" />
		</label>
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field[ @type = 'submit' or @type = 'reset' or @type = 'button' ]" priority="0">
		<input type="{ @type }" name="{ @name }" id="{ @name }" class="{ @name } { @type }">
			<xsl:if test="form:label[ lang( $lang ) ]">
				<xsl:attribute name="value"><xsl:value-of select="form:label[ lang( $lang ) ]" /></xsl:attribute>
			</xsl:if>
		</input>
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field[ @type = 'checkbox' or @type = 'radio' ]" priority="0">
		<input type="hidden" name="{ @name }" class="{ @name } { @type }" />
		<xsl:apply-templates select="form:option" />
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field//form:option[ ancestor::form:field[1]/@type = 'checkbox' or ancestor::form:field[1]/@type = 'radio' ]" priority="0">
		<xsl:variable name="name" select="ancestor::form:field[1]/@name" />
		<xsl:variable name="type" select="ancestor::form:field[1]/@type" />
		<label for="{ $name }-{ position() }" class="{ $name } { $type }">
			<xsl:apply-templates select="form:label[ @position = 'before' ]" />
			<input type="{ $type }" id="{ $name }-{ position() }" name="{ $name }[]" class="{ $name } { $type }">
				<xsl:if test="form:value">
					<xsl:attribute name="value"><xsl:value-of select="form:value[ lang( $lang ) ]" /></xsl:attribute>
					<xsl:choose>
						<xsl:when test="form:checked">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:when>
						<xsl:when test="contains( //xmvc:strings/xmvc:*[ @key = $name ], concat( '|', form:value[ lang( $lang ) ], '|' ) )">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:when>
						<xsl:otherwise />
					</xsl:choose>
				</xsl:if>
			</input>
			<xsl:apply-templates select="form:info" />
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'after' ]" />
		</label>
	</xsl:template>

	<xsl:template match="form:field[ @type = 'select' ]" priority="0">
		<label for="{ @name }" class="{ @name } { @type }">
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'before' ]" />
			<select name="{ @name }" id="{ @name }" class="{ @name } { @type }">
				<xsl:apply-templates select="*[ name() != 'form:label' and name() != 'form:info' and name() != 'form:constraint' ]" />
			</select>
			<xsl:apply-templates select="form:label[ @position = 'after' ]" />
			<xsl:apply-templates select="form:info" />
		</label>
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field[ @type = 'multi-select' ]" priority="0">
		<label for="{ @name }" class="{ @name } { @type }">
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'before' ]" />
			<select name="{ @name }[]" id="{ @name }" multiple="true" class="{ @name } { @type }">
				<xsl:apply-templates select="*[ name() != 'form:label' and name() != 'form:info' and name() != 'form:constraint' ]" />
			</select>
			<xsl:apply-templates select="form:label[ @position = 'after' ]" />
			<xsl:apply-templates select="form:info" />
		</label>
		<xsl:apply-templates select="form:constraint" />
	</xsl:template>

	<xsl:template match="form:field//form:option[ ancestor::form:field[1]/@type = 'select' or ancestor::form:field[1]/@type = 'multi-select' ]" priority="0">
		<xsl:variable name="name" select="ancestor::form:field[1]/@name" />
		<xsl:variable name="type" select="ancestor::form:field[1]/@type" />
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
			<xsl:apply-templates select="form:label[ @position = 'before' ]" />
			<xsl:if test="form:value">
				<xsl:attribute name="value"><xsl:value-of select="form:value[ lang( $lang ) ]" /></xsl:attribute>
				<xsl:choose>
					<xsl:when test="$type = 'select'">
						<xsl:choose>
							<xsl:when test="form:selected">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:when>
							<xsl:when test="//xmvc:strings/xmvc:*[ @key = $name ] = form:value[ lang( $lang ) ]">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:when>
							<xsl:otherwise />
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="contains( //xmvc:strings/xmvc:*[ @key = $name ], concat( '|', form:value[ lang( $lang ) ], '|' ) )">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
			<xsl:apply-templates select="form:label[ not( @position ) or @position = 'after' ]" />
		</option>
	</xsl:template>

	<xsl:template match="form:field//form:group" priority="0">
		<optgroup>
			<xsl:if test="form:label[ lang( $lang ) ]">
				<xsl:attribute name="label"><xsl:value-of select="form:label[ lang( $lang ) ]" /></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="*[ name() != 'form:label' ]" />
		</optgroup>
	</xsl:template>

	<xsl:template match="form:field[ @type = 'checkbox' ]//form:option/form:label" priority="3">
		<xsl:if test="lang( $lang )">
			<span><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="form:field//form:option/form:label" priority="2">
		<xsl:if test="lang( $lang )">
			<xsl:apply-templates />
		</xsl:if>
	</xsl:template>

	<xsl:template match="form:field//form:label" priority="1">
		<xsl:if test="lang( $lang )">
			<span><xsl:apply-templates /></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="form:field//form:info" priority="1">
		<xsl:if test="lang( $lang )">
			<input type="hidden" class="info" value="{ text() }" />
		</xsl:if>
	</xsl:template>

	<xsl:template match="form:fieldset" priority="0">
		<fieldset id="{ @name }" class="{ @name }">
			<xsl:apply-templates />
		</fieldset>
	</xsl:template>

	<xsl:template match="form:fieldset/form:legend" priority="1">
		<xsl:if test="lang( $lang )">
			<legend><xsl:apply-templates /></legend>
		</xsl:if>
	</xsl:template>

	<xsl:template match="form:field/form:constraint" priority="0">
		<xsl:if test="substring( @type, 1, 6 ) = 'match-'">
			<input type="hidden" name="{ ../@name }--dependency[]" value="{ @against }" />
		</xsl:if>
	</xsl:template>

	<xsl:template match="form:*" priority="0">
		<form>
			<xsl:if test="@href">
				<xsl:attribute name="action"><xsl:value-of select="@href" /></xsl:attribute>
			</xsl:if>
			<xsl:attribute name="method">
				<xsl:choose>
					<xsl:when test="@method"><xsl:value-of select="@method" /></xsl:when>
					<xsl:otherwise>post</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:if test="@enctype">
				<xsl:attribute name="enctype"><xsl:value-of select="@enctype" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="local-name() != 'form'">
				<xsl:attribute name="class"><xsl:value-of select="local-name()" /></xsl:attribute>
			</xsl:if>
			<xsl:if test="@id">
				<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
			</xsl:if>
			<div class="form">
				<xsl:apply-templates />
			</div>
		</form>
	</xsl:template>

</xsl:stylesheet>