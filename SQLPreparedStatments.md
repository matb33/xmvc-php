# SQL Prepared Statements #

Example of a complete SQL Model.

```

<?xml version="1.0" encoding="utf-8" ?>
<xmvc:database xmlns:xmvc="http://www.xmvc.org/ns/xmvc/1.0">
<xmvc:prepared-statements>
<xmvc:query xmvc:name="AddEntry">
<xmvc:sql><![CDATA[
INSERT INTO entries
(
created,
modified,
firstname,
lastname,
email,
remote_addr
)
VALUES ( NOW(), NOW(), ?, ?, ?, ? )
]]>

Unknown end tag for &lt;/sql&gt;




Unknown end tag for &lt;/query&gt;


<xmvc:query xmvc:name="GetInsertID">
<xmvc:sql><![CDATA[
SELECT
LAST_INSERT_ID() AS insert_id
]]>

Unknown end tag for &lt;/sql&gt;




Unknown end tag for &lt;/query&gt;




Unknown end tag for &lt;/prepared-statements&gt;


<xmvc:schema>
<xmvc:sql><![CDATA[
CREATE TABLE IF NOT EXISTS entries (
entryID int(11) NOT NULL auto_increment,
created datetime default NULL,
modified datetime default NULL,
firstname varchar(255) default NULL,
lastname varchar(255) default NULL,
email varchar(255) default NULL,
remote_addr varchar(255) default NULL,
PRIMARY KEY  (entryID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
]]>

Unknown end tag for &lt;/sql&gt;




Unknown end tag for &lt;/schema&gt;




Unknown end tag for &lt;/database&gt;


```