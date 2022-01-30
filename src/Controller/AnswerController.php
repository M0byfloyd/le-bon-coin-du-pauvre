<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/answer", name="answer")
 */
class AnswerController extends AbstractController
{
    /**
     * @Route("/add", name="lbcdp_answer_add", methods="POST")
     */
    public function add(Request $request, EntityManagerInterface $entityManager, QuestionRepository $questionRepository): Response
    {
        $user = $this->getUser();

        $requestObj = $request->request;
        $questionId = $requestObj->get('questionId');
        $adSlug = $requestObj->get('adSlug');
        $text = $requestObj->get('answerText');

        $answer = new Answer();
        $answer->setText($text)
            ->setCreatedAt(new \DateTime('now'))
            ->setAuthor($user)
            ->setQuestion($questionRepository->findOneBy(['id'=>$questionId]));
        $entityManager->persist($answer);
        $entityManager->flush();

        return $this->redirectToRoute('lbcdp_ad_show', ['slug' => $adSlug]);
    }
}
