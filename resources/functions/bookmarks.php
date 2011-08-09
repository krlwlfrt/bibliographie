<?php
/**
 * Set a bookmark for a publication for the logged in user.
 * @param int $pub_id
 * @return bool
 */
function bibliographie_bookmarks_set_bookmark ($pub_id) {
	return mysql_query("INSERT INTO `a2userbookmarklists` (
	`user_id`,
	`pub_id`
) VALUES (
	".((int) bibliographie_user_get_id()).",
	".((int) $pub_id)."
)");
}

/**
 * Clear a specific bookmark for the logged in user.
 * @param int $pub_id
 * @return bool
 */
function bibliographie_bookmarks_unset_bookmark ($pub_id) {
	mysql_query("DELETE FROM `a2userbookmarklists` WHERE `user_id` = ".((int) bibliographie_user_get_id())." AND `pub_id` = ".((int) $pub_id));
	return (bool) mysql_affected_rows();
}

/**
 * Clear all bookmarks for the logged in user.
 * @return bool
 */
function bibliographie_bookmarks_clear_bookmarks () {
	mysql_query("DELETE FROM `a2userbookmarklists` WHERE `user_id` = ".((int) bibliographie_user_get_id()));
	return (bool) mysql_affected_rows();
}

/**
 * Check if a user has set a bookmark for a publication.
 * @param int $pub_id
 */
function bibliographie_bookmarks_check_publication ($pub_id) {
	return (bool) mysql_num_rows(mysql_query("SELECT * FROM `a2userbookmarklists` WHERE `user_id` = ".((int) bibliographie_user_get_id())." AND `pub_id` = ".((int) $pub_id)));
}

/**
 * Print the snippet of html that is needed for bookmark interaction.
 * @param int $pub_id
 */
function bibliographie_bookmarks_print_html ($pub_id) {
	$str = '<div id="bibliographie_bookmark_container_'.((int) $pub_id).'" class="bibliographie_bookmark_container">';

	if(bibliographie_bookmarks_check_publication($pub_id)){
		$str .= '<a href="javascript:;" onclick="bibliographie_bookmarks_unset_bookmark('.((int) $pub_id).')">'.bibliographie_icon_get('tag-blue-delete').' unbookmark</a>';
	}else{
		$str .= '<a href="javascript:;" onclick="bibliographie_bookmarks_set_bookmark('.((int) $pub_id).')">'.bibliographie_icon_get('tag-blue-add').' bookmark</a>';
	}

	$str .= '</div>';

	return $str;
}

/**
 * Print the javascript that is needed for bookmark interactions.
 */
function bibliographie_bookmarks_print_javascript () {
?>

<script type="text/javascript">
	/* <![CDATA[ */
function bibliographie_bookmarks_set_bookmark (pub_id) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/bookmarks/ajax.php'?>',
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
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/bookmarks/ajax.php'?>',
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
	/* ]]> */
</script>
<?php
}