<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre adresse e-mail',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@email.com',
                    'autocomplete' => 'email'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez saisir votre adresse e-mail.'
                    ]),
                    new Assert\Email([
                        'message' => 'Veuillez saisir une adresse e-mail valide.'
                    ])
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Tapez votre message ici...',
                    'rows' => 6,
                    'minlength' => 10
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez saisir votre message.'
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'Votre message doit contenir au moins {{ limit }} caractères.',
                        'max' => 2000,
                        'maxMessage' => 'Votre message ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer le message',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'contact_form'
        ]);
    }
} 