<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

?>

<h2>Authors</h2>
<?php
$title = 'Authors';
switch($_GET['task']){
	case 'authorEditor':
		$created = false;
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$errors = array();

			if(empty($_POST['surname']) or empty($_POST['firstname']))
				$errors[] = 'You have to fill first name and last name!';

			if(!empty($_POST['url']) and !is_url($_POST['url']))
				$errors[] = 'The URL you filled is not valid.';

			if(!empty($_POST['email']) and !is_mail($_POST['email']))
				$errors[] = 'The mail address you filled is not valid.';

			if(count($errors) == 0){
				if(bibliographie_authors_create_author($_POST['firstname'], $_POST['von'], $_POST['surname'], $_POST['jr'], $_POST['email'], $_POST['url'], $_POST['institute'])){
					echo '<p class="success">Author has been created!</p>';
					$created = true;
				}else
					echo '<p class="error">Author could not have been created. '.mysql_error().'</p>';
			}else
				foreach($errors as $error)
					echo '<p class="error">'.$error.'</p>';
		}

		if(!$created){
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=createAuthor'?>" method="post">
	<div class="unit">
		<div style="float: right; padding-left: 10px; width: 10%;">
			<label for="jr" class="block">jr-part</label>
			<input type="text" id="jr" name="jr" value="<?php echo htmlspecialchars($_POST['jr'])?>" style="width: 100%" tabindex="4" />
		</div>
		<div style="float: right; padding-left: 10px; width: 40%;">
			<label for="surname" class="block">Last name(s)*</label>
			<input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($_POST['surname'])?>" style="width: 100%" tabindex="3" />
		</div>
		<div style="float: right; padding-left: 10px; width: 10%;">
			<label for="von" class="block">von-part</label>
			<input type="text" id="von" name="von" value="<?php echo htmlspecialchars($_POST['von'])?>" style="width: 100%" tabindex="2" />
		</div>

		<label for="firstname" class="block">First name(s)*</label>
		<input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($_POST['firstname'])?>" style="width: 35%" tabindex="1" />
	</div>

	<div class="unit">
		<div style="float: right; width: 50%;">
			<label for="url" class="block">URL</label>
			<input type="text" id="url" name="url" value="<?php echo htmlspecialchars($_POST['url'])?>" style="width: 100%" tabindex="6" />
		</div>

		<label for="email" class="block">Mail address</label>
		<input type="text" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'])?>" style="width: 45%" tabindex="5" />
	</div>

	<div class="unit">
		<label for="institute" class="block">Institute</label>
		<input type="text" id="institute" name="institute" value="<?php echo htmlspecialchars($_POST['institute'])?>" style="width: 100%" tabindex="7" />
	</div>

	<div class="submit">
		<input type="submit" value="save" tabindex="8"  />
	</div>
</form>
<?php
		}
	break;

	case 'showAuthor':
		$author = mysql_query("SELECT * FROM `a2author` WHERE `author_id` = ".((int) $_GET['author_id']));
		if(mysql_num_rows($author) == 1){
			$author = mysql_fetch_object($author);
?>

<em style="float: right;"><a href="/bibliographie/authors/?task=authorEditor&amp;author:id=<?php echo ((int) $author->author_id)?>">Edit author</a></em>
<h3><?php echo bibliographie_authors_parse_data($author)?></h3>
<ul>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/?task=showPublications&amp;author_id=<?php echo ((int) $author->author_id)?>&amp;asEditor=0">Show publications as author (<?php echo count(bibliographie_authors_get_publications($author->author_id, 0))?>)</a></li>
	<li><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/?task=showPublications&amp;author_id=<?php echo ((int) $author->author_id)?>&amp;asEditor=1">Show publications as editor (<?php echo count(bibliographie_authors_get_publications($author->author_id, 1))?>)</a></li>
</ul>

<?php
		}
	break;

	case 'showPublications':
		$author = mysql_query("SELECT * FROM `a2author` WHERE `author_id` = ".((int) $_GET['author_id']));
		if(mysql_num_rows($author) == 1){
			$author = mysql_fetch_object($author);
?>

<h3>Publications of <?php echo bibliographie_authors_parse_data($author)?></h3>
<?php
			$publications = bibliographie_authors_get_publications($_GET['author_id'], $_GET['asEditor']);

			if(count($publications) > 0)
				bibliographie_publications_print_list($publications, BIBLIOGRAPHIE_WEB_ROOT.'/authors/?task=showPublications&author_id='.((int) $_GET['author_id'].'&asEditor='.((int) $_GET['asEditor'])));
			else
				echo '<p class="error">This author has no publications!</p>';
		}
	break;

	case 'showList':
		$initialsResult = mysql_query("SELECT * FROM (SELECT UPPER(SUBSTRING(`surname`, 1, 1)) AS `initial`, COUNT(*) AS `count` FROM `a2author` GROUP BY `initial` ORDER BY `initial`) initials WHERE `initial` REGEXP '[ABCDEFGHIJKLMNOPQRSTUVWXYZ]'");

		$miscResult = mysql_num_rows(mysql_query("SELECT * FROM (SELECT UPPER(SUBSTRING(`surname`, 1, 1)) AS `initial` FROM `a2author`) initials WHERE `initial` NOT REGEXP '[ABCDEFGHIJKLMNOPQRSTUVWXYZ]'"));

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
		$authorsResult = mysql_query("SELECT * FROM `a2author` ".$whereClause." ORDER BY `surname`, `firstname`");

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