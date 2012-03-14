<?php

namespace bibliographie;

class RISTranslator {

	private $tagAllocations = array (
		'pub_type' => 'TY',
		'bibtex_id' => 'ID',
		'title' => array (
			'T1',
			'TI',
			'CT',
			'T2'
		),
		'author' => array (
			'A1',
			'A2',
			'AU'
		),
		'ris_date' => 'Y1',
		'year' => 'PY',
		'note' => array (
			'N1',
			'L1',
			'L2',
			'L3',
			'L4'
		),
		'tags' => 'KW',
		'start_page' => 'SP',
		'end_page' => 'EP',
		'journal' => array (
			'JF',
			'JO',
			'JA',
			'J1',
			'J2'
		),
		'volume' => 'VL',
		'number' => 'IS',
		'location' => 'CY',
		'publisher' => 'PB',
		'series' => 'T3',
		'abstract' => 'N2',
		'isbn' => 'SN',
		'location' => 'AD',
		'url' => 'UR'
	),
	$typeAllocations = array (
		'Misc' => array (
			'GEN',
			'ABST',
			'ADVS',
			'ART',
			'CASE',
			'COMP',
			'CTLG',
			'DATA',
			'ELEC',
			'HEAR',
			'ICOMM',
			'JFULL',
			'JOUR',
			'MAP',
			'MPCT',
			'MUSIC',
			'NEWS',
			'PAMP',
			'PAT',
			'PCOMM',
			'SLIDE',
			'SOUND',
			'STAT'
		),
		'Article' => array (
			'MGZN'
		),
		'Book' => array (
			'BOOK'
		),
		'Booklet' => array (
			'GEN'
		),
		'Conference' => array (
			'GEN'
		),
		'Inbook' => array (
			'CHAP'
		),
		'Incollection' => array (
			'SER'
		),
		'Inproceedings' => array (
			'INPR',
		),
		'Manual' => array (
			'GEN'
		),
		'Masterthesis' => array (
			'THES'
		),
		'Phdthesis' => array (
			'THES'
		),
		'Proceedings' => array (
			'CONF'
		),
		'Techreport' => array (
			'RPRT',
		),
		'Unpublished' => array (
			'UNPB'
		)
	);

	private function bibtexType2risType ($type) {
		if(isset($this->typeAllocations[ucfirst($type)][0]))
			return $this->typeAllocations[ucfirst($type)][0];

		return 'Misc';
	}

	private function risType2bibtexType ($type) {
		foreach($this->typeAllocations as $bibtexType => $risTypes)
			foreach($risTypes as $risType)
				if($risType == $type)
					return $bibtexType;

		return 'GEN';
	}

	public function bibtex2ris (array $data) {
		$result = array();

		foreach($data as $i => $entry){
			$entry['pub_type'] = $this->bibtexType2risType($entry['entryType']);
			$entry['bibtex_id'] = $entry['cite'];
			unset($entry['entryType'], $entry['cite']);

			foreach($entry as $key => $content){
				$dummy = array();

				if(is_array($content)){
					if($key == 'author')
						foreach($content as $author){
							$dummy[] = $author['last'].', '.$author['first'];
						}

				}else{
					$dummy[] = $content;
				}

				if(is_array($this->tagAllocations[$key]))
					$result[$i][$this->tagAllocations[$key][0]] = $dummy;
				else
					$result[$i][$this->tagAllocations[$key]] = $dummy;
			}
		}

		return $result;
	}

	public function ris2bibtex (array $data) {
		$result = array();

		foreach($data as $i => $entry){
			$result[$i] = array(
				'editor' => array(),
				'author' => array(),
				'tags' => array()
			);
			foreach($this->tagAllocations as $bibtex => $allocation){
				if(is_array($allocation)){
					foreach($allocation as $risTag){
						if(!empty($entry[$risTag])){
							foreach($entry[$risTag] as $row)
								if($bibtex == 'author'){
									$row = explode(', ', $row);
									$result[$i][$bibtex][] = array (
										'last' => $row[0],
										'first' => $row[1],
										'jr' => '',
										'von' => ''
									);
								}
								else
									$result[$i][$bibtex] .= $row.PHP_EOL;
						}
					}

				}elseif(!empty($entry[$allocation])){
					$result[$i][$bibtex] = $entry[$allocation][0];
				}
			}

			if(!empty($result[$i]['start_page']) and !empty($result[$i]['end_page']))
				$result[$i]['pages'] = $result[$i]['start_page'].'-'.$result[$i]['end_page'];

			$result[$i]['pub_type'] = $this->risType2bibtexType($result[$i]['pub_type']);
		}

		return $result;
	}

}