<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Tags</h2>
<?php
switch($_GET['task']){
	case 'showTag':
		$tag = bibliographie_tags_get_data($_GET['tag_id']);
		if($tag){
?>

<span style="float: right">
	<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/tags/?task=showTag&tag_id=<?php echo ((int) $_GET['tag_id'])?>&bookmarkBatch=add">Bookmark</a>
	<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/tags/?task=showTag&tag_id=<?php echo ((int) $_GET['tag_id'])?>&bookmarkBatch=remove">Unbookmark</a>
	all
</span>

<h3>Publications assigned to <?php echo htmlspecialchars($tag->tag)?></h3>
<?php
			$publications = bibliographie_tags_get_publications($tag->tag_id);
			if(count($publications) > 0)
				bibliographie_publications_print_list($publications, BIBLIOGRAPHIE_WEB_ROOT.'/tags/?task=showTag&amp;tag_id='.((int) $_GET['tag_id']), $_GET['bookmarkBatch']);
			else
				echo '<p class="error">No publications are assigned to this tag!</p>';
		}
	break;

	case 'showCloud':
?>

<h3>Tag cloud</h3>
<?php
		$tagsResult = mysql_query("SELECT occurrences.`tag_id`, `tag`, `count` FROM `a2tags` tags, (SELECT `tag_id`, COUNT(*) AS `count` FROM `a2publicationtaglink` GROUP BY `tag_id`) occurrences WHERE tags.`tag_id` = occurrences.`tag_id` ORDER BY `tag` ASC");

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

				//if($size > BIBLIOGRAPHIE_TAG_SIZE_MINIMUM){
?>

	<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/tags/?task=showTag&amp;tag_id=<?php echo $tag->tag_id?>" style="font-size: <?php echo round($size, 2).'px'?>; line-height: <?php echo $size.'px'?>;padding: 10px; text-transform: lowercase;" title="<?php echo $tag->count?> publications"><?php echo $tag->tag?></a>
<?php
				//}
			}
?>

</div>
<?php
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';