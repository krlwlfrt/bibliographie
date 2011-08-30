<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Publications</h2>
<?php
switch($_GET['task']){
	case 'checkData':
		unset($_SESSION['publication_prefetchedData_checked']);
?>

<h3>Check fetched data</h3>
<?php
		if(is_array($_SESSION['publication_prefetchedData_unchecked'])){
			$searchPersons = array();
			foreach($_SESSION['publication_prefetchedData_unchecked'] as $outerID => $entry){
?>

<div id="checkData_entry_<?php echo $outerID?>" style="background: #eee; border: 1px solid #aaa; color: #000; margin-top: 20px; padding: 5px;">
	<em style="float: right;"><?php echo $entry['pub_type']?></em>
	<strong><?php echo $entry['title']?></strong>

	<div id="checkData_entryResult_<?php echo $outerID?>"></div>
	<div class="innerData">
		<span style="float: right; text-align: right;">
			<a href="javascript:;" onclick="bibliographie_check_data_approve_entry(<?php echo $outerID?>)"><?php echo bibliographie_icon_get('tick')?> Approve entry</a><br />
			<a href="javascript:;" onclick="$('#checkData_entry_<?php echo $outerID?>').remove()"><?php echo bibliographie_icon_get('cross')?> Remove entry</a>
		</span>
<?php
				$persons = false;
				foreach(array('author', 'editor') as $role){
					if(count($entry[$role]) > 0){
						$persons = true;
						foreach($entry[$role] as $innerID => $person){
							$searchPersons[$role][] = array (
								'id' => $outerID.'_'.$innerID,
								'outerID' => $outerID,
								'innerID' => $innerID,
								'name' => $person['first'].' '.$person['von'].' '.$person['last'].' '.$person['jr'],

								'first' => $person['first'],
								'von' => $person['von'],
								'last' => $person['last'],
								'jr' => $person['jr']
							);

							if(!empty($person['jr']))
								$person['jr'] = ' '.$person['jr'];
?>

		<div id="checkData_<?php echo $role.'_'.$outerID.'_'.$innerID?>" style="margin-top: 10px;">
			<?php echo $role?> #<?php echo ($innerID + 1)?>: <?php echo $person['von'].' <strong>'.$person['last'].'</strong>'.$person['jr'].', '.$person['first']?>
			<div id="checkData_<?php echo $role.'Result_'.$outerID.'_'.$innerID?>"><img src="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/resources/images/loading.gif" alt="pending" /></div>
		</div>
<?php
						}
					}
				}

				if(!$persons)
					echo '<p class="error">No persons were found for this entry!</p>';
?>

	</div>
</div>
<?php
			}
?>

<p>If you are finished pre checking your fetched data you can move to <a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor&amp;useFetchedData=1">creating them</a> as publications!</p>

<script type="text/javascript">
	/* <![CDATA[ */
var persons = <?php echo json_encode($searchPersons)?>;

function bibliographie_check_data_approve_entry (outerID) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/ajax.php',
		data: {
			'task': 'checkData',
			'subTask': 'approveEntry',
			'outerID': outerID
		},
		dataType: 'json',
		success: function (json) {
			$('#checkData_entryResult_'+outerID).html(json.text);
			if(json.status == 'success')
				$('#checkData_entry_'+outerID+' .innerData').remove();
		}
	});
}

function bibliographie_check_data_create_person (role, outerID, innerID, first, von, last, jr) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/ajax.php',
		data: {
			'task': 'checkData',
			'subTask': 'createPerson',
			'role': role,
			'outerID': outerID,
			'innerID': innerID,
			'first': first,
			'von': von,
			'last': last,
			'jr': jr
		},
		success: function (html) {
			$('#checkData_'+role+'Result_'+outerID+'_'+innerID).html(html);
		}
	});
}

