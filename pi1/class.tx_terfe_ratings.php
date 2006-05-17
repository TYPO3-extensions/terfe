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
		* Checks if rating cache is up to date, processes incoming rating data and saves rating
		*
		* @return boolean  False if saving did not work
		*/
		function process_rating () {
			global $TSFE;
			$ratingsArr = t3lib_div::_POST('rating');
			$real_rating = $this->comp_weightedRating($this->extensionKey, $this->version);
			// if ratings cache is outdated update it
			 
			 
			if ($real_rating != $this->extRow['rating']) {
				$this->db_cache_rating($this->extensionKey, $this->version);
				$TSFE->clearPageCacheContent();
				 
			}
			 
			if ($ratingsArr && $this->canBeRated()) {
				$res = $this->db_saveRating($ratingsArr, t3lib_div::_POST('notes'), $this->username);
				if ($res) {
					$TSFE->clearPageCacheContent();
				} else {
					return FALSE;
				}
				 
			}
		}
		 
		/**
		* Renders the single view for ratings, with 
		*
		* @return string The rendered output
		*/
		function renderSingleView_rating() {
			global $TSFE;
			 
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
		* Displays a table with rating records
		*
		* @param array  $ratings: Array with rating records
		* @return string Rating list
		*/
		function render_ratingList($ratings) {
			if (is_array($ratings[0])) {
				$output .= '<table class="ext-compactlist review-hist">
					<thead><tr>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_username', '').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_func_short', '').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_doc_short', '').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_code_short', '').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_overallrating', '').'</th>
					<th>'.$this->backRef->pi_getLL('extensioninfo_ratings_notes', '').'</th>
					</tr></thead>';
				foreach ($ratings as $rating) {
					if ($rating['version'] > $this->version) {
						continue;
					}
					else if ($rating['version'] == $this->version) {
						$output .= '<tr class="current">';
					} else {
						$output .= '<tr class="old">';
					}
					$output .= '
						<td>'.$rating['username'].'</td>
						<td>'.$rating['funcrating'].'</td>
						<td>'.$rating['docrating'].'</td>
						<td>'.$rating['coderating'].'</td>
						<td>'.$this->render_starWrap($rating['overall']).'</td>
						<td>'.nl2br(htmlspecialchars(substr($rating['notes'],0,250))). '</td></tr>';
				}
				$output .= '</table>';
				return $output;
			} else {
				return FALSE;
			}
		}
		/**
		* Renders a rating form
		*
		* @return string  Rating form HTML
		*/
		private function render_ratingForm() {
			$valuerows = array();
			for ($i = $this->minRating; $i <= $this->maxRating; $i++) {
				$valuerows[] = '<option value="'.$i.'">'.$this->ratingItems[$i].'</option>';
			}
			 
			$output = '<form action="'.t3lib_div::linkThisScript().'" method="POST" class="rating">';
			$output .= '<fieldset><legend>Rate this extension</legend>';
			$output .= '<div>';
			 
			$output .= '<label for="funcrating">'.$this->backRef->pi_getLL('extensioninfo_ratings_funcrating', '').'</label><select name="rating[funcrating]" id="funcrating"><option value=""></option>';
			$output .= implode('', array_reverse($valuerows));
			$output .= '</select>';
			 
			$output .= '<label for="docrating">'.$this->backRef->pi_getLL('extensioninfo_ratings_docrating', '').'</label><select name="rating[docrating]" id="docrating"><option value=""></option>';
			$output .= implode('', array_reverse($valuerows));
			$output .= '</select>';
			 
			$output .= '<label for="coderating">'.$this->backRef->pi_getLL('extensioninfo_ratings_coderating', '').'</label><select name="rating[coderating]" id="coderating"><option value="" ></option>';
			$output .= implode('', array_reverse($valuerows));
			$output .= '</select>';
			 
			 
			$output .= '</div><div>';
			 
			$output .= '<label for="notes">'.$this->backRef->pi_getLL('extensioninfo_ratings_addnotes', '').'</label><br/><textarea name="notes" id="notes" cols="50" rows="4"></textarea>';
			$output .= '<input type="hidden" name="no_cache" value="1"/>';
			$output .= '<input type="submit" name="submit" value="Submit rating"/></div>';
			$output .= '</fieldset></form>';
			return $output;
			 
			 
		}
		/**
		* Renders a rating wrapped in css-stylable span
		*
		* @param int  $int: The rating value
		* @return string HTML output
		*/
		public static function render_starWrap($num) {
			if (!$num) {
				return '';
			}
			$roundedvalue = round(($num * 2), 0)/2;
			return '<span class="ext_rating stars'. $roundedvalue * 10 .'">' . $roundedvalue . ' out of 5</span>';
		}
		 
		/**
		* Computes the weighted rating for an extension version
		*
		* @param string  $extensionKey: extension key
		* @param string	 $version: extension version
		* @return array  Array with [0] rating and [1] number of votes
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
		* Computes rating average from 3 dimensions
		*
		* @param array  $ratings: rating record
		* @return float	Rating average
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
		* Saves submitted rating data to db
		*
		* @param array  $rating: Rating form data
		* @param string  $notes: Rating notes
		* @param string  $username: Username
		* @return boolean	True if saving worked, else False
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
				'notes' => $notes,
				'username' => $TYPO3_DB->quoteStr($username, $table),
				'tstamp' => time()
			);
			 
			$res = $TYPO3_DB->exec_INSERTquery($table, $insertArr);
			 
			if (!$res) {
				return FALSE;
			}
			$this->db_cache_rating($this->extensionKey, $this->version);
			return TRUE;
			 
		}
		 
		/**
		* Saves average rating to the db for caching
		*
		* @param string  $extensionKey: extension key
		* @param string	 $version: extension version (optional)
		* @return boolean False if saving failed
		*/
		private function db_cache_rating($extensionKey, $version) {
			$cachable_ratings = $this->comp_weightedRating($extensionKey, $version);
			 
			if ($cachable_ratings) {
				 
				global $TYPO3_DB;
				$table = 'tx_terfe_ratings';
				 
				$cachedRatingArr = array (
				'extensionkey' => $TYPO3_DB->quoteStr($extensionKey, $table),
					'version' => $TYPO3_DB->quoteStr($version, $table),
					'rating' => floatval($cachable_ratings[0]),
					'votes' => intval($cachable_ratings[1])
				);
				$cachingTable = 'tx_terfe_ratingscache';
				 
				$res2 = $TYPO3_DB->exec_DELETEquery(
				$cachingTable,
					'extensionkey =' . $TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_ratingscache'). ' AND version = ' . $TYPO3_DB->fullQuoteStr($version, 'tx_terfe_ratingscache')
				);
				$res3 = $TYPO3_DB->exec_INSERTquery(
				$cachingTable,
					$cachedRatingArr );
				 
				return $res3? TRUE:
				 FALSE;
				 
				 
			} else {
				return FALSE;
			}
		}
		 
		/**
		* Retrieves rating records from the db,
		*
		* @param string  $extensionKey: extension key
		* @param string	 $version: extension version (optional)
		* @param string  $username: Username (optional)
		* @return array  Rating record
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
		* Gets Average rating and number of votes from the db
		*
		* @param string  $extensionKey: extension key
		* @param string	 $version: extension version (optional)
		* @param boolean  $previous: Include previous version ratings
		* @return array  Array containing avg and number of votes
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
