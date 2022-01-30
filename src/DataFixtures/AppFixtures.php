<?php

namespace App\DataFixtures;

use App\Entity\Question;
use App\Factory\AdFactory;
use App\Factory\AnswerFactory;
use App\Factory\QuestionFactory;
use App\Factory\TagFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createMany(20);

        TagFactory::createMany(10);

        AdFactory::createMany(20, function () {
            return ['tags'=>TagFactory::random()];
        });

        QuestionFactory::createMany(56, function () {
            return ['usr' => UserFactory::random(), 'ad'=> AdFactory::random()];
        });

        AnswerFactory::createMany(200, function () {
            return ['author'=> UserFactory::random(), 'question' => QuestionFactory::random()];
        });
    }
}
