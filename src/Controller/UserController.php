<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Authenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="user_index")
     */
    public function index(UserRepository $userRepository): Response
    {

        dd($userRepository->findAll());
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/signin", name="lbcdp_signin")
     */
    public function new(Request                     $request,
                        EntityManagerInterface      $entityManager,
                        UserPasswordHasherInterface $hasher,
                        UserAuthenticatorInterface  $authenticator,
                        Authenticator               $loginFormAuthenticator): Response
    {
        if ($request->isMethod('POST')) {
            $requestObj = $request->request;
            if (!empty($requestObj->get('password'))
                && !empty($requestObj->get('passwordVerify'))
                && $request->request->get('password') === $requestObj->get('passwordVerify')
                && $this->isCsrfTokenValid('register_form', $requestObj->get('csrf'))) {
                $user = new User();
                $user->setFirstName($requestObj->get('firstName'))
                    ->setLastName($requestObj->get('lastName'))
                    ->setEmail($requestObj->get('email'))
                    ->setPassword($hasher->hashPassword($user, $requestObj->get('password')))
                    ->setVotes(0);

                $entityManager->persist($user);
                $entityManager->flush();

                return $authenticator->authenticateUser($user, $loginFormAuthenticator, $request);
            }
        }
        return $this->render('user/new.html.twig');
    }

    /**
     * @param User $user
     * @return Response
     * @Route("/show/{email}",name="lbcdp_show")
     */
    public function show(User $user): Response
    {
        $this->denyAccessUnlessGranted('USER_VIEW', $user);
        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    /**
     * @param User $user
     * @return Response
     * @Route ("/user/{email}", name="lbcdp_showprofile")
     */
    public function showProfile(User $user): Response
    {
        return $this->render('user/profile.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/user/{id}/vote", name="lbcdp_ads_vote", methods="POST")
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function vote(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $parameters = json_decode($request->getContent(), true);
        $voteDirection = $parameters['voteDirection'];

        $voteDirection === 'up' ? $user->upVote() : $user->downVote();

        $entityManager->flush();

        return $this->json(['votesTotal' => $user->getVotes()]);
    }
}