function bibliographie_check_data_approve_person (role, outerID, innerID) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/ajax.php',
		data: {
			'task': 'checkData',
			'subTask': 'approvePerson',
			'role': role,
			'outerID': outerID,
			'innerID': innerID,
			'personID': $('#checkData_'+role+'Select_'+outerID+'_'+innerID).val()
		},
		success: function (html) {
			$('#checkData_'+role+'Result_'+outerID+'_'+innerID).html(html);
		}
	});
}

$(function () {
	$.each(persons, function (role, persons) {
		$.each(persons, function (key, person) {
			$.ajax({
				url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/ajax.php',
				data: {
					'task': 'searchAuthors',
					'q': person.name
				},
				dataType: 'json',
				success: function (json) {
					if(json.length > 0){
						$('#checkData_'+role+'Result_'+person.id)
							.html('<select id="checkData_'+role+'Select_'+person.id+'" style="width: 45%;"></select>')
							.append(' <a href="javascript:;" onclick="bibliographie_check_data_approve_person(\''+role+'\', '+person.outerID+', '+person.innerID+')"><span class="silk-icon silk-icon-tick"></span> Approve '+role)
							.append(' <a href="javascript:;" onclick="bibliographie_check_data_create_person(\''+role+'\', '+person.outerID+', '+person.innerID+', \''+person.first+'\', \''+person.von+'\', \''+person.last+'\', \''+person.jr+'\')"><span class="silk-icon silk-icon-user-add"></span> Create person');

						$.each(json, function (key, personResult) {
							$('#checkData_'+role+'Select_'+person.id).append('<option value="'+personResult.id+'">'+personResult.name+'</option>');
						});
					}
				}
			});
		});
	});
});
	/* ]]> */
</script>
<?php
			break;
		}else
			echo '<p class="error">You did not fetch any data yet! You may want to do so now!</p>';

	case 'fetchData':
		unset($_SESSION['publication_prefetchedData_checked']);
		unset($_SESSION['publication_prefetchedData_unchecked']);
?>

<h3>Fetch data for publication creation</h3>
<div id="fetchData_container">
	<strong>1. step</strong> Select source... <span class="silk-icon silk-icon-hourglass"></span><br />

	<label for="source" class="block">Source</label>
	<select id="source" name="source" style="width: 60%;">
		<option value="">Please choose!</option>
		<option value="bibtexInput">BibTex direct input</option>
		<option value="bibtexRemote">BibTex remote file</option>
		<option value="isbndb">ISBNDB.com</option>
	</select>

	<button onclick="bibliographie_fetch_data_proceed({'source': $('#source').val(), 'step': '1'})">Select & proceed!</button>
</div>

<script type="text/javascript">
	/* <![CDATA[ */
function bibliographie_fetch_data_proceed (data) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/ajax.php?task=fetchData_proceed',
		data: data,
		type: 'POST',
		success: function (html) {
			$('#fetchData_container').html(html);
		}
	})
}
	/* ]]> */
