<?php

namespace App\Controller;

use App\Repository\AdRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search")
     */
    public function index(): Response
    {
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
        ]);
    }

    /**
     * @Route("/search-ad", name="search-ad")
     */
    public function search_ad(AdRepository $adRepository, Request $request): Response
    {
        $search = $request->query->get('s');

        if ($search !== '') {
            $query = $adRepository->createQueryBuilder('a')
                ->where('a.title LIKE :key')->orWhere('a.description LIKE :key')
                ->orderBy('a.votes', 'DESC')
                ->setParameter('key' , '%'.$search.'%')->getQuery();
            $ads = $query->getResult();
        } else {
            $ads = $adRepository->findBy([],['votes'=>'DESC']);
        }

        return $this->render('search/index.html.twig', [
            'ads' => $ads,
            'query' => $search
        ]);
    }
}
