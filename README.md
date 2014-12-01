#Example

```php
<?php

require 'Seumka.php';

$api = new xf3\Seumka('my_username', 'my_password);

$sites = $api->listsites();

$api->getphrasesjson(array('siteid' => $sites['sites'][0]['siteid']));


```