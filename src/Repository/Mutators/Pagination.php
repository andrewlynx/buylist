<?php

namespace App\Repository\Mutators;

use Doctrine\ORM\Query;

class Pagination
{
    public const PER_PAGE = 10;

    /**
     * @var int
     */
    private $perPage = self::PER_PAGE;

    /**
     * @param int $perPage
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    /**
     * @param Query    $query
     * @param int|null $page
     *
     * @return Query
     */
    public function paginate(Query $query, ?int $page): Query
    {
        $query->setMaxResults($this->perPage);

        if ($page !== null) {
            $query->setFirstResult(intval($page * $this->perPage));
        }

        return $query;
    }
}