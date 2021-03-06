<?php

/**
 * This file is part of the propel-default-order-behavior package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace GHarlan\Propel\Behavior\DefaultOrder;

use Propel\Generator\Builder\Om\QueryBuilder;
use Propel\Generator\Exception\InvalidArgumentException;
use Propel\Generator\Model\Behavior;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Sets the default order for tables
 *
 * @author Gregor Harlan
 */
class DefaultOrderBehavior extends Behavior
{
    public function preSelectQuery(QueryBuilder $builder)
    {
        $columns = [];

        foreach ($this->getParameters() as $parameter) {
            $parameter = explode(' ', $parameter);

            $column = $this->getTable()->getColumn($parameter[0]);
            $columnConstant = $builder->getColumnConstant($column);

            $direction = isset($parameter[1]) ? strtoupper($parameter[1]) : Criteria::ASC;
            switch ($direction) {
                case Criteria::ASC:
                    $columns[$columnConstant] = 'Ascending';
                    break;
                case Criteria::DESC:
                    $columns[$columnConstant] = 'Descending';
                    break;
                default:
                    throw new InvalidArgumentException('DefaultOrderBehavior only accepts "asc" or "desc" as direction parameter');
            }
        }

        if (empty($columns)) {
            throw new InvalidArgumentException('DefaultOrderBehavior needs at least one column parameter');
        }

        $script = 'if (!$this->getOrderByColumns()) {
    $this';

        $prefix = '';
        if (count($columns) > 1) {
            $prefix = "\n        ";
        }
        foreach ($columns as $column => $direction) {
            $script .= $prefix . "->add{$direction}OrderByColumn($column)";
        }


        $script .= ';
}';

        return $script;
    }
}
