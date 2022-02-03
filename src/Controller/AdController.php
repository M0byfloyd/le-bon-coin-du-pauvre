<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\NewAddType;
use App\Repository\AdRepository;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="lbcdp_ads")
     */
    public function index(AdRepository $adRepository): Response
    {
        return $this->render('ad/list.html.twig', ['ads' => $adRepository->findBy([],['votes'=>'DESC'])]);
    }

    /**
     * @Route("/ad/{slug}/vote", name="lbcdp_ads_vote", methods="POST")
     * @param Ad $ad
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function vote(Ad $ad, Request $request, EntityManagerInterface $entityManager): Response
    {
        $vote = $request->request->get('vote');
        $vote === 'up' ? $ad->upVote() : $ad->downVote();
        try {
            $entityManager->flush();

            return $this->redirectToRoute('lbcdp_ad_show', [
                'slug' => $ad->getSlug()
            ]);

        } catch (OptimisticLockException $e) {
            dd($e);
        }
    }

    /**
     * @Route("/ad/new", name="lbcdp_ad_new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, UploadHelper $uploadHelper): Response
    {
        $this->denyAccessUnlessGranted('USER_VIEW', $this->getUser());

        $form = $this->createForm(NewAddType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var $ad Ad
             */

            $ad = $form->getData();
            $ad->setUser($this->getUser())
            ->setCreationDate(new \DateTime('now'));

            $newFile = $form['images']->getData();

            if ($newFile) {
                $fileName = $uploadHelper->uploadImg($newFile, 'ad');
                $ad->setImages($fileName);
            }

            $entityManager->persist($ad);
            $entityManager->flush();

            $successMessages = [
                'Faut être sacrément pauvre pour faire une annonce comme ça',
                'Félicitation LBCDP vous confirme que vous êtes pauvre',
                'Une annonce pauvre en contenu, comme on les aime !',
                'Vous vous êtes surpassé dans la pauvreté',
                'Vous êtes sûr que cette annonce sera vraiment utile ?'
            ];

            $this->addFlash('success', $successMessages[array_rand($successMessages)]);

            return $this->redirectToRoute('lbcdp_ad_show', ['slug'=> $ad->getSlug()]);
        }

        return $this->render('ad/new.html.twig', ['newAddForm' => $form->createView()]);
    }

    /**
     * @Route("/ad/{slug}", name="lbcdp_ad_show")
     */
    public function show(Ad $ad): Response
    {
        return $this->render('ad/index.html.twig',
            ['ad' => $ad, 'isAuthor'=> $ad->getUser() === $this->getUser()]
        );
    }

    /**
     * @Route("/ad/remove/{slug}", name="lbcdp_ad_remove")
     */
    public function remove(Ad $ad, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('USER_EDIT',$ad->getUser());

        if ($request->isMethod('POST')) {
            if ($request->request->get('choice') === 'n') {
                return $this->redirectToRoute('lbcdp_ad_show', ['slug'=> $ad->getSlug()]);
            }
            $entityManager->remove($ad);
            $entityManager->flush();

            $this->addFlash('success', 'L\'annonce a été giga supprimée');

            return $this->redirectToRoute('lbcdp_homepage');
        }
        return $this->render('ad/remove.html.twig',['ad'=>$ad]);
    }

    /**
     * @Route("/ad/modify/{slug}", name="lbcdp_ad_edit")
     */
    public function edit(Ad $ad,Request $request, EntityManagerInterface $entityManager, UploadHelper $uploadHelper): Response
    {
        $this->denyAccessUnlessGranted('USER_VIEW', $this->getUser());

        $form = $this->createForm(NewAddType::class, $ad);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var $ad Ad
             */

            $ad = $form->getData();

            $newFile = $form['images']->getData();

            if ($newFile) {
                $fileName = $uploadHelper->uploadImg($newFile, 'ad');
                $ad->setImages($fileName);
            }

            $entityManager->persist($ad);
            $entityManager->flush();

            $successMessages = [
                'De pauvres modifications vous avez fait',
                'L\'équipe LBCDP ne saurait approuver l\'utilité de ce changement'
            ];

            $this->addFlash('success', $successMessages[array_rand($successMessages)]);

            return $this->redirectToRoute('lbcdp_ad_show', ['slug'=> $ad->getSlug()]);
        }

        return $this->render('ad/edit.html.twig', ['newAddForm' => $form->createView()]);
    }
}
