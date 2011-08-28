Intents to be a bibliography management tool that derives from the database scheme of aigaion2.

# Get it running #
## Config file ##

All you need is a yet existent aigaion2 database and a config file named 'config.php' that you put in the root of this app.
The file should look something like that:

```php
<?php
define('BIBLIOGRAPHIE_MYSQL_HOST', 'host');
define('BIBLIOGRAPHIE_MYSQL_USER', 'user');
define('BIBLIOGRAPHIE_MYSQL_PASSWORD', 'password');
define('BIBLIOGRAPHIE_MYSQL_DATABASE', 'database');

define('BIBLIOGRAPHIE_WEB_ROOT', '/bibliographie');

define('BIBLIOGRAPHIE_CACHING', true);
```

## Drop unnecessary aigaion2 tables ##
Since we do not implement such neat user priviliges we don't need all of that stuff!

* a2aigaiongeneral
* a2availablerights
* a2changehistory
* a2config
* a2grouprightsprofilelink
* a2logintegration
* a2rightsprofilerightlink
* a2rightsprofiles
* a2usergrouplink
* a2userrights

```sql
DROP TABLE a2aigaiongeneral, a2availablerights, a2changehistory, a2config, a2grouprightsprofilelink, a2logintegration, a2rightsprofilerightlink, a2rightsprofiles, a2usergrouplink, a2userrights
```

## Change existing tables ##
To make the code consistent and more straight forward we want to rename the a2keywords to a2tags

### Rename keywords to tags ##

```sql
ALTER TABLE `a2keywords` RENAME TO `a2tags`, CHANGE COLUMN `keyword_id` `tag_id` INT(10) NOT NULL AUTO_INCREMENT FIRST, CHANGE COLUMN `keyword` `tag` MEDIUMTEXT NOT NULL AFTER `tag_id`;
ALTER TABLE `a2publicationkeywordlink` RENAME TO `a2publicationtaglink`, CHANGE COLUMN `keyword_id` `tag_id` INT(10) NOT NULL AFTER `pub_id`;
```

### Alter publication table ###

```sql
ALTER TABLE `a2publication` ADD FULLTEXT INDEX `fulltext_title` (`title`);
```

### Alter topic table ###

```sql
ALTER TABLE `a2topics`  ADD FULLTEXT INDEX `fulltext` (`name`, `description`);
```

### Alter author table ###

```sql
ALTER TABLE `a2author`  ADD FULLTEXT INDEX `fulltext` (`surname`, `firstname`);
```

## Add new tables ##
This is a new table that we need to cross reference with the file log.

```sql
CREATE TABLE `log` (
	`log_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`log_file` TEXT NOT NULL,
	`log_time` TEXT NOT NULL,
	PRIMARY KEY (`log_id`)
) COLLATE='utf8_general_ci' ENGINE=MyISAM;
```

This is a new table that we need to lock tables against editing.

```sql
CREATE TABLE `lockedtables` (
	`topic_id` INT(10) UNSIGNED NOT NULL
)
COLLATE='utf8_general_ci'
ENGINE=MyISAM
```

## 3rd party libraries ##
This is a list of stuff that i didn't handcraft myself but took from other nice people because their software suits my needs.

* jQuery http://www.jquery.com/
* jQuery UI http://www.jquery-ui.com/
* jGrowl http://plugins.jquery.com/project/jGrowl
* jQuery TokenInput http://loopj.com/jquery-tokeninput/

### Adjust 3rd party libraries ###
From file `resources/javascript/jquery.tokeninput.js` remove all lines where it says `cache.add(SOMETHING)`. This is already done in the file that is distributed with bibliographie.