<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

?>

<h2>Authors</h2>
<?php
$title = 'Authors';
switch($_GET['task']){
	case 'authorEditor':
		$done = false;

		$author = bibliographie_authors_get_data($_GET['author_id'], 'assoc');

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$errors = array();

			if(empty($_POST['surname']) or empty($_POST['firstname']))
				$errors[] = 'You have to fill first name and last name!';

			if(!empty($_POST['url']) and !is_url($_POST['url']))
				$errors[] = 'The URL you filled is not valid.';

			if(!empty($_POST['email']) and !is_mail($_POST['email']))
				$errors[] = 'The mail address you filled is not valid.';

			if(count($errors) == 0){
				if(is_array($author)){
					if(bibliographie_authors_edit_author()){
					}

				}else{
					if(bibliographie_authors_create_author($_POST['firstname'], $_POST['von'], $_POST['surname'], $_POST['jr'], $_POST['email'], $_POST['url'], $_POST['institute'])){
						echo '<p class="success">Author has been created!</p>';
						$done = true;
					}else
						echo '<p class="error">Author could not have been created. '.mysql_error().'</p>';
				}
			}else
				foreach($errors as $error)
					echo '<p class="error">'.$error.'</p>';
		}

		if(!$done){
?>

<p class="notice">On this page you can create and edit authors! Just fill in the required fields and hit save!</p>

<?php
			if(is_array($author)){
				$_POST = $author;
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=createAuthor&amp;author_id='.((int) $_POST['author_id'])?>" method="post">
<?php
			}else{
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=createAuthor'?>" method="post">
<?php
			}
?>
	<div class="unit">
		<div style="float: right; padding-left: 10px; width: 10%;">
			<label for="jr" class="block">jr-part</label>
			<input type="text" id="jr" name="jr" value="<?php echo htmlspecialchars($_POST['jr'])?>" style="width: 100%" tabindex="4" />
		</div>
		<div style="float: right; padding-left: 10px; width: 40%;">
			<label for="surname" class="block"><span class="silk-icon silk-icon-asterisk-yellow"></span> Last name(s)</label>
			<input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($_POST['surname'])?>" style="width: 100%" tabindex="3" />
		</div>
		<div style="float: right; padding-left: 10px; width: 10%;">
			<label for="von" class="block">von-part</label>
			<input type="text" id="von" name="von" value="<?php echo htmlspecialchars($_POST['von'])?>" style="width: 100%" tabindex="2" />
		</div>

		<label for="firstname" class="block"><span class="silk-icon silk-icon-asterisk-yellow"></span> First name(s)</label>
		<input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($_POST['firstname'])?>" style="width: 35%" tabindex="1" />

		<div id="similarNameContainer" class="bibliographie_similarity_container" style="max-height: 200px; overflow-y: scroll; width: 40%"></div>
		<br style="clear: both;" />
	</div>

	<div class="unit">
		<label for="email" class="block">Mail address</label>
		<input type="text" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'])?>" style="width: 100%" tabindex="5" />

		<label for="url" class="block">URL</label>
		<input type="text" id="url" name="url" value="<?php echo htmlspecialchars($_POST['url'])?>" style="width: 100%" tabindex="6" />

		<label for="institute" class="block">Institute</label>
		<input type="text" id="institute" name="institute" value="<?php echo htmlspecialchars($_POST['institute'])?>" style="width: 100%" tabindex="7" />
	</div>

	<div class="submit">
		<input type="submit" value="save" tabindex="8"  />
	</div>
</form>

<script type="text/javascript">
	/* <![CDATA[ */
var author_id = <?php
			if(is_array($author))
				echo ((int) $author['author_id']);
			else
				echo 0;
?>;
$(function () {
	$('input, textarea').charmap();
	$('#bibliographie_charmap').dodge();

	$('#firstname, #surname').bind('keyup change', function (event) {
		delayRequest('bibliographie_authors_check_name', Array($('#firstname').val(), $('#surname').val()));
	});

	if(author_id != 0)
		bibliographie_authors_check_name($('#firstname').val(), $('#surname').val(), author_id);
});
</script>
<?php
			bibliographie_charmap_print_charmap();
		}
	break;

	case 'showAuthor':
		$author = bibliographie_authors_get_data($_GET['author_id']);

		if(is_object($author)){
			$publications = array_unique(array_merge(bibliographie_authors_get_publications($author->author_id, 0), bibliographie_authors_get_publications($author->author_id, 0)));
			$tagsArray = array();
			if(count($publications) > 0){
				$where_clause = (string) "";
				foreach($publications as $publication){
					if(!empty($where_clause))
						$where_clause .= " OR ";

					$where_clause .= "`pub_id` = ".((int) $publication);
				}

				$tags = _mysql_query("SELECT *, COUNT(*) AS `count` FROM `a2publicationtaglink` link LEFT JOIN (
	SELECT * FROM `a2tags`
) AS data ON link.`tag_id` = data.`tag_id` WHERE ".$where_clause." GROUP BY data.`tag_id`");

				if(mysql_num_rows($tags))
					while($tag = mysql_fetch_object($tags))
						$tagsArray[] = $tag;
			}
?>

<em style="float: right;"><a href="/bibliographie/authors/?task=authorEditor&amp;author_id=<?php echo ((int) $author->author_id)?>">Edit author</a></em>
<h3><?php echo bibliographie_authors_parse_data($author)?></h3>
<ul>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/?task=showPublications&amp;author_id=<?php echo ((int) $author->author_id)?>&amp;asEditor=0">Show publications as author (<?php echo count(bibliographie_authors_get_publications($author->author_id, 0))?>)</a></li>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/?task=showPublications&amp;author_id=<?php echo ((int) $author->author_id)?>&amp;asEditor=1">Show publications as editor (<?php echo count(bibliographie_authors_get_publications($author->author_id, 1))?>)</a></li>
</ul>

<?php
			if(count($tagsArray) > 0){
?>

<h4>Publications have the following tags</h4>
<?php
				bibliographie_tags_print_cloud($tagsArray, array('author_id' => $author->author_id));
			}
		}
	break;

	case 'showPublications':
		$author = _mysql_query("SELECT * FROM `a2author` WHERE `author_id` = ".((int) $_GET['author_id']));
		if(mysql_num_rows($author) == 1){
			$author = mysql_fetch_object($author);
?>

<h3>Publications of <?php echo bibliographie_authors_parse_data($author->author_id, array('linkProfile' => true))?></h3>
<?php
			$publications = bibliographie_authors_get_publications($_GET['author_id'], $_GET['asEditor']);
			bibliographie_publications_print_list($publications, BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showPublications&author_id='.((int) $_GET['author_id'].'&asEditor='.((int) $_GET['asEditor'])), $_GET['bookmarkBatch']);
		}
	break;

	case 'showList':
		$initialsResult = _mysql_query("SELECT * FROM (SELECT UPPER(SUBSTRING(`surname`, 1, 1)) AS `initial`, COUNT(*) AS `count` FROM `a2author` GROUP BY `initial` ORDER BY `initial`) initials WHERE `initial` REGEXP '[ABCDEFGHIJKLMNOPQRSTUVWXYZ]'");

		$miscResult = mysql_num_rows(_mysql_query("SELECT * FROM (SELECT UPPER(SUBSTRING(`surname`, 1, 1)) AS `initial` FROM `a2author`) initials WHERE `initial` NOT REGEXP '[ABCDEFGHIJKLMNOPQRSTUVWXYZ]'"));

		$whereClause = "";
		if(empty($_GET['initial'])){
			$_GET['initial'] = 'A';
			$whereClause = " WHERE SUBSTRING(`surname`, 1, 1) = 'A'";
		}

		if(mysql_num_rows($initialsResult) or $miscResult){
?>

<p class="bibliographie_pages">
	<strong>Initials: </strong>
<?php
			while($initial = mysql_fetch_object($initialsResult)){
				if($_GET['initial'] != $initial->initial)
					echo '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showList&initial='.$initial->initial.'" title="'.$initial->count.' persons">['.$initial->initial.']</a>'.PHP_EOL;
				else{
					echo '<strong>['.$initial->initial.']</strong>'.PHP_EOL;
					$whereClause = " WHERE UPPER(SUBSTRING(`surname`, 1, 1)) = '".mysql_real_escape_string(stripslashes($initial->initial))."'";
				}
			}

			if($miscResult){
				if($_GET['initial'] != 'Misc')
					echo '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showList&initial=Misc" title="'.$miscResult.' persons">Misc</a>'.PHP_EOL;
				else{
					echo '<strong>Misc</strong>'.PHP_EOL;
					$whereClause = " WHERE UPPER(SUBSTRING(`surname`, 1, 1)) NOT REGEXP '[ABCDEFGHIJKLMNOPQRSTUVWXYZ]'";
				}
			}
?>

</p>
<?php
		}
?>

<h3>List of authors</h3>
<?php
		$authorsResult = _mysql_query("SELECT * FROM `a2author` ".$whereClause." ORDER BY `surname`, `firstname`");

		if(mysql_num_rows($authorsResult) > 0){
?>

<table class="dataContainer">
	<tr>
		<th>Surname</th>
		<th>Firstname</th>
	</tr>
<?php
			while($author = mysql_fetch_object($authorsResult)){
				$name = bibliographie_authors_parse_data($author, array('splitNames'=>true));
?>

	<tr>
		<td><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/?task=showAuthor&author_id=<?php echo $author->author_id?>"><?php echo $name['surname']?></a></td>
		<td><?php echo $name['firstname']?></td>
	</tr>
<?php
			}
?>

</table>
<script type="text/javascript">
	/* <![CDATA[ */
$('.dataContainer').floatingTableHead();
	/* ]]> */
</script>
<?php
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';