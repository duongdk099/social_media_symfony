<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', ChoiceType::class, [
                'choices' => [
                    'up' => 1,
                    'down' => -1,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Vote',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('post', EntityType::class, [
                'class' => Post::class,
                'choice_label' => 'id',
            ])
            ->add('comment', EntityType::class, [
                'class' => Comment::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vote::class,
        ]);
    }
}
