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
		if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/attachment_'.((int) $att_id).'.json'))
			return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/attachment_'.((int) $att_id).'.json'));

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

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/attachment_'.((int) $att_id).'.json', 'w+');
			fwrite($cacheFile, json_encode($return));
			fclose($cacheFile);
		}
	}

	return $return;
}

/**
 *
 * @param type $att_id
 */
function bibliographie_attachments_parse ($att_id) {
	$attachment = bibliographie_attachments_get_data($att_id);

	$return = false;

	if(is_object($attachment)){

		if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/attachment_'.($attachment->att_id).'_parsed.html'))
			return file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/attachment_'.($attachment->att_id).'_parsed.html');

		if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/attachments/'.$attachment->location)){
			$return = '<div class="bibliographie_attachment">';
			$return .= '<div style="float: right;">';
			$return .= bibliographie_icon_get('cross');
			$return .= '</div>';
			$return .= '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/attachments/'.$attachment->location.'">';
			$return .= bibliographie_icon_get('disk');
			$return .= ' ';
			$return .= $attachment->name;
			$return .= '</a> (';
			$return .= $attachment->mime;
			$return .= ') ';
			$return .= round(filesize(BIBLIOGRAPHIE_ROOT_PATH.'/attachments/'.$attachment->location) / 1024, 2);
			$return .= 'KByte<br />uploaded by: ';
			$return .= bibliographie_user_get_name($attachment->user_id);
			$return .= '</div>';
		}else
			$return = '<p class="error">This file does not exist! ('.$attachment->location.')</p>';

		if(BIBLIOGRAPHIE_CACHING){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/attachment_'.($attachment->att_id).'_parsed.html', 'w+');
			fwrite($cacheFile, $return);
			fclose($cacheFile);
		}
	}

	return $return;
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
function bibliographie_attachments_register ($pub_id, $name, $location, $type, $att_id = null, $user_id = null) {
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
	`att_id`,
	`pub_id`,
	`user_id`,
	`location`,
	`name`,
	`mime`
) VALUES (
	:att_id,
	:pub_id,
	:user_id,
	:location,
	:name,
	:mime
)');

		if($user_id === null)
			$user_id = bibliographie_user_get_id();

		$data = array (
			'att_id' => $att_id,
			'pub_id' => $publication->pub_id,
			'user_id' => $user_id,
			'location' => $location,
			'name' => $name,
			'mime' => $type
		);

		$registerAttachment->execute($data);

		if($registerAttachment->rowCount() == 1){
			if($att_id === null)
				$data['att_id'] = DB::getInstance()->lastInsertId();

			bibliographie_log('attachments', 'registerAttachment', json_encode($data));
			$return = $data;
		}
	}

	return $return;
}