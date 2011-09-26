<?php
/**
 * Get a list of the bookmarks of the currently logged in user.
 * @return array
 */
function bibliographie_bookmarks_get_bookmarks () {
	if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bookmarks_'.((int) bibliographie_user_get_id()).'.json'))
		return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bookmarks_'.((int) bibliographie_user_get_id()).'.json'));

	$bookmarks = array();
	$bookmarksResult = _mysql_query("SELECT publications.`pub_id` FROM
		`a2userbookmarklists` bookmarks,
		`a2publication` publications
	WHERE
		publications.`pub_id` = bookmarks.`pub_id` AND
		bookmarks.`user_id` = ".((int) bibliographie_user_get_id()."
	ORDER BY
		publications.`year` DESC"));

	if(mysql_num_rows($bookmarksResult) > 0)
		while($bookmark = mysql_fetch_object($bookmarksResult))
			$bookmarks[] = $bookmark->pub_id;

	if(BIBLIOGRAPHIE_CACHING){
		$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bookmarks_'.((int) bibliographie_user_get_id()).'.json', 'w+');
		fwrite($cacheFile, json_encode($bookmarks));
		fclose($cacheFile);
	}

	return $bookmarks;
}

/**
 * Set a bookmark for a publication for the logged in user.
 * @param int $pub_id
 * @return bool
 */
function bibliographie_bookmarks_set_bookmark ($pub_id) {
	return (bool) bibliographie_bookmarks_set_bookmarks_for_list(array($pub_id));
}

/**
 * Clear a specific bookmark for the logged in user.
 * @param int $pub_id
 * @return bool
 */
function bibliographie_bookmarks_unset_bookmark ($pub_id) {
	return (bool) bibliographie_bookmarks_unset_bookmarks_for_list(array($pub_id));
}

/**
 * Clear all bookmarks for the logged in user.
 * @return bool
 */
function bibliographie_bookmarks_clear_bookmarks () {
	if(count(bibliographie_bookmarks_get_bookmarks()) > 0){
		_mysql_query("DELETE FROM `a2userbookmarklists` WHERE `user_id` = ".((int) bibliographie_user_get_id()));
		$return = mysql_affected_rows();

		if($return){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bookmarks_'.((int) bibliographie_user_get_id()).'.json', 'w+');
			fwrite($cacheFile, '[]');
			fclose($cacheFile);
		}

		return $return;
	}

	return false;
}

/**
 * Check if a user has set a bookmark for a publication.
 * @param int $pub_id
 */
function bibliographie_bookmarks_check_publication ($pub_id) {
	return in_array($pub_id, bibliographie_bookmarks_get_bookmarks());
}

/**
 * Print the snippet of html that is needed for bookmark interaction.
 * @param int $pub_id
 */
function bibliographie_bookmarks_print_html ($pub_id) {
	$str = '<div id="bibliographie_bookmark_container_'.((int) $pub_id).'" class="bibliographie_bookmark_container">';

	if(bibliographie_bookmarks_check_publication($pub_id)){
		$str .= '<a href="javascript:;" onclick="bibliographie_bookmarks_unset_bookmark('.((int) $pub_id).')">'.bibliographie_icon_get('cross').'</a>';
	}else{
		$str .= '<a href="javascript:;" onclick="bibliographie_bookmarks_set_bookmark('.((int) $pub_id).')">'.bibliographie_icon_get('star').'</a>';
	}

	$str .= '</div>';

	return $str;
}

/**
 * Print the javascript that is needed for bookmark interactions.
 */
function bibliographie_bookmarks_print_javascript ($withHTML = true) {
	if($withHTML)
		echo '<script type="text/javascript"> /* <![CDATA[ */';
?>

function bibliographie_bookmarks_set_bookmark (pub_id) {
	$.ajax({
		url: bibliographie_web_root+'/bookmarks/ajax.php',
		data: {
			'task': 'setBookmark',
			'pub_id': pub_id
		},
		success: function (html) {
			$('#bibliographie_bookmark_container_'+pub_id).replaceWith(html);
			$('#publication_container_'+pub_id).addClass('bibliographie_publication_bookmarked');
		}
	})
}

function bibliographie_bookmarks_unset_bookmark (pub_id) {
	$.ajax({
		url: bibliographie_web_root+'/bookmarks/ajax.php',
		data: {
			'task': 'unsetBookmark',
			'pub_id': pub_id
		},
		success: function (html) {
			$('#bibliographie_bookmark_container_'+pub_id).replaceWith(html);
			$('#publication_container_'+pub_id).removeClass('bibliographie_publication_bookmarked');
		}
	})
}

<?php
	if($withHTML)
		echo '/* ]]> */ </script>';
}

/**
 * Set bookmarks for a given list of publications.
 * @param array $list
 * @return mixed Amount of set bookmarks or false on error.
 */
function bibliographie_bookmarks_set_bookmarks_for_list (array $list) {
	if(count($list) > 0){
		$mysqlString = "";

		$list = array_diff($list, bibliographie_bookmarks_get_bookmarks());
		if(count($list) > 0)
			foreach($list as $pub_id){
				if(!empty($mysqlString))
					$mysqlString .= ",";
				$mysqlString .= "(".((int) bibliographie_user_get_id()).",".((int) $pub_id).")";
			}

		$return = 0;
		if(!empty($mysqlString)){
			_mysql_query("INSERT INTO `a2userbookmarklists` (`user_id`,`pub_id`) VALUES ".$mysqlString.";");
			$return = mysql_affected_rows();
		}

		bibliographie_purge_cache('bookmarks_'.((int) bibliographie_user_get_id()));

		return $return;
	}

	return false;
}

/**
 * Unset bookmarks for a given list of publications
 * @param array $list
 * @return mixed Amount of unset bookmarks or false on error.
 */
function bibliographie_bookmarks_unset_bookmarks_for_list (array $list) {
	if(count($list)){
		$list = array_intersect($list, bibliographie_bookmarks_get_bookmarks());
		$return = 0;
		if(count($list) > 0){
			_mysql_query("DELETE FROM `a2userbookmarklists` WHERE `user_id` = ".((int) bibliographie_user_get_id())." AND FIND_IN_SET(`pub_id`, '".implode(',', $list)."')");
			$return = mysql_affected_rows();
		}

		if($return > 0)
			bibliographie_purge_cache('bookmarks_'.((int) bibliographie_user_get_id()));

		return $return;
	}

	return false;
}