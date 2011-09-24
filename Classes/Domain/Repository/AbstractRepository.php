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
	 * Abstract repository
	 */
	abstract class Tx_TerFe2_Domain_Repository_AbstractRepository extends Tx_Extbase_Persistence_Repository {

		/**
		 * Returns a query for objects of this repository
		 *
		 * @param string $offset Offset to start with
		 * @param string $count Count of result
		 * @param array $ordering Ordering <-> Direction
		 * @return Tx_Extbase_Persistence_QueryInterface
		 */
		public function createQuery($offset = 0, $count = 0, $ordering = array()) {
			$query = parent::createQuery();

			if (!empty($offset)) {
				$query->setOffset((int) $offset);
			}

			if (!empty($count)) {
				$query->setLimit((int) $count);
			}

			if (!empty($ordering)) {
				$query->setOrderings($ordering);
			}

			return $query;
		}


		/**
		 * Returns random objects from db
		 *
		 * @param integer $limit Limit of the results
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findRandom($limit) {
			$query = $this->createQuery(0, $limit);

				// Workaround for random ordering while Extbase doesn't support this
				// See: http://lists.typo3.org/pipermail/typo3-project-typo3v4mvc/2010-July/005870.html
			$backend = $this->objectManager->get('Tx_Extbase_Persistence_Storage_Typo3DbBackend');
			$parameters = array();
			$statementParts = $backend->parseQuery($query, $parameters);
			$statementParts['orderings'][] = ' RAND()';
			$statement = $backend->buildQuery($statementParts, $parameters);
			$query->statement($statement, $parameters);

			return $query->execute();
		}


		/**
		 * Returns all objects ordered by given sorting and direction
		 *
		 * @param string $sorting Sort result by this key
		 * @param string $direction Sorting order
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findAllBySortingAndDirection($sorting, $direction) {
			$query = $this->createQuery(0, 0, array($sorting => $direction));
			return $query->execute();
		}

	}
?>