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
        if ($user === $this->getUser()) {
            return $this->redirectToRoute('lbcdp_user_me');
        }

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

    /**
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UploadHelper $uploadHelper
     * @return Response
     * @Route ("/user/{email}/edit", name="lbcdp_user_edit")
     */
    public function editProfile(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UploadHelper $uploadHelper
    ): Response
    {
        $this->denyAccessUnlessGranted('USER_VIEW', $this->getUser());

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var $user User
             */

            $user = $form->getData();
            $newFile = $form['profilePicture']->getData();

            if ($newFile) {
                $fileName = $uploadHelper->uploadImg($newFile, 'user');
                $user->setProfilePicture($fileName);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $successMessages = [
                'Waouh. Bravo. Génial.',
                'Ah super, je croyais que tu t\'appelais Romuald'
            ];

            $this->addFlash('success', $successMessages[array_rand($successMessages)]);

            return $this->redirectToRoute('lbcdp_user_me');
        }

        return $this->render('user/edit.html.twig', ['userForm' => $form->createView()]);
    }
}