</script>
<?php
	break;

	case 'publicationEditor':
		$title = 'Publication editor';
		$done = false;

		$publication = null;
		if(!empty($_GET['pub_id']))
			$publication = bibliographie_publications_get_data($_GET['pub_id'], 'assoc');

		if($_GET['skipEntry'] == '1')
			array_shift($_SESSION['publication_prefetchedData_checked']);

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$errors = array();

			if(in_array($_POST['pub_type'], $bibliographie_publication_types)){
				foreach($bibliographie_publication_fields[mb_strtolower($_POST['pub_type'])][0] as $requiredField){
					if(mb_strpos($requiredField, ',')){
						$fields = explode(',', $requiredField);
						if(empty($_POST[$fields[0]]) and empty($_POST[$fields[1]]))
							$errors[] = 'You have to fill '.$fields[0].' or '.$fields[1].'!';
					}elseif(empty($_POST[$requiredField]))
						$errors[] = 'You did not fill required field '.$requiredField.'!';
				}

				$author = explode(',', $_POST['author']);
				$editor = explode(',', $_POST['editor']);
				$topics = explode(',', $_POST['topics']);
				$tags = explode(',', $_POST['tags']);

				if(count($errors) == 0){
					if(is_array($publication)){
						echo '<h3>Updating publication...</h3>';

						$done = bibliographie_publications_edit_publication($publication['pub_id'], $_POST['pub_type'], $author, $editor, $_POST['title'], $_POST['month'], $_POST['year'], $_POST['booktitle'], $_POST['chapter'], $_POST['series'], $_POST['journal'], $_POST['volume'], $_POST['number'], $_POST['edition'], $_POST['publisher'], $_POST['location'], $_POST['howpublished'], $_POST['organization'], $_POST['institution'], $_POST['school'], $_POST['address'], $_POST['pages'], $_POST['note'], $_POST['abstract'], $_POST['userfields'], $_POST['bibtex_id'], $_POST['isbn'], $_POST['issn'], $_POST['doi'], $_POST['url'], $topics, $tags);

						if($done)
							echo '<p class="success">Publication has been edited!</p>';
					}else{
						echo '<h3>Creating publication...</h3>';

						$data = bibliographie_publications_create_publication($_POST['pub_type'], $author, $editor, $_POST['title'], $_POST['month'], $_POST['year'], $_POST['booktitle'], $_POST['chapter'], $_POST['series'], $_POST['journal'], $_POST['volume'], $_POST['number'], $_POST['edition'], $_POST['publisher'], $_POST['location'], $_POST['howpublished'], $_POST['organization'], $_POST['institution'], $_POST['school'], $_POST['address'], $_POST['pages'], $_POST['note'], $_POST['abstract'], $_POST['userfields'], $_POST['bibtex_id'], $_POST['isbn'], $_POST['issn'], $_POST['doi'], $_POST['url'], $topics, $tags);

						if(is_array($data)){
							echo '<p class="success">Publication has been created!</p>';
							echo 'You can <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showPublication&amp;pub_id='.((int) $data['pub_id']).'">view the created publication</a> or you can proceed by <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=publicationEditor">creating another</a> publication.';

							if($_GET['useFetchedData'] == '1'){
								array_shift($_SESSION['publication_prefetchedData_checked']);
								if(count($_SESSION['publication_prefetchedData_checked']) > 0)
									echo '<br /><br /><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=publicationEditor&amp;useFetchedData=1">'.bibliographie_icon_get('page-white-go').' Proceed publication creation with fetched data.</a>';
								else
									echo '<br /><br /><em>'.bibliographie_icon_get('page-white-go').' Prefetched data queue is now empty!</em>';
							}

							$done = true;
						}else
							echo '<p class="error">Something went wrong. Publication could not be created!</p>';
					}
				}else
					bibliographie_print_errors($errors);
			}
		}

		if(!$done){
			/**
			 * Initialize arrays for pre populating specific fields in the form.
			 */
			$prePopulateAuthor = array();
			$prePopulateEditor = array();
			$prePopulateTags = array();
			$prePopulateTopics = array();

			/**
			 * If requested parse existing publication and prefill the form with that.
			 */
			$usingFetchedData = false;
			if($_SERVER['REQUEST_METHOD'] == 'GET'){
				if($_GET['useFetchedData'] == '1' and count($_SESSION['publication_prefetchedData_checked']) > 0){
					$_POST = reset($_SESSION['publication_prefetchedData_checked']);
					if(count($_POST['checked_author']) == count($_POST['author']) and count($_POST['checked_editor']) == count($_POST['editor'])){
						if(is_array($_POST['checked_author']))
							$_POST['author'] = implode(',', $_POST['checked_author']);
						else
							$_POST['author'] = '';

						if(is_array($_POST['checked_editor']))
							$_POST['editor'] = implode(',', $_POST['checked_editor']);
						else
							$_POST['editor'] = '';

						$usingFetchedData = true;
					}else{
						echo '<p class="error">There was an error with the prefetched authors!</p>';
						$_POST = array();
					}

				}elseif(is_array($publication)){
					$_POST = $publication;

					$authors = bibliographie_publications_get_authors($_GET['pub_id']);
					if(is_array($authors) and count($authors) > 0)
						$_POST['author'] = implode(',', $authors);

					$editors = bibliographie_publications_get_editors($_GET['pub_id']);
					if(is_array($editors) and count($editors) > 0)
						$_POST['editor'] = implode(',', $editors);

					$tags = bibliographie_publications_get_tags($_GET['pub_id']);
					if(is_array($tags) and count($tags) > 0)
						$_POST['tags'] = implode(',', $tags);

					$topics = bibliographie_publications_get_topics($_GET['pub_id']);
					if(is_array($topics) and count($topics) > 0)
						$_POST['topics'] = implode(',', $topics);
				}
			}

			/**
			 * Fill the prePropulateAuthor array.
			 */
			if(!empty($_POST['author'])){
				if(preg_match('~[0-9]+(\,[0-9]+)*~', $_POST['author'])){
					$authors = explode(',', $_POST['author']);
					foreach($authors as $author)
						$prePopulateAuthor[] = array (
							'id' => $author,
							'name' => bibliographie_authors_parse_data($author)
						);
				}
			}

			/**
			 * Fill the prePropulateEditor array.
			 */
			if(!empty($_POST['editor'])){
				if(preg_match('~[0-9]+(\,[0-9]+)*~', $_POST['editor'])){
					$editors = explode(',', $_POST['editor']);
					foreach($editors as $editor)
						$prePopulateEditor[] = array (
							'id' => $editor,
							'name' => bibliographie_authors_parse_data($editor)
						);
				}
			}

			/**
			 * Fill the prePropulateTags array.
			 */
			if(!empty($_POST['tags'])){
				if(preg_match('~[0-9]+(\,[0-9]+)*~', $_POST['tags'])){
					$tags = explode(',', $_POST['tags']);
					foreach($tags as $tag)
						$prePopulateTags[] = array (
							'id' => $tag,
							'name' => bibliographie_tags_tag_by_id($tag)
						);
				}
			}

			/**
			 * Fill the prePropulateTopics array.
			 */
			if(!empty($_POST['topics'])){
				if(preg_match('~[0-9]+(\,[0-9]+)*~', $_POST['topics'])){
					$topics = explode(',', $_POST['topics']);
					foreach($topics as $topic){
						$prePopulateTopics[] = array (
							'id' => $topic,
							'name' => bibliographie_topics_topic_by_id($topic)
						);
					}
				}
			}
?>

<h3>Publication editor</h3>
<?php
			if(count($_SESSION['publication_prefetchedData_checked']) > 0 and $_GET['useFetchedData'] != '1'){
?>

<p class="notice"><?php echo bibliographie_icon_get('page-white-go')?> You have <?php echo count($_SESSION['publication_prefetchedData_checked'])?> entries in the fetched data queue. You might want to <a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor&amp;useFetchedData=1"> use the fetched data</a>.</p>
<?php
			}

			if($usingFetchedData){
?>

<p class="notice"><?php echo bibliographie_icon_get('page-white-go')?> Using the first of <?php echo count($_SESSION['publication_prefetchedData_checked'])?> entries in the fetched data queue. <a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor&amp;useFetchedData=1&amp;skipEntry=1">Skip this one.</a></a></p>
<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor&amp;useFetchedData=1" method="post">
<?php
			}elseif(is_array($publication)){
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor&amp;pub_id=<?php echo ((int) $publication['pub_id'])?>" method="post">
<?php
			}else{
?>

<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor" method="post">
<?php
			}
?>

	<div class="unit"><h4>General data</h4>
		<label for="pub_type" class="block">Publication type</label>
		<select id="pub_type" name="pub_type" style="width: 100%">
<?php
			foreach($bibliographie_publication_types as $type){
				echo '<option value="'.$type.'"';
				if($type == $_POST['pub_type'])
					echo ' selected="selected"';
				echo '>'.$type.'</option>';
			}
?>

		</select>

		<p id="authorOrEditorNotice" class="notice" style="display: none;"><span class="silk-icon silk-icon-asterisk-yellow"></span> Either you have to fill an author or an editor!</p>

		<label for="author" class="block">Author(s)</label>
		<em style="float: right"><a href="javascript:;" onclick="bibliographie_publications_create_person_form('author')"><span class="silk-icon silk-icon-user-add"></span> Add new author</a></em>
		<input type="text" id="author" name="author" style="width: 100%" value="<?php echo htmlspecialchars($_POST['author'])?>" />

		<label for="editor" class="block">Editor(s)</label>
		<em style="float: right"><a href="javascript:;" onclick="bibliographie_publications_create_person_form('editor')"><span class="silk-icon silk-icon-user-add"></span> Add new editor</a></em>
		<input type="text" id="editor" name="editor" style="width: 100%" value="<?php echo htmlspecialchars($_POST['editor'])?>" />

		<label for="title" class="block">Title</label>
		<input type="text" id="title" name="title" style="width: 80%" value="<?php echo htmlspecialchars($_POST['title'])?>" />
		<div id="similarTitleContainer" style="background: #fff; border: 1px solid #aaa; color: #000; display: none; float: right; font-size: 0.8em; padding: 5px; width: 80%"></div>
		<br style="clear: both;" />

		<div style="float: right; width: 50%">
			<label for="month" class="block">Month</label>
			<select id="month" name="month" style="width: 100%">
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

		<label for="year" class="block">Year</label>
		<input type="text" id="year" name="year" style="width: 45%" value="<?php echo htmlspecialchars($_POST['year'])?>" />
	</div>


	<div class="unit collapsible"><h4>Association</h4>
		<label for="booktitle" class="block">Booktitle</label>
		<input type="text" id="booktitle" name="booktitle" style="width: 100%" value="<?php echo htmlspecialchars($_POST['booktitle'])?>" />

		<label for="chapter" class="block">Chapter</label>
		<input type="text" id="chapter" name="chapter" style="width: 100%" value="<?php echo htmlspecialchars($_POST['chapter'])?>" />

		<label for="series" class="block">Series</label>
		<input type="text" id="series" name="series" style="width: 100%" value="<?php echo htmlspecialchars($_POST['series'])?>" />

		<label for="journal" class="block">Journal</label>
		<input type="text" id="journal" name="journal" style="width: 100%" value="<?php echo htmlspecialchars($_POST['journal'])?>" />

		<label for="volume" class="block">Volume</label>
		<input type="text" id="volume" name="volume" style="width: 100%" value="<?php echo htmlspecialchars($_POST['volume'])?>" />

		<label for="number" class="block">Number</label>
		<input type="text" id="number" name="number" style="width: 100%" value="<?php echo htmlspecialchars($_POST['number'])?>" />

		<label for="edition" class="block">Edition</label>
		<input type="text" id="edition" name="edition" style="width: 100%" value="<?php echo htmlspecialchars($_POST['edition'])?>" />
	</div>


	<div class="unit collapsible"><h4>Publishing & organization</h4>
		<label for="publisher" class="block">Publisher</label>
		<input type="text" id="publisher" name="publisher" style="width: 100%" value="<?php echo htmlspecialchars($_POST['publisher'])?>" />

		<label for="location" class="block">Location <em>of publisher</em></label>
		<input type="text" id="location" name="location" style="width: 100%" value="<?php echo htmlspecialchars($_POST['location'])?>" />

		<label for="howpublished" class="block">How published</label>
		<input type="text" id="howpublished" name="howpublished" style="width: 100%" value="<?php echo htmlspecialchars($_POST['howpublished'])?>" />

		<label for="organization" class="block">Organization</label>
		<input type="text" id="organization" name="organization" style="width: 100%" value="<?php echo htmlspecialchars($_POST['organization'])?>" />

		<label for="institution" class="block">Institution</label>
		<input type="text" id="institution" name="institution" style="width: 100%" value="<?php echo htmlspecialchars($_POST['institution'])?>" />

		<label for="school" class="block">School</label>
		<input type="text" id="school" name="school" style="width: 100%" value="<?php echo htmlspecialchars($_POST['school'])?>" />

		<label for="address" class="block">Address</label>
		<input type="text" id="address" name="address" style="width: 100%" value="<?php echo htmlspecialchars($_POST['address'])?>" />
	</div>


	<div class="unit collapsible"><h4>Pagination</h4>
		<label for="pages" class="block">Pages</label>
		<input type="text" id="pages" name="pages" style="width: 50%" value="<?php echo htmlspecialchars(str_replace('--', '-', $_POST['pages']))?>" />
	</div>


	<div class="unit"><h4>Descriptional stuff</h4>
		<label for="note" class="block">Note</label>
		<textarea id="note" name="note" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['note'])?></textarea>

		<label for="abstract" class="block">Abstract</label>
		<textarea id="abstract" name="abstract" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['abstract'])?></textarea>

		<label for="userfields" class="block">User fields</label>
		<textarea id="userfields" name="userfields" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['userfields'])?></textarea>

		<label for="bibtex_id" class="block">bibtex_id</label>
		<input id="bibtex_id" name="bibtex_id" style="width: 100%" value="<?php echo htmlspecialchars($_POST['bibtex_id'])?>" />
	</div>


	<div class="unit"><h4>Identification</h4>
		<label for="isbn" class="block">ISBN <em>for books</em></label>
		<input type="text" id="isbn" name="isbn" style="width: 100%" value="<?php echo htmlspecialchars($_POST['isbn'])?>" />

		<label for="issn" class="block">ISSN <em>for journals</em></label>
		<input type="text" id="issn" name="issn" style="width: 100%" value="<?php echo htmlspecialchars($_POST['issn'])?>" />

		<label for="doi" class="block">DOI <em>of publication</em></label>
		<input type="text" id="doi" name="doi" style="width: 100%" value="<?php echo htmlspecialchars($_POST['doi'])?>" />

		<label for="url" class="block">URL <em>of publication</em></label>
		<input type="text" id="url" name="url" style="width: 100%" value="<?php echo htmlspecialchars($_POST['url'])?>" />
	</div>


	<div class="unit"><h4>Topics & tags</h4>
		<label for="topics" class="block">Topics</label>
		<div id="topicsContainer" style="background: #fff; border: 1px solid #aaa; color: #000; float: right; font-size: 0.8em; padding: 5px; width: 45%;"><em>Search for a topic in the left container!</em></div>
		<input type="text" id="topics" name="topics" style="width: 100%" value="<?php echo htmlspecialchars($_POST['topics'])?>" />
		<br style="clear: both" />

		<label for="tags" class="block">Tags</label>
		<em style="float: right"><a href="javascript:;" onclick="bibliographie_publications_create_tag()"><span class="silk-icon silk-icon-tag-blue-add"></span> Add new tag</a></em>
		<input type="text" id="tags" name="tags" style="width: 100%" value="<?php echo htmlspecialchars($_POST['tags'])?>" />
	</div>

	<div class="submit"><input type="submit" value="save" /></div>
