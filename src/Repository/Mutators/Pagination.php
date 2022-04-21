<?php

namespace App\Repository\Mutators;

use Doctrine\ORM\QueryBuilder;

class Pagination
{
    public const PER_PAGE = 10;

    /**
     * @var int
     */
    private $perPage = self::PER_PAGE;

    /**
     * @param int $perPage
     *
     * @codeCoverageIgnore
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    /**
     * @param QueryBuilder $query
     * @param int|null     $page
     *
     * @return QueryBuilder
     */
    public function paginate(QueryBuilder $query, ?int $page): QueryBuilder
    {
        $query->setMaxResults($this->perPage);

        if ($page !== null) {
            $query->setFirstResult(intval($page * $this->perPage));
        }

        return $query;
    }
}
