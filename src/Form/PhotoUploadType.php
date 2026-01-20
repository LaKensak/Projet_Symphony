<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class PhotoUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photo', FileType::class, [
                'label' => 'Photo (JPEG ou PNG, max 5Mo)',
                'attr' => ['class' => 'form-control', 'accept' => 'image/jpeg,image/png'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une photo.']),
                    new File([
                        'maxSize' => '5M',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). Maximum autorisé : {{ limit }} {{ suffix }}.',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Seuls les formats JPEG et PNG sont acceptés.',
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
