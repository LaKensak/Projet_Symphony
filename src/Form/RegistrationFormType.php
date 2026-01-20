<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'attr' => ['class' => 'form-control', 'placeholder' => 'votre@email.com']
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre pseudo']
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Votre âge', 'min' => 13, 'max' => 120]
            ])
            ->add('captcha', TextType::class, [
                'label' => 'Captcha : Combien font 3 + 4 ?',
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez répondre au captcha.'])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
