<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';

?>

<h2>Authors</h2>
<?php
$bibliographie_title = 'Authors';
switch($_GET['task']){
	case 'deleteAuthor':
		$person = bibliographie_authors_get_data($_GET['author_id']);

		if(is_object($person)){
			$publications = array_unique(array_merge(bibliographie_authors_get_publications($person->author_id, false), bibliographie_authors_get_publications($person->author_id, true)));
			if(count($publications) == 0){
				echo '<h3>Deleting <em>'.bibliographie_authors_parse_data($person->author_id).'</em></h3>';
				if(bibliographie_authors_delete($person->author_id))
					echo '<p class="success">Person was successfully deleted!</p>';
				else
					echo '<p class="error">An error occured!</p>';
			}else
				echo '<p class="error"><em>'.bibliographie_authors_parse_data($person->author_id).'</em> has '.count($publications).' publications and can therefore not be deleted!</p>';
		}else
			echo '<p class="error">Person could not be found!</p>';

		bibliographie_history_append_step('authors', 'Delete author', false);
	break;

	case 'authorEditor':
		$done = false;

		$author = bibliographie_authors_get_data($_GET['author_id']);

		if(is_object($author))
			bibliographie_history_append_step('authors', 'Editing author '.bibliographie_authors_parse_data($author->author_id));
		else
			bibliographie_history_append_step('authors', 'Author editor');

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$errors = array();

			if(empty($_POST['surname']) or empty($_POST['firstname']))
				$errors[] = 'You have to fill first name and last name!';

			if(!empty($_POST['url']) and !is_url($_POST['url']))
				$errors[] = 'The URL you filled is not valid.';

			if(!empty($_POST['email']) and !is_mail($_POST['email']))
				$errors[] = 'The mail address you filled is not valid.';

			if(count($errors) == 0){
				if(is_object($author)){
					$return = bibliographie_authors_edit_author($author->author_id, $_POST['firstname'], $_POST['von'], $_POST['surname'], $_POST['jr'], $_POST['email'], $_POST['url'], $_POST['institute']);
					if(is_array($return)){
						echo '<p class="success">Author has been edited!</p>';
						echo '<p>You can proceed by viewing the author\'s <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&amp;author_id='.((int) $return['dataAfter']['author_id']).'">profile</a> or going back to the <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=authorEditor">editor</a>.</p>';
						$done = true;
					}else
						echo '<p class="error">Author could not be edited.</p>';

				}else{
					$return = bibliographie_authors_create_author($_POST['firstname'], $_POST['von'], $_POST['surname'], $_POST['jr'], $_POST['email'], $_POST['url'], $_POST['institute']);
					if(is_array($return)){
						echo '<p class="success">Author has been created!</p>';
						echo '<p>You can proceed by viewing the author\'s <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showAuthor&amp;author_id='.((int) $return['author_id']).'">profile</a> or going back to the <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=authorEditor">editor</a>.</p>';
						$done = true;
					}else
						echo '<p class="error">Author could not be created.</p>';
				}
			}else
				foreach($errors as $error)
					echo '<p class="error">'.$error.'</p>';
		}

		if(!$done){
?>

<h3>Author editor</h3>
<p class="notice">On this page you can create and edit authors! Just fill in the required fields and hit save!</p>
<?php
			if(is_object($author)){
				$_POST = (array) $author;
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=authorEditor&amp;author_id='.((int) $_POST['author_id'])?>" method="post">
<?php
			}else{
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=authorEditor'?>" method="post">
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
			if(is_object($author))
				echo (int) $author->author_id;
			else
				echo 0;
?>;
$(function () {
	$('#content input, #content textarea').charmap();

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
			bibliographie_history_append_step('authors', 'Showing author '.bibliographie_authors_parse_data($author->author_id));

			$publications = array_unique(array_merge(bibliographie_authors_get_publications($author->author_id, 0), bibliographie_authors_get_publications($author->author_id, 0)));
			bibliographie_authors_get_publications($author->author_id);
			$tagsArray = array();
			$topicsArray = array();
?>

<em style="float: right;">
	<a href="/bibliographie/authors/?task=authorEditor&amp;author_id=<?php echo ((int) $author->author_id)?>"><?php echo bibliographie_icon_get('user-edit')?> Edit</a>
	<a href="javascript:;" onclick="bibliographie_authors_confirm_delete(<?php echo $author->author_id?>)"><?php echo bibliographie_icon_get('user-delete')?> Delete</a>
</em>
<h3><?php echo bibliographie_authors_parse_data($author)?></h3>
<ul>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/?task=showPublications&amp;author_id=<?php echo ((int) $author->author_id)?>&amp;asEditor=0">Show publications as author (<?php echo count(bibliographie_authors_get_publications($author->author_id, 0))?>)</a></li>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/?task=showPublications&amp;author_id=<?php echo ((int) $author->author_id)?>&amp;asEditor=1">Show publications as editor (<?php echo count(bibliographie_authors_get_publications($author->author_id, 1))?>)</a></li>
</ul>
<?php
			$tagsArray = bibliographie_authors_get_tags($author->author_id);
			if(is_array($tagsArray) and count($tagsArray) > 0){
?>

<h4>Tags of publications</h4>
<?php
				bibliographie_tags_print_cloud(bibliographie_authors_get_tags($author->author_id), array('author_id' => $author->author_id));
			}
		}
	break;

	case 'showPublications':
		$author = bibliographie_authors_get_data($_GET['author_id']);
		if(is_object($author)){
			bibliographie_history_append_step('authors', 'Show publications of author '.bibliographie_authors_parse_data($author->author_id).' (page '.((int) $_GET['page']).')');
?>

<h3>Publications of <?php echo bibliographie_authors_parse_data($author->author_id, array('linkProfile' => true))?></h3>
<?php
			$publications = bibliographie_authors_get_publications($_GET['author_id'], $_GET['asEditor']);
			bibliographie_publications_print_list($publications, BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showPublications&author_id='.((int) $_GET['author_id'].'&asEditor='.((int) $_GET['asEditor'])), $_GET['bookmarkBatch']);
		}
	break;

	case 'showList':
		$authors = DB::getInstance()->query('SELECT * FROM `a2author`');
		$initials = DB::getInstance()->query('SELECT * FROM (
	SELECT UPPER(
		SUBSTRING(`surname`, 1, 1)
	) AS `initial`, COUNT(*) AS `count`
	FROM
		`a2author`
	GROUP BY
		`initial`
	ORDER BY
		`initial`
) initials
WHERE
	`initial` REGEXP "[ABCDEFGHIJKLMNOPQRSTUVWXYZ]"');

		if($authors->rowCount() > 0){
			$initials->setFetchMode();
			$initialsArray = $initials->fetchAll();
			if($authors->rowCount() != $initials->rowCount())
				$initialsArray[] = array (
					'initial' => 'Misc',
					'count' => $authors->rowCount() - $initials->rowCount()
				);

			echo '<p class="bibliographie_pages"><strong>Initials:</strong>';
			if(empty($_GET['initial']))
				$_GET['initial'] = 'A';

			$selectedInitial = (string) '';
			foreach($initialsArray as $initial){
				if($_GET['initial'] != $initial['initial']){
					echo ' <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showList&amp;initial='.$initial['initial'].'" title="'.$initial['count'].' persons">['.$initial['initial'].']</a>';
				}else{
					echo ' <strong>['.$initial['initial'].']</strong>';
					$selectedInitial = $initial['initial'];
				}
			}
			echo '</p>';

			$showAuthors = null;
			if(in_array($selectedInitial, array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'))){
				$showAuthors = DB::getInstance()->prepare('SELECT
	`author_id`, `surname`, `firstname`
FROM
	`a2author`
WHERE
	UPPER(
		SUBSTRING(`surname`, 1, 1)
	) = :initial
ORDER BY `surname`, `firstname`');
				$showAuthors->bindParam('initial', $selectedInitial);
			}else{
				$showAuthors = DB::getInstance()->prepare('SELECT
	`author_id`, `surname`, `firstname`
FROM
	`a2author`
WHERE
	UPPER(
		SUBSTRING(`surname`, 1, 1)
	) NOT REGEXP "[ABCDEFGHIJKLMNOPQRSTUVWXYZ]"
ORDER BY `surname`, `firstname`
');
			}
			$showAuthors->execute();

			if($showAuthors->rowCount() > 0){
				$showAuthors->setFetchMode(PDO::FETCH_OBJ);
				$authorsArray = $showAuthors->fetchAll();
?>

<table class="dataContainer">
	<tr>
		<th>Surname</th>
		<th>Firstname</th>
	</tr>
<?php
				foreach($authorsArray as $author){
					$name = bibliographie_authors_parse_data($author, array('splitNames' => true));
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
			}else
				echo '<h3 class="error">No authors in the list!</h3>';

			bibliographie_history_append_step('authors', 'Showing list of authors (selection '.htmlspecialchars($selectedInitial).')');
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';