</form>

<script type="text/javascript">
	/* <![CDATA[ */
<?php
			echo 'var pub_id = ';
			if(is_array($publication))
				echo $publication['pub_id'];
			else
				echo 0;
			echo ';';
?>

function bibliographie_publications_show_fields (selectedType) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/ajax.php',
		data: {
			'task': 'getFields',
			'type': selectedType
		},
		dataType: 'json',
		success: function (json) {
			if(json != ''){
				$('.collapsible, .collapsible input, .collapsible textarea, .collapsible select, .collapsible label').hide();
				$('input, textarea, select').removeClass('obligatory');
				$('label span').remove();
				$('#authorOrEditorNotice').hide();

				$.each(json, function(key, value){
					if(value.field == 'author,editor'){
						$('#authorOrEditorNotice').show();
					}else{
						$('.collapsible #'+value.field).show().parent().show();
						$('label[for="'+value.field+'"]').show();
						if(value.flag == 0){
							$('#'+value.field).addClass('obligatory');
							$('label[for="'+value.field+'"]').prepend('<span class="silk-icon silk-icon-asterisk-yellow"></span> ');
						}
					}
				});
			}else
				$.jGrowl('Something bad happened! Could not fetch the field specifications for the publication type.');
		}
	});
}

function bibliographie_publications_create_person (firstname, von, surname, jr, role) {
	if(role != 'author' && role != 'editor')
		return;

	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/ajax.php',
		data: {
			'task': 'createPerson',
			'firstname': firstname,
			'von': von,
			'surname': surname,
			'jr': jr
		},
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text);
			if(json.status == 'success')
				$('#'+role).tokenInput('add', {id: json.autor_id, name: json.name});
		}
	})
}

