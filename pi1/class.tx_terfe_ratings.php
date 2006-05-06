<?php
	 
	class tx_terfe_ratings {
		 
		var $minRating = 1;
		var $maxRating = 5;
		var $minNumRatings = 3;
		var $ratingItems = array (1 => 'useless', 2 => 'bad', 3 => 'average', 4 => 'good', 5 => 'excellent');
		 
		function __construct($extensionRecord, $backref) {
			global $TSFE;
			$this->extRow = $extensionRecord;
			$this->extensionKey = $extensionRecord['extensionkey'];
			$this->version = $extensionRecord['version'];
			$this->username = $TSFE->fe_user->user['username'];
			$this->backRef = $backref;
		}
		 
		/**
		* [Describe function...]
		*
		* @return [type]  ...
		*/
		function renderSingleView_rating() {
			global $TSFE;
			$ratingsArr = t3lib_div::_POST('rating');

			if ($ratingsArr && $this->canBeRated()) {
				$res = $this->db_saveRating($ratingsArr, t3lib_div::_POST('notes'), $this->username);
				if ($res) {
					$TSFE->clearPageCacheContent();
				} else {
					$output .= '<em>'.$this->backRef->pi_getLL('extensioninfo_ratings_errornotsaved','').'</em>';
				}
				 
			}
			 
			// Rendering rating history
			$history = $this->render_ratingList($this->db_getRatings($this->extensionKey));
			if ($history) {
				$output .= '<li>'.$history.'</li>';
			}
			 
			// Rendering form
			if ($this->canBeRated()) {
				$output .= '<li>'.$this->render_ratingForm().'</li>';
			}
			 
			return $output;
		}
		 
		/**
		* [Describe function...]
		*
		* @param [type]  $ratings: ...
		* @return [type]  ...
		*/
		function render_ratingList($ratings) {
			if (is_array($ratings[0])) {
				$output .= '<table class="ext-compactlist review-hist">
					<thead><tr>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_version','').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_username','').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_funcrating','').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_docrating','').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_coderating','').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_overallrating','').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_notes','').'</th>
					</tr></thead>';
				foreach ($ratings as $rating) {
					if ($rating['version'] > $this->version) {
						continue;
					}
					else if ($rating['version'] == $this->version) {
						$output .= '<tr class="current">';
					} else {
						$output .= '<tr>';
					}
					$output .= '
						<td>'.$rating['version'].'</td>
						<td>'.$rating['username'].'</td>
						<td>'.$this->render_starWrap($rating['funcrating']).'</td>
						<td>'.$this->render_starWrap($rating['docrating']).'</td>
						<td>'.$this->render_starWrap($rating['coderating']).'</td>
						<td>'.$this->render_starWrap($rating['overall']).'</td>
						<td>'.htmlspecialchars($rating['notes']). '</td></tr>';
				}
				$output .= '</table>';
				return $output;
			} else {
				return FALSE;
			}
		}
		/**
		* [Describe function...]
		*
		* @return [type]  ...
		*/
		private function render_ratingForm() {
			$valuerows = array();
			for ($i = $this->minRating; $i <= $this->maxRating; $i++) {
				$valuerows[] = '<option value="'.$i.'">'.$this->ratingItems[$i].'</option>';
			}
			 
			$output = '<form action="'.t3lib_div::linkThisScript().'" method="POST" class="rating">';
			$output .= '<fieldset><legend>Rate this extension</legend>';
			$output .= '<div>';
			 
			$output .= '<label for="funcrating">'.$this->backRef->pi_getLL('extensioninfo_ratings_funcrating','').'</label><select name="rating[funcrating]" id="funcrating"><option value=""></option>';
			$output .= implode('', array_reverse($valuerows));
			$output .= '</select>';
			 
			$output .= '<label for="docrating">'.$this->backRef->pi_getLL('extensioninfo_ratings_docrating','').'</label><select name="rating[docrating]" id="docrating"><option value=""></option>';
			$output .= implode('', array_reverse($valuerows));
			$output .= '</select>';
			 
			$output .= '<label for="coderating">'.$this->backRef->pi_getLL('extensioninfo_ratings_coderating','').'</label><select name="rating[coderating]" id="coderating"><option value="" ></option>';
			$output .= implode('', array_reverse($valuerows));
			$output .= '</select>';
			 
			 
			$output .= '</div><div>';
			 
			$output .= '<label for="notes">'.$this->backRef->pi_getLL('extensioninfo_ratings_addnotes','').'</label><br/><textarea name="notes" id="notes" cols="50" rows="5"></textarea>';
			$output .= '<input type="hidden" name="no_cache" value="1"/>';
			$output .= '<input type="submit" name="submit" value="Submit rating"/></div>';
			$output .= '</fieldset></form>';
			return $output;
			 
			 
		}
		/**
		* [Describe function...]
		*
		* @param [type]  $int: ...
		* @return [type]  ...
		*/
		public static function render_starWrap($num) {
			if (!$num) {
				return '';
			}
			$roundedvalue = round(($num * 2), 0)/2;
			return '<span class="ext_rating stars'. $roundedvalue * 10 .'">' . $roundedvalue . ' out of 5</span>';
		}
		 
		/**
		* [Describe function...]
		*
		* @param [type]  $ratings: ...
		* @param [type]  $allRating: ...
		* @return [type]  ...
		*/
		private function comp_weightedRating($extensionKey, $version) {
			$versionRating = $this->db_getAvgRating($this->extensionKey, $this->version);
			$allRating = $this->db_getAvgRating($this->extensionKey, $this->version, 1);
			
			$counter = 0;
			if (is_array($versionRating) && $versionRating['num'] >= $this->minNumRatings) {
				$version = $versionRating['avg'];
				$counter++;
			}
			 
			if (is_array($allRating) && $allRating['num'] >= $this->minNumRatings) {
				$all = $allRating['avg'];
				$counter++;
			} else {
				return FALSE;
			}
			$realRating = ($version + $all)/$counter;
			$realNumber = $allRating['num'];
			return array ($realRating, $realNumber);
		}
		 
		/**
		* [Describe function...]
		*
		* @param [type]  $ratings: ...
		* @return [type]  ...
		* @access private
		*/
		private function comp_overallAvg($ratings) {
			$ratingsArr = array ($ratings['funcrating'], $ratings['docrating'] , $ratings['coderating']);
			$counter = 0;
			$sum = 0;
			foreach ($ratingsArr as $rating) {
				if ($rating) {
					$sum = $sum + $rating;
					 $counter++;
				}
			}
			if (!$counter) {
				return FALSE;
			}
			return $sum/$counter;
		}
		 
		/**
		* Checks if the current extension version can be rated by the current FE user
		*
		* @return boolean  True if extension can be rated
		*/
		private function canBeRated() {
			if (!$this->username) {
				return FALSE;
			}
			 
			if ($this->extRow['authorname'] == $this->username) {
				return FALSE;
			}
			if ($ratings = $this->db_getRatings($this->extensionKey, $this->version, $this->username)) {
				return FALSE;
			}
			return True;
			 
		}
		 
		 
		 
		/**
		* [Describe function...]
		*
		* @param [type]  $rating: ...
		* @param [type]  $notes: ...
		* @param [type]  $username: ...
		* @return [type]  ...
		*/
		private function db_saveRating($ratingsArr, $notes, $username) {
			 
			foreach ($ratingsArr as $ratingType => $rating) {
				if ($this->maxRating < $rating || $this->minRating > $rating) {
					$ratingsArr[$ratingType] = FALSE;
				}
			}
			 
			if (! $overall = $this->comp_overallAvg($ratingsArr)) {
				return FALSE;
			}
			 
			global $TYPO3_DB;
			$table = 'tx_terfe_ratings';
			$insertArr = array(
			'extensionkey' => $TYPO3_DB->quoteStr($this->extensionKey, $table),
				'version' => $TYPO3_DB->quoteStr($this->version, $table),
				'funcrating' => intval($ratingsArr['funcrating']),
				'docrating' => intval($ratingsArr['docrating']),
				'coderating' => intval($ratingsArr['coderating']),
				'overall' => floatval($overall),
				'notes' => $TYPO3_DB->quoteStr($notes, $table),
				'username' => $TYPO3_DB->quoteStr($username, $table),
				'tstamp' => time()
			);
			 
			$res = $TYPO3_DB->exec_INSERTquery($table, $insertArr);
			
			if (!$res){
				return FALSE;
			}
			
			$cachable_ratings = $this->comp_weightedRating($this->extensionkey, $this->version);
				
			
			if ($cachable_ratings){

				$cachedRatingArr = array (
					'extensionkey' => $TYPO3_DB->quoteStr($this->extensionKey, $table),
					'version' => $TYPO3_DB->quoteStr($this->version, $table),
					'rating' => floatval($cachable_ratings[0]),
					'votes' => intval($cachable_ratings[1])
					);
				$cachingTable = 'tx_terfe_ratingscache';

				$res2 = $TYPO3_DB->exec_DELETEquery(
					$cachingTable,
					'extensionkey =' . $TYPO3_DB->fullQuoteStr($this->extensionKey, 'tx_terfe_ratingscache').
					' AND version = ' . $TYPO3_DB->fullQuoteStr($this->version, 'tx_terfe_ratingscache')
				);
				
				$res3 = $TYPO3_DB->exec_INSERTquery(
					$cachingTable,
					$cachedRatingArr
					);
				
				return $res3? TRUE: FALSE;
			
			
			} else {
				return $res? TRUE : FALSE;
			}
			
		}
		 
		/**
		* [Describe function...]
		*
		* @param [type]  $extensionkey: ...
		* @param [type]  $version: ...
		* @param [type]  $username: ...
		* @return [type]  ...
		*/
		private function db_getRatings($extensionkey, $version = '', $username = '') {
			 
			global $TYPO3_DB, $TSFE;
			$res = $TYPO3_DB->exec_SELECTquery (
			'*',
				'tx_terfe_ratings',
				'extensionkey =' . $TYPO3_DB->fullQuoteStr($extensionkey, 'tx_terfe_ratings')
			.($version ? ' AND version = ' . $TYPO3_DB->fullQuoteStr($version, 'tx_terfe_ratings') : '')
			.($username ? ' AND username = ' . $TYPO3_DB->fullQuoteStr($username, 'tx_terfe_ratings') : ''),
				'',
				'version DESC,tstamp DESC' );
			 
			if ($res) {
				$ratings = array();
				while ($ratingRow = $TYPO3_DB->sql_fetch_assoc($res)) {
					$ratings[] = $ratingRow;
				}
				return $ratings;
			}
		}
		 
		/**
		* [Describe function...]
		*
		* @param [type]  $extensionkey: ...
		* @param [type]  $version: ...
		* @param [type]  $previous: ...
		* @return [type]  ...
		*/
		private function db_getAvgRating($extensionkey, $version, $previous = FALSE) {
			 
			global $TYPO3_DB, $TSFE;
			$res = $TYPO3_DB->exec_SELECTquery (
			'AVG(overall) as avg, COUNT(overall) as num',
				'tx_terfe_ratings',
				'extensionkey =' . $TYPO3_DB->fullQuoteStr($extensionkey, 'tx_terfe_ratings')
			.($previous ? ' AND version <= ': ' AND version =') . $TYPO3_DB->fullQuoteStr($version, 'tx_terfe_ratings')
			);
			if ($res) {
				$ratings = $TYPO3_DB->sql_fetch_assoc($res);
				return $ratings;
			}
		}
		 
	}
	 
	 
?>
