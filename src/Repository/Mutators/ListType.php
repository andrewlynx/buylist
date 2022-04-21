<?php

namespace App\Repository\Mutators;

use App\Constant\TaskListTypes;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

class ListType
{
    /**
     * @var string
     */
    private $alias = 't';

    /**
     * @var int|null
     */
    private $listType;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        if ($requestStack->getCurrentRequest() !== null) {
            $query = $requestStack->getCurrentRequest()->get(TaskListTypes::QUERY);
            $this->listType = TaskListTypes::typeExists($query) ? (int)$query : null;
        }
    }

    /**
     * @param string $alias
     *
     * @codeCoverageIgnore
     *
     * @return ListType
     */
    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @param QueryBuilder $query
     *
     * @return QueryBuilder
     */
    public function filter(QueryBuilder $query): QueryBuilder
    {
        if ($this->listType !== null) {
            $query->andWhere($this->alias.'.type = :type')
                ->setParameter('type', $this->listType)
                ;
        }

        return $query;
    }
}
