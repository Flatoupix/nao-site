<?php

namespace AppBundle\Repository;

use InvalidArgumentException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function countNbUsers()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('COUNT(u.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Récupère une liste d'articles triés et paginés.
     *
     * @param int $page Le numéro de la page
     * @param int $nbMaxParPage Nombre maximum d'article par page
     *
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     *
     * @return Paginator
     */
    public function findAllPagineEtTrie($page, $nbMaxParPage, $filtre = null, $ordreDeTri = 'DESC')
    {
        if (!is_numeric($page)) {
            throw new InvalidArgumentException(
                'La valeur de l\'argument $page est incorrecte (valeur : ' . $page . ').'
            );
        }

        if ($page < 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas');
        }

        if (!is_numeric($nbMaxParPage)) {
            throw new InvalidArgumentException(
                'La valeur de l\'argument $nbMaxParPage est incorrecte (valeur : ' . $nbMaxParPage . ').'
            );
        }

        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->where('CURRENT_DATE() >= u.registrationDate')
            ->andWhere('u.deleted IS NULL');

        if (isset($filtre)) {
            $mapping = [
                'user' => 'u.username',
                'status' => 'u.roles',
            ];

            $qb->orderBy($mapping[$filtre], $ordreDeTri);
        } else {
            $qb->orderBy('u.id', 'DESC');
        }

        $query = $qb->getQuery();


        $premierResultat = ($page - 1) * $nbMaxParPage;
        $query->setFirstResult($premierResultat)->setMaxResults($nbMaxParPage);
        $paginator = new Paginator($query);

        if (($paginator->count() <= $premierResultat) && $page != 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas.'); // page 404, sauf pour la première page
        }

        return $paginator;
    }

}
