<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
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
            ->add('rue', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 255
                ]
            ])
            ->add('latitude', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('longitude', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('ville', EntityType::class, [
                'required' => true,
                'class' => Ville::class,
                'choice_label' => function ($ville) {
                    return $ville->getNom();
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
