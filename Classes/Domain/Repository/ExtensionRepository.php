<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Thomas Loeffler <loeffler@spooner-web.de>, Spooner Web
	 *           Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
	 * Repository for Tx_TerFe2_Domain_Model_Extension
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Domain_Repository_ExtensionRepository extends Tx_Extbase_Persistence_Repository {

		/**
		 * Returns new and updated extensions
		 *
		 * @param integer $latestCount Count of extensions
		 * @return array An array of extensions
		 */
		public function findNewAndUpdated($latestCount) {
			$query = $this->createQuery();
			$query->setLimit((int) $latestCount);
			$query->setOrderings(
				array('lastUpdate' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING)
			);
			return $query->execute();
		}


		/**
		 * Returns all extensions in a category
		 * 
		 * @param Tx_TerFe2_Domain_Model_Category $category The Category to search in
		 * @return array An array of extensions
		 */
		public function findByCategory(Tx_TerFe2_Domain_Model_Category $category) {
			$query = $this->createQuery();
			//$query->equals('categories.title', $category->getTitle());
			$query->matching($query->contains('categories', $category));
			return $query->execute();
		}


		/**
		 * Returns all extensions with a tag
		 * 
		 * @param Tx_TerFe2_Domain_Model_Tag $tag The Tag to search for
		 * @return array An array of extensions
		 */
		public function findByTag(Tx_TerFe2_Domain_Model_Tag $tag) {
			$query = $this->createQuery();
			//$query->equals('tags.title', $tag->getTitle());
			$query->matching($query->contains('tags', $tag));
			return $query->execute();
		}

	}
?>