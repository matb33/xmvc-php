# SQL Model Driver #

To use the built in SQL Model Driver you must first include the class in the your controllers prelog ( header ).

```
use xMVC\Sys\SQLModelDriver;```

Next create a new instance of the SQL Model Driver citing the relative path to the Model containing the [SQL Prepared Statments](SQLPreparedStatments.md). UseQuery to select the specific query from the Model and Execute.

```

$entry = new SQLModelDriver( "queries/query-model" );
$entry->UseQuery( "PreparedQueryName" );
$entry->Execute();
```

Optionally you can set parameters to the query by using
```

$entry->SetParameters( "Parameter one", "Param 2" );
```

All of which would finally look something like the following code sniplet/
```

public function Send()
{
$queryData = array();
$queryData[] = trim( $_POST[ "firstname" ] );
$queryData[] = trim( $_POST[ "lastname" ] );
$queryData[] = trim( $_POST[ "email" ] );
$queryData[] = $_SERVER[ "REMOTE_ADDR" ];

$entry = new SQLModelDriver( "queries/contact-us" );
$entry->UseQuery( "AddEntry" );
$entry->SetParameters( $queryData );
$entry->Execute();

if( $entry->IsSuccessful() )
{
header( "HTTP/1.1 302 Found\r\n" );
header( "Location: /contact-us/thanks/\r\n" );
}
else
{
header( "HTTP/1.1 302 Found\r\n" );
header( "Location: /contact-us/error/\r\n" );
}
}
```