<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Publications</h2>
<?php
switch($_GET['task']){
	case 'publicationEditor':
		$title = 'Publication editor';
		$done = false;

		$publication = null;
		if(!empty($_GET['pub_id']))
			$publication = bibliographie_publications_get_data($_GET['pub_id'], 'assoc');

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

						$done = bibliographie_publications_edit_publication($publication['pub_id'], $_POST['pub_type'], $author, $editor, $_POST['title'], $_POST['month'], $_POST['year'], $_POST['booktitle'], $_POST['chapter'], $_POST['series'], $_POST['journal'], $_POST['volume'], $_POST['number'], $_POST['edition'], $_POST['publisher'], $_POST['location'], $_POST['howpublished'], $_POST['organization'], $_POST['institution'], $_POST['school'], $_POST['address'], $_POST['pages'], $_POST['note'], $_POST['abstract'], $_POST['userfields'], $_POST['isbn'], $_POST['issn'], $_POST['doi'], $_POST['url'], $topics, $tags);

						if($done)
							echo '<p class="success">Publication was edited!</p>';
					}else{
						echo '<h3>Creating publication...</h3>';

						$done = bibliographie_publications_create_publication($_POST['pub_type'], $author, $editor, $_POST['title'], $_POST['month'], $_POST['year'], $_POST['booktitle'], $_POST['chapter'], $_POST['series'], $_POST['journal'], $_POST['volume'], $_POST['number'], $_POST['edition'], $_POST['publisher'], $_POST['location'], $_POST['howpublished'], $_POST['organization'], $_POST['institution'], $_POST['school'], $_POST['address'], $_POST['pages'], $_POST['note'], $_POST['abstract'], $_POST['userfields'], $_POST['isbn'], $_POST['issn'], $_POST['doi'], $_POST['url'], $topics, $tags);
						if($done)
							echo '<p class="success">Publication was created!</p>';
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
			if($_SERVER['REQUEST_METHOD'] == 'GET' and is_array($publication)){
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
			if(is_array($publication)){
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
		<input type="text" id="title" name="title" style="width: 100%" value="<?php echo htmlspecialchars($_POST['title'])?>" />

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
		<input type="text" id="pages" name="pages" style="width: 50%" value="<?php echo htmlspecialchars($_POST['pages'])?>" />
	</div>


	<div class="unit"><h4>Descriptional stuff</h4>
		<label for="note" class="block">Note</label>
		<textarea id="note" name="note" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['note'])?></textarea>

		<label for="abstract" class="block">Abstract</label>
		<textarea id="abstract" name="abstract" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['abstract'])?></textarea>

		<label for="userfields" class="block">User fields</label>
		<textarea id="userfields" name="userfields" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['userfields'])?></textarea>
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
function bibliographie_publications_show_fields (select) {
	$.ajax({
		url: '<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/ajax.php',
		data: {
			'task': 'getFields',
			'type': select.value
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
				alert('Something bad happened!');
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
				width: 400,
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
						$('#topicsContainer').append('<div><span class="silk-icon silk-icon-tick"></span> <em>'+value.name+'</em> is selected.</div>')
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

	$('#pub_type').change(function(event) {
		bibliographie_publications_show_fields(event.target);
	});

	bibliographie_publications_show_fields(document.getElementById('pub_type'));
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