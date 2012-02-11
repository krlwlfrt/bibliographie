<?php
/**
 *
 * @staticvar null $attachment
 * @param type $att_id
 * @return type
 */
function bibliographie_attachments_get_data ($att_id) {
	static $attachment = null;

	$return = false;

	if(is_numeric($att_id)){
		if(!($attachment instanceof PDOStatement))
			$attachment = DB::getInstance()->prepare('SELECT
	`pub_id`,
	`att_id`,
	`user_id`,
	`location`,
	`name`,
	`mime`,
	`note`,
	`ismain`
FROM
	`'.BIBLIOGRAPHIE_PREFIX.'attachments`
WHERE
	`att_id` = :att_id
LIMIT 1');

		$attachment->execute(array(
			'att_id' => (int) $att_id
		));

		if($attachment->rowCount() == 1)
			$return = $attachment->fetch(PDO::FETCH_OBJ);
	}

	return $return;
}

/**
 *
 * @param type $att_id
 */
function bibliographie_attachments_parse ($att_id) {
	$attachment = bibliographie_attachments_get_data($att_id);

	if(is_object($attachment)){
		if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/attachments/'.$attachment->location)){
		echo '<div class="bibliographie_attachment">',
			'<div style="float: right;">',
			bibliographie_icon_get('cross'),
			'</div>',
			'<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/attachments/'.$attachment->location.'">',
			bibliographie_icon_get('disk'),
			' ',
			$attachment->name,
			'</a> (',
			$attachment->mime,
			') ',
			round(filesize(BIBLIOGRAPHIE_ROOT_PATH.'/attachments/'.$attachment->location) / 1024, 2),
			'KByte<br />uploaded by: ',
			bibliographie_user_get_name($attachment->user_id),
			'</div>';
		}else
			echo '<p class="error">This file does not exist! ('.$attachment->location.')</p>';
	}
}

/**
 *
 * @staticvar null $registerAttachment
 * @param type $pub_id
 * @param type $name
 * @param type $location
 * @param type $type
 * @return boolean
 */
function bibliographie_attachments_register ($pub_id, $name, $location, $type) {
	static $registerAttachment = null;

	$return = false;

	$publication = bibliographie_publications_get_data($pub_id);

	if(is_object($publication)
		and !mb_strpos($location, '..')
		and !mb_strpos($location, '/')
		and !mb_strpos($location, '\\')
		and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/attachments/'.$location)){

		if(!($registerAttachment instanceof PDOStatement))
			$registerAttachment = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'attachments` (
	`pub_id`,
	`user_id`,
	`location`,
	`name`,
	`mime`
) VALUES (
	:pub_id,
	:user_id,
	:location,
	:name,
	:mime
)');

		$data = array (
			'pub_id' => $publication->pub_id,
			'user_id' => bibliographie_user_get_id(),
			'location' => $location,
			'name' => $name,
			'mime' => $type
		);

		$registerAttachment->execute($data);

		if($registerAttachment->rowCount() == 1){
			$data['att_id'] = DB::getInstance()->lastInsertId();
			$return = $data;
		}
	}

	return $return;
}