<?php

define('BIBLIOGRAPHIE_ROOT_PATH', '..');
define('BIBLIOGRAPHIE_OUTPUT_BODY', false);

require BIBLIOGRAPHIE_ROOT_PATH.'/functions.php';

switch($_GET['task']){
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
<button onclick="bibliographie_fetch_data_proceed({'source': 'bibtexInput', 'step': '2', 'bibtexInput': $('#bibtexInput').val()})">Parse & proceed!</button>
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
<button onclick="bibliographie_fetch_data_proceed({'source': 'bibtexRemote', 'step': '2', 'bibtexRemote': $('#bibtexRemote').val()})">Parse & proceed!</button>
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
			if($_POST['step'] == '1'){

			}elseif($_POST['step'] == '2'){
				/*
				 * /api/books.xml?access_key=BIBLIOGRAPHIE_ISBNDB_KEY&results=authors&index1=isbn&value1=ISBN
				 * /api/books.xml?access_key=BIBLIOGRAPHIE_ISBNDB_KEY&results=authors&index1=full&value1=TEXT
				*/

				$response = '';

				$response = json_decode(json_encode(simplexml_load_string($response)), true);
				if(!is_array($response['BookList']['BookData']))
					echo 'Result was empty!';

				/**
				 * Map unique results to the structure of multiple results for convenience...
				 */
				if($response['BookList']['@attributes']['shown_results'] == '1'){
					$dummy = $response['BookList']['BookData'];
					$response['BookList']['BookData'] = null;
					$response['BookList']['BookData'][] = $dummy;
				}

				echo '<pre style="font-size: 0.8em;">'.print_r($response, true).'</pre>';
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
			}
		}

		//echo '<pre>'.print_r($_POST, true).'</pre>';
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