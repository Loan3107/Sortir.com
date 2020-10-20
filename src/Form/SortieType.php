<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 100
                ]
            ])
            ->add('dateDebut', DateTimeType::class, [
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('duree', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateCloture', DateTimeType::class, [
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('descriptionInfos', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 500
                ]
            ])
            ->add('lieu', EntityType::class, [
                'required' => true,
                'class' => Lieu::class,
                'choice_label' => function ($lieu) {
                    return $lieu->getNom();
                },
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('enregistrer',SubmitType::class,[
                'label' => 'Enregister',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('publier',SubmitType::class,[
                'label' => 'Publier',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i'
        ]);
    }
}
