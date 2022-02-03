<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\Authenticator;
use App\Service\UploadHelper;
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
                        Authenticator               $loginFormAuthenticator,
                        UploadHelper                $uploadHelper
    ): Response
    {
        $form = $this->createForm(UserType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var $user User
             */

            $user = $form->getData();

            $user->setPassword($hasher->hashPassword($user,$form['password']->getData()));

            $newFile = $form['profilePicture']->getData();

            if ($newFile) {
                $fileName = $uploadHelper->uploadImg($newFile, 'user');
                $user->setProfilePicture($fileName);
            }
            $user->setVotes(0);

            $entityManager->persist($user);
            $entityManager->flush();

            return $authenticator->authenticateUser($user, $loginFormAuthenticator, $request);
        }
        return $this->render('user/new.html.twig', ['userForm' => $form->createView()]);
    }

    /**
     * @return Response
     * @Route("/me",name="lbcdp_user_me")
     */
    public function me(): Response
    {
        $user = $this->getUser();

        if ($user) {
            return $this->render('user/show.html.twig', ['user' => $user]);
        }

        return $this->redirectToRoute('lbcdp_homepage');
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
