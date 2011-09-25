<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';
?>

<h2>Tags</h2>
<?php
switch($_GET['task']){
	case 'showTag':
		$tag = bibliographie_tags_get_data($_GET['tag_id']);
		if(is_object($tag)){
			$options = array();
			if(is_numeric($_GET['author_id']) and bibliographie_authors_get_data($_GET['author_id'])){
				$author = bibliographie_authors_get_data($_GET['author_id']);
				$options = array('author_id' => $_GET['author_id']);
				echo '<h3>Publications of '.bibliographie_authors_parse_data($author->author_id, array('linkProfile' => true)).' tagged with <em>'.htmlspecialchars($tag->tag).'</em></h3>';
			}elseif(is_numeric($_GET['topic_id']) and bibliographie_topics_get_data($_GET['topic_id'])){
				$topic = bibliographie_topics_get_data($_GET['topic_id']);
				$options = array('topic_id' => $_GET['topic_id']);
				echo '<h3>Publications in <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/topics/?task=showTopic&amp;topic_id='.((int) $topic->topic_id).'">'.$topic->name.'</a> tagged with <em>'.htmlspecialchars($tag->tag).'</em></h3>';
			}else
				echo '<h3>Publications tagged with <em>'.htmlspecialchars($tag->tag).'</em></h3>';

			bibliographie_publications_print_list(
				bibliographie_tags_get_publications($tag->tag_id, $options),
				BIBLIOGRAPHIE_WEB_ROOT.'/tags/?task=showTag&amp;tag_id='.((int) $_GET['tag_id']),
				$_GET['bookmarkBatch']
			);
		}
	break;

	case 'showCloud':
?>

<h3>Tag cloud</h3>
<?php
		$tagsResult = _mysql_query("SELECT occurrences.`tag_id`, `tag`, `count` FROM `a2tags` tags, (SELECT `tag_id`, COUNT(*) AS `count` FROM `a2publicationtaglink` GROUP BY `tag_id`) occurrences WHERE tags.`tag_id` = occurrences.`tag_id` ORDER BY `tag` ASC");

		if(mysql_num_rows($tagsResult) > 0){
?>

<div id="bibliographie_tag_cloud" style="border: 1px solid #aaa; border-radius: 20px; font-size: 0.8em; text-align: center; padding: 20px;">
<?php
			while($tag = mysql_fetch_object($tagsResult)){
				/**
				 * Converges against BIBLIOGRAPHIE_TAG_SIZE_FACTOR.
				 */
				$size = BIBLIOGRAPHIE_TAG_SIZE_FACTOR * $tag->count / ($tag->count + BIBLIOGRAPHIE_TAG_SIZE_FLATNESS);
				$size = ($size < BIBLIOGRAPHIE_TAG_SIZE_MINIMUM) ? BIBLIOGRAPHIE_TAG_SIZE_MINIMUM : $size;
?>

	<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/tags/?task=showTag&amp;tag_id=<?php echo $tag->tag_id?>" style="font-size: <?php echo round($size, 2).'px'?>; line-height: <?php echo $size.'px'?>;padding: 10px; text-transform: lowercase;" title="<?php echo $tag->count?> publications"><?php echo $tag->tag?></a>
<?php
			}
?>

</div>
<?php
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';