<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
	case 'exportPublications':
		$publications = bibliographie_publications_get_cached_list($_GET['exportList']);

		if(is_array($publications) and count($publications) > 0){
			$mysql_string = "";

			foreach($publications as $publication){
				if(!empty($mysql_string))
					$mysql_string .= " OR ";

				$mysql_string .= "`pub_id` = ".((int) $publication);
			}

			$result = mysql_query("SELECT `pub_id`, `pub_type`, `bibtex_id`, `address`, `booktitle`, `chapter`, `edition`, `howpublished`, `institution`, `journal`, `month`, `note`, `number`, `organization`, `pages`, `publisher`, `school`, `series`, `title`, `url`, `volume`, `year` FROM `a2publication` WHERE ".$mysql_string." ORDER BY `title`");
			if(mysql_num_rows($result) > 0){
				$bibtex = new Structures_BibTex(array(
					'stripDelimiter' => true,
					'validate' => true,
					'unwrap' => true,
					'removeCurlyBraces' => true,
					'extractAuthors' => true
				));

				while($publication = mysql_fetch_assoc($result)){
					$publication['entryType'] = $publication['pub_type'];
					if(empty($publication['bibtex_id']))
						$publication['bibtex_id'] = md5($publication['title']);
					$publication['cite'] = $publication['bibtex_id'];

					$authors = bibliographie_publications_get_authors($publication['pub_id']);
					$editors = bibliographie_publications_get_editors($publication['pub_id']);

					unset($publication['pub_id'], $publication['pub_type'], $publication['bibtex_id']);

					if(is_array($authors) and count($authors) > 0)
						foreach($authors as $author)
							$publication['author'][] = bibliographie_authors_parse_data($author, array('forBibTex' => true));

					if(is_array($editors) and count($editors) > 0)
						foreach($editors as $editor)
							$publication['editor'][] = bibliographie_authors_parse_data($editor, array('forBibTex' => true));

					$_publication = array();
					foreach($publication as $key => $field)
						if(!empty($field))
							$_publication[$key] = $field;

					$bibtex->data[] = $_publication;
				}

				if($_GET['target'] == 'bibTex'){
					header('Content-Type: text/plain; charset=UTF-8');
					echo $bibtex->bibtex();
				}elseif($_GET['target'] == 'rtf'){
					$rtf = $bibtex->rtf();
					$file = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/cache/export_'.md5($rtf).'.rtf', 'w+');
					fwrite($file, $rtf);
					fclose($file);
					header('Location: '.BIBLIOGRAPHIE_ROOT_PATH.'/cache/export_'.md5($rtf).'.rtf');
				}

			}
		}
	break;

	case 'checkData':
		if($_GET['subTask'] == 'approvePerson'){
			if(!is_array($_SESSION['publication_prefetchedData_unchecked'][$_GET['outerID']]['checked_'.$_GET['role']]))
				$_SESSION['publication_prefetchedData_unchecked'][$_GET['outerID']]['checked_'.$_GET['role']] = array();

			$_SESSION['publication_prefetchedData_unchecked'][$_GET['outerID']]['checked_'.$_GET['role']][$_GET['innerID']] = $_GET['personID'];
			echo bibliographie_icon_get('tick').' Person has been approved as '.$_GET['role'].'!';
		}elseif($_GET['subTask'] == 'createPerson'){
			$data = bibliographie_authors_create_author($_GET['first'], $_GET['von'], $_GET['last'], $_GET['jr'], '', '', '');
			if(is_array($data)){
				$_SESSION['publication_prefetchedData_unchecked'][$_GET['outerID']]['checked_'.$_GET['role']][$_GET['innerID']] = $data['author_id'];
				echo bibliographie_icon_get('tick').' Person has been created and approved as '.$_GET['role'].'!';
			}else
				echo '<p class="error">Person could not be created!</p>';
		}elseif($_GET['subTask'] == 'approveEntry'){
			if(count($_SESSION['publication_prefetchedData_unchecked'][$_GET['outerID']]['checked_author']) == count($_SESSION['publication_prefetchedData_unchecked'][$_GET['outerID']]['author'])
				and count($_SESSION['publication_prefetchedData_unchecked'][$_GET['outerID']]['checked_editor']) == count($_SESSION['publication_prefetchedData_unchecked'][$_GET['outerID']]['editor'])){
				$_SESSION['publication_prefetchedData_checked'][$_GET['outerID']] = $_SESSION['publication_prefetchedData_unchecked'][$_GET['outerID']];

				echo json_encode(array(
					'text' => bibliographie_icon_get('tick').' Parsed entry has been approved and added to queue!',
					'status' => 'success'
				));
			}else
				echo json_encode(array(
					'text' => bibliographie_icon_get('cross').' Sorry but you can not approve an entry if there are authors left that are not approved!',
					'status' => 'error'
				));
		}
	break;

	case 'fetchData_proceed':
		if($_POST['source'] == 'bibtexInput'){
?>

<strong>1. step</strong> Selected source <em>BibTex input</em>... <span class="silk-icon silk-icon-tick"></span><br />
<?php
			if($_POST['step'] == '1'){
?>

<strong>2. step</strong> Input BibTex string... <span class="silk-icon silk-icon-hourglass"></span>
<label for="bibtexInput" class="block">BibTex input</label>
<textarea id="bibtexInput" name="bibtexInput" rows="20" cols="20" style="width: 100%;"></textarea>
<button onclick="bibliographie_fetch_data_proceed({'source': 'bibtexInput', 'step': '2', 'bibtexInput': $('#bibtexInput').val()})">Proceed & parse!</button>
<?php
			}elseif($_POST['step'] == '2'){
				if(!empty($_POST['bibtexInput'])){
?>

<strong>2. step</strong> Input BibTex string... <span class="silk-icon silk-icon-tick"></span><br />
<?php
					$bibtex = new Structures_BibTex(array(
						'stripDelimiter' => true,
						'validate' => true,
						'unwrap' => true,
						'removeCurlyBraces' => true,
						'extractAuthors' => true
					));
					$bibtex->loadContent($_POST['bibtexInput']);

					if($bibtex->parse() and count($bibtex->data) > 0){
?>

<strong>3. step</strong> Parsing BibTex... <span class="silk-icon silk-icon-tick"></span><br />
<?php
						foreach($bibtex->data as $key => $row){
							$bibtex->data[$key]['pub_type'] = $row['entryType'];
							$bibtex->data[$key]['bibtex_id'] = $row['cite'];
						}

						$_SESSION['publication_prefetchedData_unchecked'] = $bibtex->data;
?>

<p>
	<span class="success">Parsing of your input was successful!</span>
	Your input contained <strong><?php echo count($bibtex->data)?></strong> entry/entries.</strong><br />
	You can now proceed and <a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=checkData">check your fetched data</a>.
</p>
<?php
					}else{
?>

<strong>3. step</strong> Parsing BibTex... <span class="silk-icon silk-icon-cross"></span>
<p class="error">There was an error while parsing!</p>
<?php
					}
				}else{
?>

<strong>2. step</strong> Input BibTex string... <span class="silk-icon silk-icon-cross"></span>
<p class="error">Your input was empty!</p>
<?php
				}
			}
		}elseif($_POST['source'] == 'bibtexRemote'){
			?>

<strong>1. step</strong> Selected source <em>BibTex remote</em>... <span class="silk-icon silk-icon-tick"></span><br />
<?php
			if($_POST['step'] == '1'){
?>

<strong>2. step</strong> Input BibTex URL... <span class="silk-icon silk-icon-hourglass"></span>
<label for="bibtexRemote" class="block">BibTex input</label>
<input id="bibtexRemote" name="bibtexRemote" style="width: 100%" />
<button onclick="bibliographie_fetch_data_proceed({'source': 'bibtexRemote', 'step': '2', 'bibtexRemote': $('#bibtexRemote').val()})">Proceed & parse!</button>
<?php
			}elseif($_POST['step'] == '2'){
				if(!empty($_POST['bibtexRemote']) and is_url($_POST['bibtexRemote'])){
?>

<strong>2. step</strong> Input BibTex URL... <span class="silk-icon silk-icon-tick"></span><br />
<?php
					$bibtex = new Structures_BibTex(array(
						'stripDelimiter' => true,
						'validate' => true,
						'unwrap' => true,
						'removeCurlyBraces' => true,
						'extractAuthors' => true
					));
					$bibtex->loadContent(file_get_contents($_POST['bibtexRemote']));

					if($bibtex->parse() and count($bibtex->data) > 0){
?>

<strong>3. step</strong> Parsing BibTex... <span class="silk-icon silk-icon-tick"></span><br />
<?php
						foreach($bibtex->data as $key => $row){
							$bibtex->data[$key]['pub_type'] = $row['entryType'];
							$bibtex->data[$key]['bibtex_id'] = $row['cite'];
						}

						$_SESSION['publication_prefetchedData_unchecked'] = $bibtex->data;
?>

<p>
	<span class="success">Parsing of your input was successful!</span>
	Your input contained <strong><?php echo count($bibtex->data)?></strong> entry/entries.</strong><br />
	You can now proceed and <a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=checkData">check your fetched data</a>.
</p>
<?php
					}else{
?>

<strong>3. step</strong> Parsing BibTex... <span class="silk-icon silk-icon-cross"></span>
<p class="error">There was an error while parsing!</p>
<?php
					}
				}else{
?>

<strong>2. step</strong> Input BibTex URL... <span class="silk-icon silk-icon-cross"></span>
<p class="error">Your input was empty!</p>
<?php
				}
			}
		}elseif($_POST['source'] == 'isbndb'){
?>

<strong>1. step</strong> Selected source <em>ISBNDB</em>... <span class="silk-icon silk-icon-tick"></span><br />
<?php
			if($_POST['step'] == '1'){
?>

<strong>2. step</strong> Input query...<span class="silk-icon silk-icon-hourglass"></span>

<br />

<div style="float: right; width: 50%">
	<label for="value" class="block">Query</label>
	<input type="text" id="value" name="value" style="width: 100%" />
</div>

<label for="key" class="block">Range</label>
<select id="key" name="key" style="width: 45%">
	<option value="isbn">ISBN #</option>
	<option value="title">In field title</option>
	<option value="combined">In fields title, authors and publisher</option>
	<option value="full">Fulltext</option>
</select>

<button onclick="bibliographie_fetch_data_proceed({'source': 'isbndb', 'step': '2', 'key': $('#key').val(), 'value': $('#value').val()})">Search</button>
<?php
			}elseif($_POST['step'] == '2'){
?>


<strong>2. step</strong> Input query...<span class="silk-icon silk-icon-tick"></span><br />
<?php

				$response = '';
				if(in_array($_POST['key'], array('isbn', 'full', 'title', 'combined'))){
					if($_POST['key'] == 'isbn')
						$_POST['value'] = str_replace('-', '', $_POST['value']);

					$response = file_get_contents('http://www.isbndb.com/api/books.xml?access_key='.BIBLIOGRAPHIE_ISBNDB_KEY.'&results=authors&index1='.$_POST['key'].'&value1='.strip_tags($_POST['value']));
				}

				$response = json_decode(json_encode(simplexml_load_string($response)), true);
				if(is_array($response['BookList']['BookData'])){
?>

<strong>3. step</strong> Parsing result...<span class="silk-icon silk-icon-tick"></span>
<?php

					/**
					 * Map unique results to the structure of multiple results for convenience...
					 */
					if($response['BookList']['@attributes']['shown_results'] == '1'){
						$dummy = $response['BookList']['BookData'];
						$response['BookList']['BookData'] = null;
						$response['BookList']['BookData'][] = $dummy;
					}

					$i = 0;
					foreach($response['BookList']['BookData'] as $book){
						$_SESSION['publication_prefetchedData_unchecked'][$i]['title'] = $book['Title'];
						if(is_string($book['TitleLong']))
							$_SESSION['publication_prefetchedData_unchecked'][$i]['title'] = $book['TitleLong'];

						$_SESSION['publication_prefetchedData_unchecked'][$i]['isbn'] = $book['@attributes']['isbn'];
						if(!empty($book['@attributes']['isbn13']))
							$_SESSION['publication_prefetchedData_unchecked'][$i]['isbn'] = $book['@attributes']['isbn13'];

						$_SESSION['publication_prefetchedData_unchecked'][$i]['publisher'] = $book['PublisherText'];

						if(is_array($book['Authors']['Person'])){
							foreach($book['Authors']['Person'] as $author){
								$author = explode(',', $author);
								$_SESSION['publication_prefetchedData_unchecked'][$i]['author'][] = array (
									'first' => $author[1],
									'von' => '',
									'last' => $author[0],
									'jr' => ''
								);
							}
						}else{
							$author = explode(',', $book['Authors']['Person']);
							$_SESSION['publication_prefetchedData_unchecked'][$i]['author'][0] = array (
								'first' => $author[1],
								'von' => '',
								'last' => $author[0],
								'jr' => ''
							);
						}
						$i++;
					}
?>

<p>
	<span class="success">Parsing of your search was successful!</span> You can now proceed and <a href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/publications/?task=checkData">check your fetched data</a>.<br /><br />
<?php if($response['BookList']['@attributes']['total_results'] > $i){ ?>
	Your search result contained <?php echo ((int) $response['BookList']['@attributes']['total_results'])?> results. Due to service limitations only the first 10 entries can be shown. If the result didn't contain the book you searched for, try to narrow down your search via the query!
<?php } ?>
</p>
<?php
				}else{
?>

<strong>3. step</strong> Parsing result...<span class="silk-icon silk-icon-cross"></span>
<p class="error">Result was empty!</p>
<?php
				}
			}
		}
	break;

	case 'checkTitle':
		$result = array(
			'count' => 0,
			'results' => array(),
			'status' => 'error'
		);

		if(mb_strlen($_GET['title']) >= 3){
			$result['status'] = 'success';

			$searchResults = mysql_query("SELECT * FROM (SELECT `pub_id`, `title`, (MATCH(`title`) AGAINST ('".mysql_real_escape_string(stripslashes($_GET['title']))."' IN NATURAL LANGUAGE MODE)) AS `relevancy` FROM `a2publication`) fullTextSearch WHERE `pub_id` != ".((int) $_GET['pub_id'])." AND `relevancy` > 0 ORDER BY `relevancy` DESC");

			$results = array();
			if(mysql_num_rows($searchResults) > 0){
				$result['count'] = mysql_num_rows($searchResults);
				while($publication = mysql_fetch_object($searchResults) and count($results) < ceil(log(mysql_num_rows($searchResults), 2) + 1) * 2){
					if(mb_strtolower($publication->title) == mb_strtolower($_GET['title']))
						$publication->title = '<strong>'.$publication->title.'</strong>';
					$results[] = $publication;
				}
				$result['results'] = $results;
			}
		}

		echo json_encode($result);
	break;

	case 'getFields':
		$result = array();
		if(array_key_exists(mb_strtolower($_GET['type']), $bibliographie_publication_fields)){
			foreach($bibliographie_publication_fields[mb_strtolower($_GET['type'])] as $flag => $fields){
				foreach($fields as $field)
					$result[] = array('field'=>$field,'flag'=>$flag);
			}
		}

		echo json_encode($result);
	break;
}

require BIBLIOGRAPHIE_ROOT_PATH.'/close.php';