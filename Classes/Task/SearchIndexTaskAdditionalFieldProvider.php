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
 * Additional field provider for the search index task
 */
class Tx_TerFe2_Task_SearchIndexTaskAdditionalFieldProvider extends Tx_TerFe2_Task_AbstractAdditionalFieldProvider
{

    /**
     * Add some input fields to configure the task
     *
     * @return void
     */
    protected function addAdditionalFields()
    {

    }


    /**
     * Checks the given values
     *
     * @param array $submittedData Submitted user data
     * @return boolean TRUE if validation was ok
     */
    protected function checkAdditionalFields(array $submittedData)
    {
        return TRUE;
    }

}

?>