<?php

namespace App\Form;

use App\Entity\Ad;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class NewAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description', TextareaType::class)
            ->add('price')
            ->add('images',FileType::class, [
                'mapped'=>false,
                'required'=>false,
                'constraints'=> [
                    new Image([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Je sais que t\'es pauvre mais y a des compresseur gratuits !'
                    ])
                ]
            ])
            ->add('tags', EntityType::class, [
                'multiple' => true,
                'expanded'=>false,
                'class' => Tag::class,
                'choice_label' => 'name'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
