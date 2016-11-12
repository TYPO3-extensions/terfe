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
 * Repository for Tx_TerFe2_Domain_Model_Author
 */
class Tx_TerFe2_Domain_Repository_AuthorRepository extends Tx_TerFe2_Domain_Repository_AbstractRepository
{

    /**
     * Returns the authors from latest extension versions
     *
     * @return Tx_Extbase_Persistence_ObjectStorage Author objects
     */
    public function findByLatestExtensionVersion()
    {
        $statement = '
			SELECT author FROM tx_terfe2_domain_model_version RIGHT JOIN tx_terfe2_domain_model_extension ON (
				tx_terfe2_domain_model_version.uid = tx_terfe2_domain_model_extension.last_version
			)
		';

        // Workaround while extbase doesn't support JOIN
        $query = $this->createQuery();
        $query->getQuerySettings()->setReturnRawQueryResult(TRUE);
        $query->statement($statement, array());
        $rows = $query->execute();
        unset($query);

        // Workaround to enable paginate
        $uids = array();
        foreach ($rows as $row) {
            $uids[] = (int)$row['author'];
        }
        $query = $this->createQuery();
        $query->setOrderings(
            array('name' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING)
        );
        $query->matching($query->in('uid', $uids));

        return $query->execute();
    }

    /**
     * Returns all matching records by combination of email and name
     *
     * @param string $email
     * @param string $name
     *
     * @return matching records
     */
    public function findByEmailAndName($email, $name)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                array(
                    $query->equals('email', $email),
                    $query->equals('name', $name)
                )
            )
        );

        return $query->execute();
    }

    /**
     * Returns author with matching email, name and username
     *
     * @param array $authorRow
     * @return array|Tx_Extbase_Persistence_QueryResultInterface
     */
    public function findByAuthorData($authorRow)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(FALSE);
        $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        $query->matching(
            $query->logicalAnd(
                $query->equals('email', $authorRow['email']),
                $query->equals('name', $authorRow['name']),
                $query->equals('username', $authorRow['username'])
            )
        );
        return $query->execute();
    }
}

?>