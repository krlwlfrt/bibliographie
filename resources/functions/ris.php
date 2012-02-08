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
			$references = array();
			preg_match_all('~TY\s+\-.*?ER\s+-~is', $this->content, $references, PREG_SET_ORDER);

			if(count($references) == 0)
				return false;

			foreach($references as $reference){
				$pages = array();

				preg_match_all('~([A-Z0-9]{2})\s+\-\s+(.*)~', $reference[0], $tags, PREG_SET_ORDER);
				$reference = array(
					'author' => array(),
					'editor' => array()
				);

				foreach($tags as $tag){
					if(count($tag) == 3){
						list($line, $key, $value) = $tag;

						if($key == 'TY')
							$reference['pub_type'] = $value;
						elseif(in_array($key, array('AU', 'A2', 'A3', 'A4'))){
							$value = explode(',', $value);
							$reference['author'][] = array (
								'last' => $value[0],
								'von' => '',
								'first' => $value[1],
								'jr' => ''
							);
						}elseif($key == 'AB')
							$reference['abstract'] = $value;
						elseif($key == 'CY')
							$reference['location'] = $value;
						elseif($key == 'PY')
							$reference['year'] = $value;
						elseif($key == 'DO')
							$reference['doi'] = $value;
						elseif($key == 'ET')
							$reference['edition'] = $value;
						elseif($key == 'N1')
							$reference['note'] = $value;
						elseif($key == 'PB')
							$reference['publisher'] = $value;
						elseif($key == 'SN'){
							$reference['isbn'] = $value;
							$reference['issn'] = $value;
						}elseif($key == 'TI')
							$reference['title'] = $value;
						elseif($key == 'UR')
							$reference['url'] = $value;
						elseif($key == 'VL')
							$reference['volume'] = $value;
						elseif($key == 'EP')
							$pages[1] = $value;
						elseif($key == 'SP')
							$pages[0] = $value;
						elseif($key == 'JO')
							$reference['journal'] = $value;
					}
				}
				if(count($pages) == 2)
					$reference['pages'] = $pages[0].'-'.$pages[1];

				$this->data[] = $reference;
			}
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