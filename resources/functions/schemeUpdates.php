<?php
$bibliographie_database_updates = array (
	2 => array(
		'description' => 'Remove unused columns from `users` table.',
		'query' => 'ALTER TABLE `'.BIBLIOGRAPHIE_PREFIX.'users`  CHANGE COLUMN `login` `login` VARCHAR(100) NOT NULL AFTER `user_id`,  ADD COLUMN `last_access` DATETIME NOT NULL AFTER `login`,  DROP COLUMN `theme`,  DROP COLUMN `password_invalidated`,  DROP COLUMN `newwindowforatt`,  DROP COLUMN `summarystyle`,  DROP COLUMN `authordisplaystyle`,  DROP COLUMN `liststyle`,  DROP COLUMN `password`,  DROP COLUMN `initials`,  DROP COLUMN `firstname`,  DROP COLUMN `betweenname`,  DROP COLUMN `surname`,  DROP COLUMN `csname`,  DROP COLUMN `abbreviation`,  DROP COLUMN `email`,  DROP COLUMN `u_rights`,  DROP COLUMN `lastreviewedtopic`,  DROP COLUMN `type`,  DROP COLUMN `lastupdatecheck`,  DROP COLUMN `exportinbrowser`,  DROP COLUMN `utf8bibtex`,  DROP COLUMN `language`,  DROP COLUMN `similar_author_test`,  ADD UNIQUE INDEX `login` (`login`);'
	),
	3 => array(
		'description' => 'Removing unnessecary column `log_file` from table `log`.',
		'query' => 'ALTER TABLE `'.BIBLIOGRAPHIE_PREFIX.'log` DROP COLUMN `log_file`;'
	)
);