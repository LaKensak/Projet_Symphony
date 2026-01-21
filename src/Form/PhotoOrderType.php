<?php

namespace App\Form;

use App\Entity\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhotoOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayOrder', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'N°',
                    'min' => 1,
                    'style' => 'width: 70px;'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Photo::class,
            'csrf_protection' => false,
        ]);
    }
}
