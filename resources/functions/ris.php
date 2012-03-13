<?php

namespace bibliographie;

class RISTranslator {

	private $allocations = array (
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
	);

	public function bibtex2ris (array $data) {
		$result = array();

		foreach($data as $key => $content)
			if(is_array($this->allocations[$key]))
				$result[$this->allocations[$key][0]] = $content;
			else
				$result[$this->allocations[$key]] = $content;

		return $result;
	}

	public function ris2bibtex (array $data) {
		$result = array();

		foreach($data as $i => $entry){
			foreach($this->allocations as $bibtex => $allocation){
				if(is_array($allocation)){
					foreach($allocation as $possibility){
						if(!empty($entry[$possibility])){
							if($bibtex == 'author')
								$result[$i][$bibtex][] = $entry[$possibility][0];
							else
								$result[$i][$bibtex] .= $entry[$possibility][0].PHP_EOL;
						}
					}

				}elseif(!empty($entry[$allocation])){
					$result[$i][$bibtex] = $entry[$allocation][0];
				}
			}
		}

		return $result;
	}

}