function bibliographie_publications_create_person_form (role) {
	if(role != 'author' && role != 'editor')
		return;

	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/ajax.php',
		data: {
			task: 'createPersonForm'
		},
		success: function (html) {
			$('#dialogContainer').append(html);
			$('#createPersonForm').dialog({
				width: 400,
				modal: true,
				buttons: {
					'Create & add': function () {
						bibliographie_publications_create_person($('#firstname').val(), $('#von').val(), $('#surname').val(), $('#jr').val(), role);
						$(this).dialog('close');
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				close: function () {
					$(this).remove();
				}
			});
		}
	})
}

function bibliographie_publications_create_tag () {
	tagName = window.prompt('Please enter the tag you want to create!');

	if(tagName == null)
		return;

	if(tagName == '')
		return $.jGrowl('You have to enter something to add a new tag!');

	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/tags/ajax.php',
		data: {
			'task': 'createTag',
			'tag': tagName
		},
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text);
			if(json.status == 'success')
				$('#tags').tokenInput('add', {id: json.tag_id, name: json.tag});
		}
	})
}

function bibliographie_publications_show_subgraph (topic) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/ajax.php',
		data: {
			'task': 'getSubgraph',
			'topic_id': topic
		},
		success: function (html) {
			$('#dialogContainer').append(html);
			$('#selectFromTopicSubgraph').dialog({
				width: 600,
				modal: true,
				buttons: {
					'Ok': function () {
						$(this).dialog('close');
					}
				},
				close: function () {
					$(this).remove();
				}
			});
		}
	});
}

