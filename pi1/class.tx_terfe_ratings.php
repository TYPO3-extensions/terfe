<?php

class tx_terfe_ratings{

	var $minRating = 1;
	var $maxRating = 5;
	var $ratingItems = array (1 => 'useless',2=> 'ok',3=>'average',4=>'good',5=>'excellent');
	
	function __construct($extRow,&$backref){
		global $TSFE;
		$this->extRow = $extRow;
		$this->extensionKey = $extRow['extensionkey'];
		$this->version = $extRow['version'];
		$this->username = $TSFE->fe_user->user['username'];
		$this->backRef = &$backref;
	}


	
	
	function renderSingleView_rating() {
		global $TSFE;
	//	return $this->db_getRatings($this->extensionKey,$this->version,$this->username);

		if (t3lib_div::_GP('rating') && $this->can_be_rated()){
			$res = $this->db_saveRating(t3lib_div::_GP('rating'),t3lib_div::_GP('notes'),$this->username);
			if ($res) {
				tslib_fe::clearPageCacheContent_pidList($TSFE->id);
				$justsaved = 1;
			} else {
				$output .= "Could not save!";
			}
			
		}
		// Rendering current rating
		$output = '<em>Average rating:</em> ';
		
//		$rating = $this->db_getAvgRating($this->extensionKey);
//		$output .= $this->num_to_star($rating['average']);

		// Rendering form
		if (!$justsaved && $this->can_be_rated()){
			$output .= $this->render_rating_form();
		}

		// Rendering rating history
		$ratings = $this->db_getRatings($this->extensionKey,$this->version);
		if (is_array($ratings)){
			
			$output .= $this->render_rating($rating_summary[$this->version]['avg'],$rating_summary[$this->version]['count']);

			$output .= '<h2>Ratings for this extension version</h2><table><thead><tr><th>User</th><th>Rating</th><th>Notes</th></tr></thead>';
			
			foreach ($ratings as $rating){
				$output .= '<tr><td>'.$rating['username'].'</td><td>'.$this->num_to_star($rating['rating']).'</td><td>'.htmlspecialchars($rating['notes']).
				'</td></tr>';
			}
			$output .= '</table>';
		}
		return $output;
	}

	function render_rating($avg, $num){
		$output = '<span class="t3rating_summary">';
		$output .= $avg .'('.$num.' ratings)';
		$output .= '</span>';
		return $output;

	}

	
	function can_be_rated(){
		
		if(!$this->username){
			return False;
		}
		
		if ($this->extRow['authorname'] == $this->username){
			return False;
		}
		if ($ratings = $this->db_getRatings($this->extensionKey, $this->version, $this->username)){
			return False;
		}
		return True;
	
	}


	function render_rating_form(){
	
		$output =	'<form action="'.t3lib_div::linkThisScript().'" method="POST" class="rating">';
		
	//	$output .= '<fieldset style="float:left;">';
		$outrows = array();
		for ($i=$this->minRating;$i <= $this->maxRating; $i++){
			$outrows[] =	'<label for="rating'.$i.'"><input type="radio" name="rating" id="rating'.$i.'" value="'.$i.'"/>'
				.$i.' - '.$this->ratingItems[$i].' </label><br/>';
		}

		$output .= implode('',array_reverse($outrows));
	//	$output .= '</fieldset>';
		
		$output .= '<label for="notes">Add your review notes</label><br/><textarea name="notes" id="notes" cols="50" rows="5"></textarea>';
		$output .= '<input type="hidden" name="no_cache" value="1"/>';
		$output .= '<input type="submit" name="submit" value="Submit rating"/></form>';
		return $output;


	}

	function rating_summary($ratings){
		if (!is_array($ratings)){
			return False;
		}
		
		$rating_counter = array();
		
		foreach ($ratings as $rating_row){
			$rating_counter[$rating_row['version']][] = $rating_row['rating'];
		}

		$rating_avgs = array();

		foreach ($rating_counter as $version => $rating_arr){
			$rating_avgs[$version]['avg'] = array_sum($rating_arr)/count($rating_arr);
			$rating_avgs[$version]['count'] = count($rating_arr);
		}
		return $rating_avgs;
	}

	function get_valid_avg($rating_summary,$version){
		/*	wenn genug Einträge, dann aktuelle Version, sonst ältere mit genug oder alle? */

	}

	
	
	function num_to_star($int){
		$roundedvalue = round(($int*2), 0)/2;
		$img = '<img src="fileadmin/stars/' . $roundedvalue. '.png"'.
			'alt="'.$roundedvalue.' stars out of 5" '.
			'title="'.$roundedvalue.' stars out of 5"/>';
		return $img;

	}

	

	
	function db_saveRating($rating, $notes=''){

		if ( $rating > $this->maxRating || $rating < $this->minRating) {return False;}
		global $TYPO3_DB;
		$table = 'tx_terfe_ratings';
		$insertArr = array(
			'extensionkey' => $TYPO3_DB->quoteStr($this->extensionKey,$table),
			'version' => $TYPO3_DB->quoteStr($this->version,$table),
			'rating' => intval($rating),
			'notes' => $TYPO3_DB->quoteStr($notes,$table),
			'username' =>  $TYPO3_DB->quoteStr($this->username,$table),
			'tstamp' => time()
		);
 
		$query = $TYPO3_DB->INSERTquery($table,$insertArr);
		
		return $TYPO3_DB->sql(TYPO3_db,$query) ? True : $query;
	}


	function db_getRatings($extensionkey,$version='',$username=''){
		
		global $TYPO3_DB, $TSFE;
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			'tx_terfe_ratings',
			'extensionkey =' . $TYPO3_DB->fullQuoteStr($extensionkey,'tx_terfe_ratings')
			.($version ? ' AND version = ' . $TYPO3_DB->fullQuoteStr($version,'tx_terfe_ratings') : '')
			.($username ? ' AND username = ' . $TYPO3_DB->fullQuoteStr($username,'tx_terfe_ratings') : ''),
				'',
				'version'
		);
		

		if ($res) {
			$ratings = array();
			while ($ratingRow = $TYPO3_DB->sql_fetch_assoc($res)) 
			{
				$ratings[]=$ratingRow;
			}
		
			return $ratings;
		}
	}

	
	function db_getAvgRating($extensionkey,$version=''){
		
		global $TYPO3_DB, $TSFE;
		$res = $TYPO3_DB->exec_SELECTquery (
			'AVG(rating) as average, COUNT(rating) as num_ratings',
			'tx_terfe_ratings',
			'extensionkey =' . $TYPO3_DB->fullQuoteStr($extensionkey,'tx_terfe_ratings')
			.($version ? ' AND version = ' . $TYPO3_DB->fullQuoteStr($version,'tx_terfe_ratings') : '')
		);
		

		if ($res) {
			$ratings = $TYPO3_DB->sql_fetch_assoc($res);
			return $ratings;
		}
	}

}


?>
