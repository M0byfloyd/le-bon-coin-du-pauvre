<?php

namespace App\Controller;

use App\Repository\AdRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="lbcdp_homepage")
     */
    public function index(AdRepository $adRepository): Response
    {

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'ads'=>$adRepository->findBy([],['votes'=>'DESC'])
        ]);
    }

    /**
     * @Route("/debug", name="lbcdp_debug")
     */
    public function debug(UserRepository $userRepository): Response
    {
        return $this->render('default/debug.html.twig',
            ['users'=>$userRepository->findAll()]
        );
    }
}
