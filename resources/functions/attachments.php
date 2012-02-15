<?php
/**
 * Get the data of an attachment.
 * @staticvar null $attachment
 * @param int $att_id
 * @return mixed False on error or and object on success.
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
 * Parse the data of an attachment as HTML.
 * @param int $att_id
 * @return string
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
			$return .= '<a href="javascript:;" onclick="bibliographie_attachments_confirm_delete('.$attachment->att_id.')">'.bibliographie_icon_get('cross').'</a>';
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
 * Register an attachment and link it to a publication.
 * @staticvar null $registerAttachment
 * @param int $pub_id
 * @param string $name
 * @param string $location
 * @param string $type
 * @return mixed False on error or and array with attachment's data on success.
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

/**
 * Delete an attachment.
 * @staticvar null $deleteAttachment
 * @param int $att_id
 * @return bool False on error or true on success.
 */
function bibliographie_attachments_delete ($att_id) {
	static $deleteAttachment = null;

	$return = false;

	$attachment = bibliographie_attachments_get_data($att_id);

	if(is_object($attachment)){
		if($deleteAttachment === null)
			$deleteAttachment = DB::getInstance()->prepare('DELETE FROM
	`'.BIBLIOGRAPHIE_PREFIX.'attachments`
WHERE
	`att_id` = :att_id
LIMIT 1');

		$deleteAttachment->bindParam('att_id', $attachment->att_id);
		$return = $deleteAttachment->execute();

		if($return){
			bibliographie_cache_purge();
			if(file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/attachments/'.$attachment->location))
				unlink(BIBLIOGRAPHIE_ROOT_PATH.'/attachments/'.$attachment->location);
			bibliographie_log('attachments', 'deleteAttachment', json_encode(array('dataDeleted' => $attachment)));
		}
	}

	return $return;
}