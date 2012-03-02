<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
	 *
	 *  All rights reserved
	 *
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as
	 *  published by the Free Software Foundation; either version 2 of
	 *  the License, or (at your option) any later version.
	 *
	 *  The GNU General Public License can be found at
	 *  http://www.gnu.org/copyleft/gpl.html.
	 *
	 *  This script is distributed in the hope that it will be useful,
	 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *  GNU General Public License for more details.
	 *
	 *  This copyright notice MUST APPEAR in all copies of the script!
	 ******************************************************************/

	/**
	 * Repository for Tx_TerFe2_Domain_Model_Search
	 */
	class Tx_TerFe2_Domain_Repository_SearchRepository extends Tx_TerFe2_Domain_Repository_AbstractRepository {

		/**
		 * Search extension uids by search words and filters
		 *
		 * TODO:
		 * - Implement filters
		 *
		 * @param string $needle Search string
		 * @param array $filters Filter extension list
		 * @return array UIDs of the extensions
		 */
		public function findUidsBySearchWordsAndFilters($searchWords = NULL, array $filters = NULL) {
			$statement = '
				SELECT DISTINCT extension_uid, MATCH (extension_key,title,description,author_list,upload_comment,version_string,state,em_category,software_relation_list,category_list,tag_list) AGAINST (?) AS score
				FROM tx_terfe2_domain_model_search
				WHERE MATCH (extension_key,title,description,author_list,upload_comment,version_string,state,em_category,software_relation_list,category_list,tag_list) AGAINST (?)
				GROUP BY extension_uid
				ORDER BY score DESC
			';
			return $this->getUidsByStatement($statement, array($searchWords, $searchWords));
		}


		/**
		 * Search extension uids by author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author The Author to search for
		 * @return array UIDs of the extensions
		 */
		public function findUidsByAuthor(Tx_TerFe2_Domain_Model_Author $author) {
			$statement = '
				SELECT DISTINCT extension_uid FROM tx_terfe2_domain_model_search
				WHERE tx_terfe2_domain_model_search.author_list LIKE "%,?,%"
				OR tx_terfe2_domain_model_search.author_list LIKE "%,?%"
				OR tx_terfe2_domain_model_search.author_list LIKE "%?,%"
				OR tx_terfe2_domain_model_search.author_list LIKE "?"
				GROUP BY extension_uid
			';
			$author = (int) $author->getName();
			return $this->getUidsByStatement($statement, array($author, $author, $author, $author));
		}


		/**
		 * Execute statement and return resulting uids
		 *
		 * @param string $statement The statement to execute
		 * @param array $attributes Statement parameters
		 * @param string $uidField Name of the field with the uids
		 * @return array UIDs of the extensions
		 */
		public function getUidsByStatement($statement, array $attributes = array(), $uidField = 'extension_uid') {
			$query = $this->createQuery();
			$query->getQuerySettings()->setReturnRawQueryResult(TRUE);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->statement($statement, $attributes);
			$rows = $query->execute();
			unset($query);

			$uids = array(0);
			foreach ($rows as $row) {
				$uids[] = (int) $row[$uidField];
			}

			return $uids;
		}

	}
?>