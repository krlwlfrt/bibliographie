<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Publications</h2>
<?php
switch($_GET['task']){
	case 'createPublication':
?>

<h3>Create publication</h3>
<p class="error">Field <em>namekey</em> not used!<br />Field <em>crossref</em> hardly used. Implementation unclear!</p>
<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=createPublication" method="post">
	<h4>General data</h4>
	<div class="unit">
		<div style="float: right; width: 20%;">
			<label for="pub_type" class="block">Publication type*</label>
			<select id="pub_type" name="pub_type" style="width: 100%">
<?php
		foreach($bibliographie_publication_types as $type){
			echo '<option value="'.$type.'"';
			if($type == $_POST['type'])
				echo ' selected="selected"';
			echo '>'.$type.'</option>';
		}
?>

			</select>
		</div>

		<label for="title" class="block">Title*</label>
		<input type="text" id="title" name="title" style="width: 75%" value="<?php echo htmlspecialchars($_POST['title'])?>" />
	</div>

	<h4>Dating</h4>
	<div class="unit">
		<div style="float: right; width: 40%;">
			<label for="year" class="block">Year*</label>
			<input type="number" id="year" name="year" style="width: 100%" value="<?php echo htmlspecialchars($_POST['year'])?>" placeholder="<?php echo date('Y')?>" />
		</div>

		<label for="month" class="block">Month</label>
		<select id="month" name="month" style="width: 55%">
			<option value=""></option>
<?php
		foreach($bibliographie_publication_months as $month){
			echo '<option value="'.$month.'"';
			if($month == $_POST['month'])
				echo ' selected="selected"';
			echo '>'.$month.'</option>';
		}
?>

		</select>
	</div>

	<h4>Association</h4>
	<div class="unit">
		<label for="booktitle" class="block">Booktitle</label>
		<input type="text" id="journal" name="journal" style="width: 100%" value="<?php echo htmlspecialchars($_POST['journal'])?>" />
	</div>

	<div class="unit">
		<div style="float: right; width: 20%;">
			<label for="volume" class="block">Volume</label>
			<input type="text" id="volume" name="volume" style="width: 100%" value="<?php echo htmlspecialchars($_POST['volume'])?>" />

			<label for="number" class="block">Number</label>
			<input type="text" id="number" name="number" style="width: 100%" value="<?php echo htmlspecialchars($_POST['number'])?>" />
		</div>

		<label for="series" class="block">Series <em>for books</em></label>
		<input type="text" id="series" name="series" style="width: 75%" value="<?php echo htmlspecialchars($_POST['series'])?>" />

		<label for="journal" class="block">Journal</label>
		<input type="text" id="journal" name="journal" style="width: 75%" value="<?php echo htmlspecialchars($_POST['journal'])?>" />
	</div>

	<h4>Publishing</h4>
	<div class="unit">
		<label for="publisher" class="block">Publisher</label>
		<input type="text" id="publisher" name="publisher" style="width: 100%" value="<?php echo htmlspecialchars($_POST['publisher'])?>" />

		<label for="location" class="block">Location <em>of publisher</em></label>
		<input type="text" id="location" name="location" style="width: 100%" value="<?php echo htmlspecialchars($_POST['location'])?>" />
	</div>

	<h4>Identification</h4>
	<div class="unit">
		<label for="isbn" class="block">ISBN <em>for books</em></label>
		<input type="text" id="isbn" name="isbn" style="width: 100%" value="<?php echo htmlspecialchars($_POST['isbn'])?>" />

		<label for="issn" class="block">ISSN <em>for journals</em></label>
		<input type="text" id="issn" name="issn" style="width: 100%" value="<?php echo htmlspecialchars($_POST['issn'])?>" />
	</div>

	<div class="unit">
		<label for="doi" class="block">DOI <em>of publication</em></label>
		<input type="text" id="doi" name="doi" style="width: 100%" value="<?php echo htmlspecialchars($_POST['doi'])?>" />

		<label for="url" class="block">URL <em>of publication</em></label>
		<input type="text" id="url" name="url" style="width: 100%" value="<?php echo htmlspecialchars($_POST['url'])?>" />
	</div>

	<h4>Pagination</h4>
	<div class="unit">
		<div style="float: right; padding-left: 10px; width: 20%;">
			<label for="lastpage" class="block">Last page</label>
			<input type="number" id="lastpage" name="lastpage" style="width: 100%" value="<?php echo htmlspecialchars($_POST['lastpage'])?>" />
		</div>

		<div style="float: right; width: 20%;">
			<label for="firstpage" class="block">First page</label>
			<input type="number" id="firstpage" name="firstpage" style="width: 100%" value="<?php echo htmlspecialchars($_POST['firstpage'])?>" />
		</div>

		<label for="pages" class="block">Pages</label>
		<input type="text" id="pages" name="pages" style="width: 55%" value="<?php echo htmlspecialchars($_POST['pages'])?>" />
	</div>

	<h4>Organization</h4>
	<div class="unit">
		<label for="organization" class="block">Organization</label>
		<input type="text" id="organization" name="organization" style="width: 100%" value="<?php echo htmlspecialchars($_POST['organization'])?>" />

		<label for="institution" class="block">Institution</label>
		<input type="text" id="institution" name="institution" style="width: 100%" value="<?php echo htmlspecialchars($_POST['institution'])?>" />

		<label for="school" class="block">School</label>
		<input type="text" id="school" name="school" style="width: 100%" value="<?php echo htmlspecialchars($_POST['school'])?>" />

		<label for="address" class="block">Address</label>
		<input type="text" id="address" name="address" style="width: 100%" value="<?php echo htmlspecialchars($_POST['address'])?>" />
	</div>

	<h4>Misc</h4>
	<div class="unit">
		<label for="chapter" class="block">Chapter</label>
		<input type="text" id="chapter" name="chapter" style="width: 100%" value="<?php echo htmlspecialchars($_POST['chapter'])?>" />

		<label for="edition" class="block">Edition</label>
		<input type="text" id="edition" name="edition" style="width: 100%" value="<?php echo htmlspecialchars($_POST['edition'])?>" />

		<label for="howpublished" class="block">Howpublished</label>
		<input type="text" id="howpublished" name="howpublished" style="width: 100%" value="<?php echo htmlspecialchars($_POST['howpublished'])?>" />
	</div>

	<h4>Descriptional stuff</h4>
	<div class="unit">
		<label for="note" class="block">Note</label>
		<textarea id="note" name="note" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['note'])?></textarea>

		<label for="abstract" class="block">Abstract</label>
		<textarea id="abstract" name="abstract" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['abstract'])?></textarea>

		<label for="userfields" class="block">User fields</label>
		<textarea id="userfields" name="userfields" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['userfields'])?></textarea>
	</div>

	<div class="submit"><input type="submit" value="save" /></div>
</form>
<?php
	break;
	case 'showPublication':
		$publication = mysql_query("SELECT * FROM `a2publication` WHERE `pub_id` = ".((int) $_GET['pub_id']));

		if(mysql_num_rows($publication) == 1){
			$publication = mysql_fetch_object($publication);
?>

<h3><?php echo htmlspecialchars($publication->title)?></h3>
<?php
			echo bibliographie_publications_parse_data($publication->pub_id);
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';