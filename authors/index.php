<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
require dirname(__FILE__).'/authors.php';

?>

<h2>Authors</h2>
<?php

switch($_GET['task']){
	case 'createAuthor':
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
				if(bibliographie_authors_create_author($_POST['firstname'], $_POST['von'], $_POST['surname'], $_POST['jr'], $_POST['email'], $_POST['url'], $_POST['instiute'])){
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

	case 'showList':
?>

<h3>List of authors</h3>
<?php
		$authors = mysql_query("SELECT * FROM `a2author` ORDER BY `surname`, `firstname`");
		if(mysql_num_rows($authors) > 0){
?>

<table class="dataContainer">
	<tr>
		<th>Surname</th>
		<th>Firstname</th>
	</tr>
<?php
			while($author = mysql_fetch_object($authors)){
				$author->surname = '<strong>'.$author->surname.'</strong>';

				if(!empty($author->von))
					$author->surname = $author->von.' '.$author->surname;

				if(!empty($author->jr))
					$author->surname = $author->surname.' '.$author->jr;
?>

	<tr>
		<td><?php echo $author->surname?></td>
		<td><?php echo $author->firstname?></td>
	</tr>
<?php
			}
			echo '</table>';
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';