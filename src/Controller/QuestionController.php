<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/question", name="question")
 */
class QuestionController extends AbstractController
{
    /**
     * @Route("/add", name="lbcdp_question_add", methods="POST")
     */
    public function add(Request $request, EntityManagerInterface $entityManager, AdRepository $adRepository): Response
    {
        $user = $this->getUser();

        $requestObj = $request->request;
        $adSlug = $requestObj->get('adSlug');
        $text = $requestObj->get('questionText');

        $question = new Question();
        $question->setAd($adRepository->findOneBy(['slug' => $adSlug]))
            ->setCreatedAt(new \DateTime('now'))
            ->setText($text)
            ->setUsr($user);

        $entityManager->persist($question);
        $entityManager->flush();

        return $this->redirectToRoute('lbcdp_ad_show', ['slug' => $adSlug]);
    }
}
