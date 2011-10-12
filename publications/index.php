<?php
define('BIBLIOGRAPHIE_ROOT_PATH', '..');

require BIBLIOGRAPHIE_ROOT_PATH.'/init.php';
?>

<h2>Publications</h2>
<?php
switch($_GET['task']){
	case 'showContainer':
		if(in_array($_GET['type'], array('journal', 'book'))){
			$fields = array (
				'journal',
				'volume'
			);
			if($_GET['type'] == 'book')
				$fields = array (
					'booktitle',
					'number'
				);

			$result = mysql_query("SELECT `year`, `".$fields[0]."`, `".$fields[1]."`, COUNT(*) AS `count` FROM `a2publication` WHERE `".$fields[0]."` = '".mysql_real_escape_string(stripslashes($_GET['container']))."' GROUP BY `".$fields[1]."` ORDER BY `year`, `volume`");

			if(mysql_num_rows($result) > 0){
				echo '<h3>Chronology of '.htmlspecialchars($_GET['container']).'</h3>';
				echo '<table class="dataContainer">';
				echo '<tr><th></th><th>'.htmlspecialchars($fields[0]).'</th><th>Year & '.htmlspecialchars($fields[1]).'</th><th>Articles</th></tr>';
				while($container = mysql_fetch_assoc($result)){
					echo '<tr>'
						.'<td><a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showContainerPiece&amp;type='.htmlspecialchars($_GET['type']).'&amp;container='.htmlspecialchars($container[$fields[0]]).'&amp;year='.((int) $container['year']).'&amp;piece='.htmlspecialchars($container[$fields[1]]).'">'.bibliographie_icon_get('page-white-stack').'</a></td>'
						.'<td>'.htmlspecialchars($container[$fields[0]]).'</td>'
						.'<td>'.$container['year'].' '.$container[$fields[1]].'</td>'
						.'<td>'.$container['count'].' article(s)</td>'
						.'</tr>';
				}
				echo '</table>';
			}
		}
	break;

	case 'showContainerPiece':
		if(in_array($_GET['type'], array('journal', 'book'))){
			$fields = array (
				'journal',
				'volume'
			);
			if($_GET['type'] == 'book')
				$fields = array (
					'booktitle',
					'number'
				);

			$result = mysql_query("SELECT `pub_id` FROM `a2publication` WHERE `".$fields[0]."` = '".mysql_real_escape_string(stripslashes($_GET['container']))."' AND `year` = ".((int) $_GET['year'])." AND `".$fields[1]."` = '".mysql_real_escape_string(stripslashes($_GET['piece']))."' ORDER BY `title`");

			if(mysql_num_rows($result) > 0){
	?>

	<h3>Publications in <a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=showContainer&amp;type=<?php echo htmlspecialchars($_GET['type'])?>&amp;container=<?php echo htmlspecialchars($_GET['container'])?>"><?php echo htmlspecialchars($_GET['container'])?></a>, <?php echo ((int) $_GET['year']).' '.htmlspecialchars($_GET[$field[1]])?></h3>
	<?php
				$publications = array();
				while($publication = mysql_fetch_object($result))
					$publications[] = $publication->pub_id;

				bibliographie_publications_print_list($publications, BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showContainerPiece&amp;type='.htmlspecialchars($_GET['type']).'&amp;container='.htmlspecialchars($_GET['container']).'&amp;year='.((int) $_GET['year']).'&amp;piece='.htmlspecialchars($_GET['piece']), $_GET['bookmarkBatch']);
			}
		}
	break;

	case 'checkData':
		/**
		 * Unset yet checked prefetched data.
		 */
		unset($_SESSION['publication_prefetchedData_checked']);
?>

<h3>Check fetched data</h3>
<p class="notice">Please precheck all of the parsed authors now before moving to creating them in the publication editor!</p>
<?php
		if(is_array($_SESSION['publication_prefetchedData_unchecked'])){
			$searchPersons = array();

			/**
			 * Loop for entries...
			 */
			foreach($_SESSION['publication_prefetchedData_unchecked'] as $entryID => $entry){
?>

<div id="bibliographie_checkData_entry_<?php echo $entryID?>" class="bibliographie_checkData_entry">
	<em class="bibliographie_checkData_pubType"><?php echo $entry['pub_type']?></em>
	<strong><?php echo $entry['title']?></strong>

	<div id="bibliographie_checkData_approvalResult_<?php echo $entryID?>"></div>

	<div class="bibliographie_checkData_persons">
		<span style="float: right; font-size: 0.8em; text-align: right;">
			<a href="javascript:;" onclick="bibliographie_publications_check_data_approve_entry(<?php echo $entryID?>)">Approve entry <?php echo bibliographie_icon_get('tick')?> </a><br />
			<a href="javascript:;" onclick="bibliographie_publications_check_data_approve_all(<?php echo $entryID?>)">Approve all persons and entry <?php echo bibliographie_icon_get('tick')?></a><br />
			<a href="javascript:;" onclick="$('#bibliographie_checkData_entry_<?php echo $entryID?>').hide('slow', function() {$(this).remove()})">Remove entry <?php echo bibliographie_icon_get('cross')?></a>
		</span>
<?php
				/**
				 * Loop for persons... Authors and editors...
				 */
				$persons = false;
				foreach(array('author', 'editor') as $role){
					if(count($entry[$role]) > 0){
						$persons = true;

						foreach($entry[$role] as $personID => $person){
							/**
							 * Put the person in the array that is needed for js functionality...
							 */
							$searchPersons[$entryID][$role][$personID] = array (
								'htmlID' => $entryID.'_'.$role.'_'.$personID,

								'role' => $role,
								'entryID' => $entryID,
								'personID' => $personID,

								'name' => $person['first'].' '.$person['von'].' '.$person['last'].' '.$person['jr'],

								'first' => $person['first'],
								'von' => $person['von'],
								'last' => $person['last'],
								'jr' => $person['jr'],

								'approved' => false
							);

							if(!empty($person['jr']))
								$person['jr'] = ' '.$person['jr'];
?>

		<div id="bibliographie_checkData_person_<?php echo $entryID.'_'.$role.'_'.$personID?>" style="margin-top: 10px;">
			<?php echo $role?> #<?php echo ((string) $personID + 1)?>:
			<?php echo $person['von'].' <strong>'.$person['last'].'</strong>'.$person['jr'].', '.$person['first']?>
			<div id="bibliographie_checkData_personResult_<?php echo $entryID.'_'.$role.'_'.$personID?>"><img src="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/resources/images/loading.gif" alt="pending" /></div>
		</div>
<?php
						}
					}
				}

				/**
				 * Tell if no persons were parsed...
				 */
				if(!$persons)
					echo '<p class="error">No persons could be parsed for this entry!</p>';
?>

	</div>
</div>
<?php
			}
?>

<div class="submit"><button onclick="window.location = bibliographie_web_root+'/publications/?task=publicationEditor&amp;useFetchedData=1';">Go to publication editor</button></div>

<script type="text/javascript">
	/* <![CDATA[ */
var bibliographie_checkData_searchPersons = <?php echo json_encode($searchPersons)?>;

$(function () {
	$.each(bibliographie_checkData_searchPersons, function (entryID, entries) {
		$.each(entries, function (role, persons) {
			$.each(persons, function (personID, person){
				bibliographie_publications_search_author_for_approval(role, person);
			})
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
<?php
		if(BIBLIOGRAPHIE_ISBNDB_KEY != '')
			echo '<option value="isbndb">ISBNDB.com</option>';
?>
	</select>

	<button onclick="bibliographie_publications_fetch_data_proceed({'source': $('#source').val(), 'step': '1'})">Select & proceed!</button>
</div>
<?php
	break;

	case 'publicationEditor':
		$bibliographie_title = 'Publication editor';
		$done = false;

		$publication = null;
		if(!empty($_GET['pub_id']))
			$publication = (array) bibliographie_publications_get_data($_GET['pub_id']);

		if(is_array($publication))
			bibliographie_history_append_step('publications', 'Editing publication '.$publication['title']);
		else
			bibliographie_history_append_step('publications', 'Creating publication');

		if($_GET['skipEntry'] == '1')
			array_shift($_SESSION['publication_prefetchedData_checked']);

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$errors = array();

			if(in_array($_POST['pub_type'], $bibliographie_publication_types)){
				foreach($bibliographie_publication_fields[mb_strtolower($_POST['pub_type'])][0] as $requiredField){
					if(mb_strpos($requiredField, ',') !== false){
						$fields = explode(',', $requiredField);
						if(empty($_POST[$fields[0]]) and empty($_POST[$fields[1]]))
							$errors[] = 'You have to fill '.$fields[0].' or '.$fields[1].'!';
					}elseif(empty($_POST[$requiredField]))
						$errors[] = 'You did not fill required field '.$requiredField.'!';
				}

				$author = csv2array($_POST['author'], 'int');
				$editor = csv2array($_POST['editor'], 'int');
				$topics = csv2array($_POST['topics'], 'int');
				$tags = csv2array($_POST['tags'], 'int');

				if(count($errors) == 0){
					if(is_array($publication)){
						echo '<h3>Updating publication...</h3>';

						$data = bibliographie_publications_edit_publication($publication['pub_id'], $_POST['pub_type'], $author, $editor, $_POST['title'], $_POST['month'], $_POST['year'], $_POST['booktitle'], $_POST['chapter'], $_POST['series'], $_POST['journal'], $_POST['volume'], $_POST['number'], $_POST['edition'], $_POST['publisher'], $_POST['location'], $_POST['howpublished'], $_POST['organization'], $_POST['institution'], $_POST['school'], $_POST['address'], $_POST['pages'], $_POST['note'], $_POST['abstract'], $_POST['userfields'], $_POST['bibtex_id'], $_POST['isbn'], $_POST['issn'], $_POST['doi'], $_POST['url'], $topics, $tags);

						if($done){
							echo '<p class="success">Publication has been edited!</p>';
							echo 'You can <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showPublication&amp;pub_id='.((int) $data['pub_id']).'">view the created publication</a> or you can proceed by <a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=publicationEditor">creating another</a> publication.';
						}
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
			$prePopulateAuthor = bibliographie_authors_populate_input($_POST['author']);
			$prePopulateEditor = bibliographie_authors_populate_input($_POST['editor']);

			/**
			 * Fill the prePropulateTags array.
			 */
			if(!empty($_POST['tags'])){
				if(preg_match('~[0-9]+(\,[0-9]+)*~', $_POST['tags'])){
					$tags = csv2array($_POST['tags'], 'int');
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
					$topics = csv2array($_POST['topics'], 'int');
					foreach($topics as $topic){
						$prePopulateTopics[] = array (
							'id' => $topic,
							'name' => bibliographie_topics_parse_name($topic)
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
		<select id="pub_type" name="pub_type" style="width: 100%" tabindex="1">
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

		<div id="authorContainer">
			<label for="author" class="block">Author(s)</label>
			<em style="float: right"><a href="javascript:;" onclick="bibliographie_publications_create_person_form('author')"><span class="silk-icon silk-icon-user-add"></span> Add new author</a></em>
			<input type="text" id="author" name="author" style="width: 100%" value="<?php echo htmlspecialchars($_POST['author'])?>" tabindex="2" />
		</div>

		<div id="editorContainer">
			<label for="editor" class="block">Editor(s)</label>
			<em style="float: right"><a href="javascript:;" onclick="bibliographie_publications_create_person_form('editor')"><span class="silk-icon silk-icon-user-add"></span> Add new editor</a></em>
			<input type="text" id="editor" name="editor" style="width: 100%" value="<?php echo htmlspecialchars($_POST['editor'])?>" tabindex="3" />
		</div>

		<label for="title" class="block">Title</label>
		<input type="text" id="title" name="title" style="width: 100%" value="<?php echo htmlspecialchars($_POST['title'])?>" class="bibtex" tabindex="4" />

		<div id="similarTitleContainer" class="bibliographie_similarity_container"></div>

		<label for="bibtex_id" class="block">BibTex cite ID</label>
		<input id="bibtex_id" name="bibtex_id" style="width: 100%" value="<?php echo htmlspecialchars($_POST['bibtex_id'])?>" class="" tabindex="27" />

		<div style="float: right; width: 50%">
			<label for="month" class="block">Month</label>
			<select id="month" name="month" style="width: 100%" class="bibtex" tabindex="5">
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
		<input type="text" id="year" name="year" style="width: 45%" value="<?php echo htmlspecialchars($_POST['year'])?>" class="bibtex" tabindex="6" />
	</div>

	<div class="unit"><h4>Topics & tags</h4>
		<label for="topics" class="block">Topics</label>
		<div id="topicsContainer" style="background: #fff; border: 1px solid #aaa; color: #000; float: right; font-size: 0.8em; padding: 5px; width: 45%;"><em>Search for a topic in the left container!</em></div>
		<input type="text" id="topics" name="topics" style="width: 100%" value="<?php echo htmlspecialchars($_POST['topics'])?>" tabindex="7" />
		<br style="clear: both" />

		<label for="tags" class="block">Tags</label>
		<em style="float: right; text-align: right;">
			<a href="javascript:;" onclick="bibliographie_publications_create_tag()"><span class="silk-icon silk-icon-tag-blue-add"></span> Add new tag</a><br />
			<span id="tags_tagNotExisting"></em>
		</em>
		<input type="text" id="tags" name="tags" style="width: 100%" value="<?php echo htmlspecialchars($_POST['tags'])?>" tabindex="8" />
		<br style="clear: both;" />
	</div>

	<div class="unit bibtex"><h4>Association</h4>
		<label for="booktitle" class="block">Booktitle</label>
		<input type="text" id="booktitle" name="booktitle" style="width: 100%" value="<?php echo htmlspecialchars($_POST['booktitle'])?>" class="bibtex" tabindex="9" />

		<label for="chapter" class="block">Chapter</label>
		<input type="text" id="chapter" name="chapter" style="width: 100%" value="<?php echo htmlspecialchars($_POST['chapter'])?>" class="bibtex" tabindex="10" />

		<label for="series" class="block">Series</label>
		<input type="text" id="series" name="series" style="width: 100%" value="<?php echo htmlspecialchars($_POST['series'])?>" class="bibtex" tabindex="11" />

		<label for="journal" class="block">Journal</label>
		<input type="text" id="journal" name="journal" style="width: 100%" value="<?php echo htmlspecialchars($_POST['journal'])?>" class="bibtex" tabindex="12" />

		<label for="volume" class="block">Volume</label>
		<input type="text" id="volume" name="volume" style="width: 100%" value="<?php echo htmlspecialchars($_POST['volume'])?>" class="bibtex" tabindex="13" />

		<label for="number" class="block">Number</label>
		<input type="text" id="number" name="number" style="width: 100%" value="<?php echo htmlspecialchars($_POST['number'])?>" class="bibtex" tabindex="14" />

		<label for="edition" class="block">Edition</label>
		<input type="text" id="edition" name="edition" style="width: 100%" value="<?php echo htmlspecialchars($_POST['edition'])?>" class="bibtex" tabindex="15" />
	</div>

	<div class="unit bibtex"><h4>Publishing & organization</h4>
		<label for="publisher" class="block">Publisher</label>
		<input type="text" id="publisher" name="publisher" style="width: 100%" value="<?php echo htmlspecialchars($_POST['publisher'])?>" class="bibtex" tabindex="16" />

		<label for="location" class="block">Location <em>of publisher</em></label>
		<input type="text" id="location" name="location" style="width: 100%" value="<?php echo htmlspecialchars($_POST['location'])?>" class="bibtex" tabindex="17" />

		<label for="howpublished" class="block">How published</label>
		<input type="text" id="howpublished" name="howpublished" style="width: 100%" value="<?php echo htmlspecialchars($_POST['howpublished'])?>" class="bibtex" tabindex="18" />

		<label for="organization" class="block">Organization</label>
		<input type="text" id="organization" name="organization" style="width: 100%" value="<?php echo htmlspecialchars($_POST['organization'])?>" class="bibtex" tabindex="19" />

		<label for="institution" class="block">Institution</label>
		<input type="text" id="institution" name="institution" style="width: 100%" value="<?php echo htmlspecialchars($_POST['institution'])?>" class="bibtex" tabindex="20" />

		<label for="school" class="block">School</label>
		<input type="text" id="school" name="school" style="width: 100%" value="<?php echo htmlspecialchars($_POST['school'])?>" class="bibtex" tabindex="21" />

		<label for="address" class="block">Address</label>
		<input type="text" id="address" name="address" style="width: 100%" value="<?php echo htmlspecialchars($_POST['address'])?>" class="bibtex" tabindex="22" />
	</div>

	<div class="unit bibtex"><h4>Pagination</h4>
		<label for="pages" class="block">Pages</label>
		<input type="text" id="pages" name="pages" style="width: 50%" value="<?php echo htmlspecialchars(str_replace('--', '-', $_POST['pages']))?>" class="bibtex" tabindex="23" />
	</div>

	<div class="unit"><h4>Descriptional stuff</h4>
		<label for="note" class="block">Note</label>
		<textarea id="note" name="note" cols="10" rows="10" style="width: 100%" class="bibtex" tabindex="24"><?php echo htmlspecialchars($_POST['note'])?></textarea>

		<label for="abstract" class="block">Abstract</label>
		<textarea id="abstract" name="abstract" cols="10" rows="10" style="width: 100%" class="collapsible" tabindex="25"><?php echo htmlspecialchars($_POST['abstract'])?></textarea>

		<label for="userfields" class="block">User fields</label>
		<textarea id="userfields" name="userfields" cols="10" rows="10" style="width: 100%" class="collapsible" tabindex="6"><?php echo htmlspecialchars($_POST['userfields'])?></textarea>
	</div>

	<div class="unit"><h4>Identification</h4>
		<label for="isbn" class="block">ISBN <em>for books</em></label>
		<input type="text" id="isbn" name="isbn" style="width: 100%" value="<?php echo htmlspecialchars($_POST['isbn'])?>" class="collapsible" tabindex="28" />

		<label for="issn" class="block">ISSN <em>for journals</em></label>
		<input type="text" id="issn" name="issn" style="width: 100%" value="<?php echo htmlspecialchars($_POST['issn'])?>" class="collapsible" tabindex="29" />

		<label for="doi" class="block">DOI <em>of publication</em></label>
		<input type="text" id="doi" name="doi" style="width: 100%" value="<?php echo htmlspecialchars($_POST['doi'])?>" class="collapsible" tabindex="30" />

		<label for="url" class="block">URL <em>of publication</em></label>
		<input type="text" id="url" name="url" style="width: 100%" value="<?php echo htmlspecialchars($_POST['url'])?>" class="collapsible" tabindex="31" />
	</div>

	<div class="submit"><input type="submit" value="save" tabindex="32" /></div>
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
$(function() {
	$('#pub_type').bind('mouseup keyup', function (event) {
		delayRequest('bibliographie_publications_show_fields', Array(event.target.value));
	});

	$('#title').bind('mouseup keyup', function (event) {
		delayRequest('bibliographie_publications_check_title', Array(event.target.value, pub_id));
	});

	bibliographie_authors_input_tokenized ('author', <?php echo json_encode($prePopulateAuthor)?>);
	bibliographie_authors_input_tokenized ('editor', <?php echo json_encode($prePopulateEditor)?>);

	$('#tags').tokenInput('<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/tags/ajax.php?task=searchTags', {
		searchDelay: bibliographie_request_delay,
		minChars: <?php echo ((int) BIBLIOGRAPHIE_SEARCH_MIN_CHARS)?>,
		preventDuplicates: true,
		theme: 'facebook',
		prePopulate: <?php echo json_encode($prePopulateTags).PHP_EOL?>,
		onResult: function (results) {
			$('#tags_tagNotExisting').empty();
			$('#bibliographie_charmap').hide();

			if(results.length == 0)
				$('#tags_tagNotExisting').html('Tag <strong>'+$('#token-input-tags').val()+'</strong> is not existing. <a href="javascript:;" onclick="bibliographie_publications_create_tag(\''+$('#token-input-tags').val()+'\');">Create it here!</a>');

			return results;
		}
	});

	bibliographie_topics_input_tokenized('topics', 'topicsContainer', <?php echo json_encode($prePopulateTopics)?>);

	bibliographie_publications_show_fields($('#pub_type').val());

	$('#content input, #content textarea').charmap();
	$('#bibliographie_charmap').dodge();

	bibliographie_publications_check_title($('#title').val(), pub_id);
});
	/* ]]> */
</script>
<?php
			bibliographie_charmap_print_charmap();
		}
	break;

	case 'showPublication':
		$publication = bibliographie_publications_get_data($_GET['pub_id'], 'assoc');

		if(is_array($publication)){
?>

<em style="float: right">
	<a href="javascript:;" onclick="bibliographie_publications_export_choose_type('<?php echo bibliographie_publications_cache_list(array($publication['pub_id']))?>')"><em><?php echo bibliographie_icon_get('page-white-go')?> Export</em></a>
	<a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=publicationEditor&amp;pub_id=<?php echo ((int) $publication['pub_id'])?>"><?php echo bibliographie_icon_get('page-white-edit')?> Edit publication</a>
</em>
<h3><?php echo htmlspecialchars($publication['title'])?></h3>
<?php
			bibliographie_publications_print_list(array($publication['pub_id']), BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=publicationEditor&amp;pub_id='.((int) $publication['pub_id']), null, false, true);

			echo '<table class="dataContainer"><tr><th colspan="2">Further data of the publication</th></tr>';
			foreach($bibliographie_publication_data as $dataKey => $dataLabel){
				if(!empty($publication[$dataKey])){
					if($dataKey == 'url')
						$publication['url'] = '<a href="'.$publication['url'].'">'.$publication['url'].'</a>';

					elseif($dataKey == 'booktitle')
						$publication['booktitle'] = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showContainer&amp;type=book&amp;container='.htmlspecialchars($publication['booktitle']).'">'.htmlspecialchars($publication['booktitle']).'</a>';

					elseif($dataKey == 'journal')
						$publication['journal'] = '<a href="'.BIBLIOGRAPHIE_WEB_ROOT.'/publications/?task=showContainer&amp;type=journal&amp;container='.htmlspecialchars($publication['journal']).'">'.htmlspecialchars($publication['journal']).'</a>';

					elseif($dataKey == 'user_id')
						$publication['user_id'] = bibliographie_user_get_name($publication['user_id']);

					else
						$publication[$dataKey] = htmlspecialchars($publication[$dataKey]);

					echo '<tr><td><strong>'.$dataLabel.'</strong></td><td>'.$publication[$dataKey].'</td></tr>';
				}elseif(in_array($dataKey, array('authors', 'editors', 'topics', 'tags'))){
					$notEmpty = false;
					if($dataKey == 'authors'){
						$authors = bibliographie_publications_get_authors($publication['pub_id'], 'name');
						if(is_array($authors) and count($authors) > 0){
							$notEmpty = true;

							foreach($authors as $author)
								$publication['authors'] .= bibliographie_authors_parse_data($author, array('linkProfile' => true)).'<br />';
						}
					}elseif($dataKey == 'editors'){
						$editors = bibliographie_publications_get_editors($publication['pub_id'], 'name');
						if(is_array($editors) and count($editors) > 0){
							$notEmpty = true;

							foreach($editors as $editor)
								$publication['editors'] .= bibliographie_authors_parse_data($editor, array('linkProfile' => true)).'<br />';
						}
					}elseif($dataKey == 'topics'){
						$topics = bibliographie_publications_get_topics($publication['pub_id']);
						if(is_array($topics) and count($topics) > 0){
							$notEmpty = true;

							foreach($topics as $topic)
								$publication['topics'] .= bibliographie_topics_parse_name($topic, array('linkProfile' => true)).'<br />';
						}
					}elseif($dataKey == 'tags'){
						$tags = bibliographie_publications_get_tags($publication['pub_id']);
						if(is_array($tags) and count($tags) > 0){
							$notEmpty = true;

							foreach($tags as $tag)
								$publication['tags'] .= bibliographie_tags_parse_tag($tag, array('linkProfile' => true)).'<br />';
						}
					}

					if($notEmpty)
						echo '<tr><td><strong>'.$dataLabel.'</strong></td><td>'.$publication[$dataKey].'</td></tr>';
				}
			}
			echo '</table>';
		}
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';