Intents to be a bibliography management tool that derives from the database scheme of aigaion v2.1.2.

# Get it running #
## 1. step: config file ##

You need a config file named 'config.php' that you put in the root of this app. The file should look something like that:

```php
<?php
// MySQL connection data
define('BIBLIOGRAPHIE_MYSQL_HOST', 'host');
define('BIBLIOGRAPHIE_MYSQL_USER', 'user');
define('BIBLIOGRAPHIE_MYSQL_PASSWORD', 'password');
define('BIBLIOGRAPHIE_MYSQL_DATABASE', 'database');

// Root path of bibliographie without ending slash.
define('BIBLIOGRAPHIE_WEB_ROOT', '/bibliographie');

// Minimum of chars needed to start a search. Should be the same as the minimum length of MySQL fulltext index length.
define('BIBLIOGRAPHIE_SEARCH_MIN_CHARS', 4);

// Configuration for the tag cloud.
define('BIBLIOGRAPHIE_TAG_SIZE_FACTOR', 100);
define('BIBLIOGRAPHIE_TAG_SIZE_MINIMUM', 10);
define('BIBLIOGRAPHIE_TAG_SIZE_FLATNESS', 40);

// If you have a key for ISBNDB.com put it here.
define('BIBLIOGRAPHIE_ISBNDB_KEY', '');

// One of 'errors', 'all' or false
define('BIBLIOGRAPHIE_DATABASE_DEBUG', false);

// Wether to use caching or not. Highly recommended for large databases.
define('BIBLIOGRAPHIE_CACHING', true);
```

## 2. Step ##

You need a server side directory authentication, e.g. via apaches .htaccess. And the appropriate authentication names in the database table `a2users` with the names in the `login` field.
If you have the user 'foobar' in your .htaccess file, you'll need a row in the `a2users` table with the login field having the value 'foobar'.

## 3. Step ##

Access the app via a browser at the path you set in the config file. Follow the instructions to convert/create the database.

## Rech the finish line ##

All done... You can now start using bibliographie...

# 3rd party libraries #
This is a list of stuff that i didn't handcraft myself but took from other nice people because their software suits my needs.

* jQuery http://www.jquery.com/
* jQuery UI http://www.jquery-ui.com/
* jGrowl http://plugins.jquery.com/project/jGrowl
* jQuery TokenInput http://loopj.com/jquery-tokeninput/

## Adjust 3rd party libraries ##
From file `resources/javascript/jquery.tokeninput.js` remove all lines where it says `cache.add(SOMETHING)`. This is already done in the file that is redistributed with bibliographie.

Additionally at the block (lines 198 to 201)

```js
.blur(function () {
	hide_dropdown();
	$(this).val("");
})
```

remove the line `$(this.val(""));`. This is also already done in the redistributed file.