function bibliographie_publications_check_title (title) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/ajax.php',
		data: {
			'task': 'checkTitle',
			'title': title,
			'pub_id': pub_id
		},
		dataType: 'json',
		success: function (json) {
			if(json.results.length > 0){
				$('#similarTitleContainer').html('<div style="margin-bottom: 10px;">Showing <strong>'+json.results.length+' most similar titles</strong> ('+json.count+' search results)</div>');
				$.each(json.results, function (key, value) {
					$('#similarTitleContainer')
						.append('<div style="margin-top: 5px;">')
						.append('<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=showPublication&amp;pub_id='+value.pub_id+'"><span class="silk-icon silk-icon-page-white-text"></a>')
						.append(' <a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor&amp;pub_id='+value.pub_id+'"><span class="silk-icon silk-icon-page-white-edit"></a>')
						.append(' '+value.title+'</div>');
				});
				if($('#similarTitleContainer').is(':visible') == false)
					$('#similarTitleContainer').show('slow');
			}else
				$('#similarTitleContainer').hide();
		}
	})
}

$(function() {
	$('#author').tokenInput('<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/ajax.php?task=searchAuthors', {
		searchDelay: 500,
		minChars: 3,
		preventDuplicates: true,
		prePopulate: <?php echo json_encode($prePopulateAuthor).PHP_EOL?>
	});

	$('#editor').tokenInput('<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/ajax.php?task=searchAuthors', {
		searchDelay: 500,
		minChars: 3,
		preventDuplicates: true,
		prePopulate: <?php echo json_encode($prePopulateEditor).PHP_EOL?>
	});

	$('#tags').tokenInput('<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/tags/ajax.php?task=searchTags', {
		searchDelay: 500,
		minChars: 3,
		preventDuplicates: true,
		theme: 'facebook',
		prePopulate: <?php echo json_encode($prePopulateTags).PHP_EOL?>
	});

	$('#topics').tokenInput('<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/topics/ajax.php?task=searchTopicJSON', {
		searchDelay: 500,
		minChars: 3,
		preventDuplicates: true,
		theme: 'facebook',
		prePopulate: <?php echo json_encode($prePopulateTopics)?>,
		noResultsText: 'Results are in the container to the right!',
		onResult: function (results) {
			$('#topicsContainer').html('<div style="margin-bottom: 10px;"><strong>Topics search result</strong></div>');
			if(results.length > 0){
				$.each(results, function (key, value) {
					var selected = false;
					var topicsArray = $('#topics').tokenInput('get')

					$.each(topicsArray, function (selectedKey, selectedValue) {
						if(selectedValue.name == value.name)
							selected = true;
					});

					if(selected){
						$('#topicsContainer')
							.append('<div>')
							.append('<a href="javascript:;" onclick="bibliographie_publications_show_subgraph(\''+value.id+'\')" style="float: right;"><span class="silk-icon silk-icon-sitemap"></span> graph</a>')
							.append('<span class="silk-icon silk-icon-tick"></span> <em>'+value.name+'</em> is selected.</div>');
					}else{
						$('#topicsContainer')
							.append('<div>')
							.append('<a href="javascript:;" onclick="$(\'#topics\').tokenInput(\'add\', {id:\''+value.id+'\',name:\''+value.name+'\'})" style="float: right;"><span class="silk-icon silk-icon-add"></span> add</a>')
							.append('<a href="javascript:;" onclick="bibliographie_publications_show_subgraph(\''+value.id+'\')" style="float: right;"><span class="silk-icon silk-icon-sitemap"></span> graph</a>')
							.append('<em>'+value.name+'</em>')
							.append('</div>');
					}
				});
			}else
				$('#topicsContainer').append('No results for search!');

			return Array();
		}
	});

	$('#pub_type').mouseup(function (event) {
		bibliographie_publications_show_fields(event.target.value);
	}).keyup(function (event) {
		bibliographie_publications_show_fields(event.target.value);
	});

	$('#title').keyup(function (event) {
		delayRequest('bibliographie_publications_check_title', Array(event.target.value));
	});

	bibliographie_publications_show_fields($('#pub_type').val());
	delayRequest('bibliographie_publications_check_title', Array($('#title').val()));
});
	/* ]]> */
</script>
<?php
		}
	break;

	case 'showPublication':
		$publication = bibliographie_publications_get_data($_GET['pub_id']);

		if(is_object($publication)){
?>

<em style="float: right"><a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor&pub_id=<?php echo $publication->pub_id?>">Edit publication</a></em>
<h3><?php echo htmlspecialchars($publication->title)?></h3>
<?php
			echo bibliographie_publications_parse_data($publication->pub_id);
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';