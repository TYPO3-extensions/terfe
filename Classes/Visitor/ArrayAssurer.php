<?php
/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

include_once(t3lib_extMgm::extPath('ter_fe2') . '/Resources/Private/Php/PHP-Parser/lib/bootstrap.php');
use PhpParser\Node;
use PhpParser\Node\Expr;

/**
 * Class ArrayAssurer
 *
 * Used to parse the ext_emconf.php and remove any illegal statements
 */
class Tx_TerFe2_Visitor_ArrayAssurer extends PhpParser\NodeVisitorAbstract
{

    /**
     * @var bool
     */
    protected $assignmentFound = FALSE;

    /**
     * Checks all nodes and returns FALSE to remove not wanted notes
     *
     * @param Node $node
     * @return bool|void
     */
    public function leaveNode(Node $node)
    {

        if ($node instanceof Expr\Assign) {
            if (!$this->assignmentFound) {
                $this->assignmentFound = TRUE;
                // Check the name of the assigned variable to
                if ($node->__get('var')->__get('var')->__get('name') === 'EM_CONF') {
                    return;
                }
            }
            // We must not have another assignment
            throw new UnexpectedValueException();
        } elseif (!($node instanceof Node\Name
            || $node instanceof Node\Scalar
            || $node instanceof Expr\Array_
            || $node instanceof Expr\ArrayDimFetch
            || $node instanceof Expr\ArrayItem
            || $node instanceof Expr\ConstFetch
            || $node instanceof Expr\Variable
        )
        ) {
            return FALSE;
        }
    }
}

?>