<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';
?>

<h2>Publications</h2>
<?php
switch($_GET['task']){
	case 'publicationEditor':
		$title = 'Publication editor';

		$prePopulateAuthor = array();
		$prePopulateEditor = array();
		$prePopulateTags = array();

		if($_SERVER['REQUEST_METHOD'] == 'GET' and !empty($_GET['pub_id'])){
			$publication = bibliographie_publications_get_data($_GET['pub_id'], 'assoc');
			if(is_array($publication)){
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
			}
		}

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
?>

<h3>Publication editor</h3>
<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor" method="post">
	<div class="unit"><h4>General data</h4>
		<label for="pub_type" class="block">Publication type</label>
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

		<p id="authorOrEditorNotice" class="notice" style="display: none;"><span class="silk-icon silk-icon-asterisk-yellow"></span> Either you have to fill an author or an editor!</p>

		<label for="author" class="block">Author(s)</label>
		<em style="float: right"><a href="javascript:;" onclick="bibliographie_publications_create_person_form('author')">Add new author</a></em>
		<input type="text" id="author" name="author" style="width: 100%" value="<?php echo htmlspecialchars($_POST['author'])?>" />

		<label for="editor" class="block">Editor(s)</label>
		<em style="float: right"><a href="javascript:;" onclick="bibliographie_publications_create_person_form('editor')">Add new editor</a></em>
		<input type="text" id="editor" name="editor" style="width: 100%" value="<?php echo htmlspecialchars($_POST['editor'])?>" />

		<label for="title" class="block">Title</label>
		<input type="text" id="title" name="title" style="width: 100%" value="<?php echo htmlspecialchars($_POST['title'])?>" />
	</div>


	<div class="unit collapsible"><h4>Dating</h4>
		<label for="year" class="block">Year</label>
		<input type="text" id="year" name="year" style="width: 100%" value="<?php echo htmlspecialchars($_POST['year'])?>" placeholder="<?php echo date('Y')?>" />

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


	<div class="unit collapsible"><h4>Publishing</h4>
		<label for="publisher" class="block">Publisher</label>
		<input type="text" id="publisher" name="publisher" style="width: 100%" value="<?php echo htmlspecialchars($_POST['publisher'])?>" />

		<label for="location" class="block">Location <em>of publisher</em></label>
		<input type="text" id="location" name="location" style="width: 100%" value="<?php echo htmlspecialchars($_POST['location'])?>" />

		<label for="howpublished" class="block">How published</label>
		<input type="text" id="howpublished" name="howpublished" style="width: 100%" value="<?php echo htmlspecialchars($_POST['howpublished'])?>" />
	</div>


	<div class="unit collapsible"><h4>Identification</h4>
		<label for="isbn" class="block">ISBN <em>for books</em></label>
		<input type="text" id="isbn" name="isbn" style="width: 100%" value="<?php echo htmlspecialchars($_POST['isbn'])?>" />

		<label for="issn" class="block">ISSN <em>for journals</em></label>
		<input type="text" id="issn" name="issn" style="width: 100%" value="<?php echo htmlspecialchars($_POST['issn'])?>" />

		<label for="doi" class="block">DOI <em>of publication</em></label>
		<input type="text" id="doi" name="doi" style="width: 100%" value="<?php echo htmlspecialchars($_POST['doi'])?>" />

		<label for="url" class="block">URL <em>of publication</em></label>
		<input type="text" id="url" name="url" style="width: 100%" value="<?php echo htmlspecialchars($_POST['url'])?>" />
	</div>


	<div class="unit collapsible"><h4>Pagination</h4>
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


	<div class="unit collapsible"><h4>Organization</h4>
		<label for="organization" class="block">Organization</label>
		<input type="text" id="organization" name="organization" style="width: 100%" value="<?php echo htmlspecialchars($_POST['organization'])?>" />

		<label for="institution" class="block">Institution</label>
		<input type="text" id="institution" name="institution" style="width: 100%" value="<?php echo htmlspecialchars($_POST['institution'])?>" />

		<label for="school" class="block">School</label>
		<input type="text" id="school" name="school" style="width: 100%" value="<?php echo htmlspecialchars($_POST['school'])?>" />

		<label for="address" class="block">Address</label>
		<input type="text" id="address" name="address" style="width: 100%" value="<?php echo htmlspecialchars($_POST['address'])?>" />
	</div>


	<div class="unit"><h4>Descriptional stuff</h4>
		<label for="note" class="block">Note</label>
		<textarea id="note" name="note" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['note'])?></textarea>

		<label for="abstract" class="block">Abstract</label>
		<textarea id="abstract" name="abstract" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['abstract'])?></textarea>

		<label for="userfields" class="block">User fields</label>
		<textarea id="userfields" name="userfields" cols="10" rows="10" style="width: 100%"><?php echo htmlspecialchars($_POST['userfields'])?></textarea>

		<label for="tags" class="block">Tags</label>
		<em style="float: right"><a href="javascript:;" onclick="bibliographie_publications_create_tag()">Add new tag</a></em>
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
				$('.collapsible, .collapsible input, .collapsible textarea, .collapsible select, .collapsible label').hide().removeClass('obligatory');
				$('label span').remove();
				$('#authorOrEditorNotice').hide();

				$.each(json, function(key, value){
					if(value.field == 'author,editor'){
						$('#authorOrEditorNotice').show();
					}else{
						$('.collapsible #'+value.field).show().parent().show();
						$('label[for="'+value.field+'"]').show();
						if(value.flag == 0){
							$('.collapsible #'+value.field).addClass('obligatory');
							$('label[for="'+value.field+'"]').append(' <span class="silk-icon silk-icon-asterisk-yellow"></span>');
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

	if(tagName != null && tagName != ''){
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
	}else
		$.jGrowl('You have to enter something to add a new tag!');
}

$(function() {
	$('#author').tokenInput('<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/ajax.php?task=searchAuthors', {
		searchDelay: 500,
		minChars: 3,
		preventDuplicates: true,
		prePopulate: <?php echo json_encode($prePopulateAuthor)."\n"?>
	});

	$('#editor').tokenInput('<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/authors/ajax.php?task=searchAuthors', {
		searchDelay: 500,
		minChars: 3,
		preventDuplicates: true,
		prePopulate: <?php echo json_encode($prePopulateEditor)."\n"?>
	});

	$('#tags').tokenInput('<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/tags/ajax.php?task=searchTags', {
		searchDelay: 500,
		minChars: 3,
		preventDuplicates: true,
		theme: 'facebook',
		prePopulate: <?php echo json_encode($prePopulateTags)."\n"?>
	});

	$('#pub_type').change(function(event) {
		bibliographie_publications_show_fields(event.target);
	});

	bibliographie_publications_show_fields(document.getElementById('pub_type'));
});
	/* ]]> */
</script>
<?php
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