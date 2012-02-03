<?php
/**
 * Get a list of the bookmarks of the currently logged in user.
 * @return array
 */
function bibliographie_bookmarks_get_bookmarks () {
	static $bookmarks = null;

	$return = array();

	if(BIBLIOGRAPHIE_CACHING and file_exists(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bookmarks_'.((int) bibliographie_user_get_id()).'.json'))
		return json_decode(file_get_contents(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bookmarks_'.((int) bibliographie_user_get_id()).'.json'));

	if($bookmarks === null)
		$bookmarks = DB::getInstance()->prepare('SELECT publications.`pub_id` FROM
		`'.BIBLIOGRAPHIE_PREFIX.'userbookmarklists` bookmarks,
		`'.BIBLIOGRAPHIE_PREFIX.'publication` publications
	WHERE
		publications.`pub_id` = bookmarks.`pub_id` AND
		bookmarks.`user_id` = :user_id
	ORDER BY
		publications.`year` DESC');

	$bookmarks->execute(array(
		'user_id' => (int) bibliographie_user_get_id()
	));

	if($bookmarks->rowCount() > 0)
		$return = $bookmarks->fetchAll(PDO::FETCH_COLUMN, 0);

	if(BIBLIOGRAPHIE_CACHING){
		$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bookmarks_'.((int) bibliographie_user_get_id()).'.json', 'w+');
		fwrite($cacheFile, json_encode($return));
		fclose($cacheFile);
	}

	return $return;
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
	$return = false;
	if(count(bibliographie_bookmarks_get_bookmarks()) > 0){
		$return = DB::getInstance()->exec('DELETE FROM `'.BIBLIOGRAPHIE_PREFIX.'userbookmarklists` WHERE `user_id` = '.((int) bibliographie_user_get_id()));

		if($return > 0){
			$cacheFile = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/bookmarks_'.((int) bibliographie_user_get_id()).'.json', 'w+');
			fwrite($cacheFile, '[]');
			fclose($cacheFile);
		}
	}

	return $return;
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
	$publication = bibliographie_publications_get_data($pub_id);
	$str = (string) '';

	if(is_object($publication)){
		$str .= '<div id="bibliographie_bookmark_container_'.((int) $pub_id).'" class="bibliographie_bookmark_container">';

		if(bibliographie_bookmarks_check_publication($pub_id)){
			$str .= '<a href="javascript:;" onclick="bibliographie_bookmarks_unset_bookmark('.((int) $pub_id).')">'.bibliographie_icon_get('cross', 'Unset bookmark').'</a>';
		}else{
			$str .= '<a href="javascript:;" onclick="bibliographie_bookmarks_set_bookmark('.((int) $pub_id).')">'.bibliographie_icon_get('star', 'Set bookmark').'</a>';
		}

		$str .= '&nbsp;<a href="javascript:;" onclick="bibliographie_publications_export_choose_type(\''.bibliographie_publications_cache_list(array($pub_id)).'\')">'.bibliographie_icon_get('page-white-go', 'Export publication').'</a>';

		$str .= '</div>';
	}

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

$(function () {
	$('span.bibliographie_publications_order_trigger').on('mouseover', function (event) {
		var id = $(event.target).attr('id');
		$('#'+id+'_selector')
			.css('display', 'block')
			.css('position', 'absolute')
			.css('left', event.target.offsetLeft)
			.css('top', event.target.offsetTop - 10);
	});
});

<?php
	if($withHTML)
		echo '/* ]]> */ </script>';
}

/**
 *
 * @staticvar string $bookmark
 * @param array $publications
 * @return type
 */
function bibliographie_bookmarks_set_bookmarks_for_list (array $publications) {
	static $bookmark = null;

	$return = false;

	if(count($publications) > 0){
		$return = 0;
		$notBookmarkedPublications = array_diff($publications, bibliographie_bookmarks_get_bookmarks());
		if(count($notBookmarkedPublications) > 0){
			$return = count($notBookmarkedPublications);
			try {
				DB::getInstance()->beginTransaction();

				foreach($notBookmarkedPublications as $pub_id){
					if($bookmark === null)
						$bookmark = DB::getInstance()->prepare('INSERT INTO `'.BIBLIOGRAPHIE_PREFIX.'userbookmarklists` (
	`user_id`,
	`pub_id`
) VALUES (
	:user_id,
	:pub_id
)');

					$bookmark->execute(array(
						'user_id' => (int) bibliographie_user_get_id(),
						'pub_id' => (int) $pub_id
					));
				}

				DB::getInstance()->commit();

				bibliographie_cache_purge('bookmarks_'.((int) bibliographie_user_get_id()));
			} catch (PDOException $e) {
				echo '<p class="error">There was an error trying to set the bookmarks!</p><p>'.$e->getMessage().'</p>';
			}
		}
	}

	return $return;
}

/**
 *
 * @param array $publications
 * @return type
 */
function bibliographie_bookmarks_unset_bookmarks_for_list (array $publications) {
	static $unbookmark = null;

	$return = false;

	if(count($publications)){
		$return = 0;

		$bookmarkedPublications = array_intersect($publications, bibliographie_bookmarks_get_bookmarks());

		if(count($bookmarkedPublications) > 0){
			if($unbookmark === null)
				$unbookmark = DB::getInstance()->prepare('DELETE FROM
	`'.BIBLIOGRAPHIE_PREFIX.'userbookmarklists`
WHERE
	`user_id` = :user_id AND
	FIND_IN_SET(`pub_id`, :set)');

			$unbookmark->execute(array(
				'user_id' => (int) bibliographie_user_get_id(),
				'set' => array2csv($bookmarkedPublications)
			));

			$return = $unbookmark->rowCount();

			if($return > 0)
				bibliographie_cache_purge('bookmarks_'.((int) bibliographie_user_get_id()));
		}
	}

	return $return;
}