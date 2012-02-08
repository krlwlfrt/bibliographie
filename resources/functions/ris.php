<?php
class RISParser {
	private
		$content,
		$data;

	/**
	 * Create a new RISParser with some RIS content.
	 * @param string $content
	 */
	function __construct ($content = '') {
		$this->content = $content;
	}

	/**
	 * Parses given content.
	 */
	function parse () {
		if(!empty($this->content)){
			$lines = explode("\n", $this->content);
			$pages = array();
			foreach($lines as $line){
				preg_match('~^([A-Z0-9]{2})\s+\-\s(.*)$~', $line, $match);
				if(count($match) == 3){
					list($line, $key, $value) = $match;

					if($key == 'TY')
						$this->data['pub_type'] = $value;
					elseif(in_array($key, array('AU', 'A2', 'A3', 'A4'))){
						$value = explode(',', $value);
						$this->data['author'][] = array (
							'last' => $value[0],
							'von' => '',
							'first' => $value[1],
							'jr' => ''
						);
					}elseif($key == 'AB')
						$this->data['abstract'] = $value;
					elseif($key == 'CY')
						$this->data['location'] = $value;
					elseif($key == 'PY')
						$this->data['year'] = $value;
					elseif($key == 'DO')
						$this->data['doi'] = $value;
					elseif($key == 'ET')
						$this->data['edition'] = $value;
					elseif($key == 'N1')
						$this->data['note'] = $value;
					elseif($key == 'PB')
						$this->data['publisher'] = $value;
					elseif($key == 'SN'){
						$this->data['isbn'] = $value;
						$this->data['issn'] = $value;
					}elseif($key == 'TI')
						$this->data['title'] = $value;
					elseif($key == 'UR')
						$this->data['url'] = $value;
					elseif($key == 'VL')
						$this->data['volume'] = $value;
					elseif($key == 'EP')
						$pages[1] = $value;
					elseif($key == 'SP')
						$pages[0] = $value;
					elseif($key == 'JO')
						$this->data['journal'] = $value;
				}
			}
			if(count($pages) == 2)
				$this->data['pages'] = $pages[0].'-'.$pages[1];
		}
	}

	/**
	 * Give the parsed data.
	 * @return mixed Returns data on successful parsing or false otherwise.
	 */
	function data () {
		//if(!is_array($this->data))
		//	return false;

		return $this->data;
	}
}