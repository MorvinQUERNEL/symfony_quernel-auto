<?php

namespace App\Form;

use App\Entity\Messages;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr' => [
                    'placeholder' => 'Entrez le sujet du message',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Le sujet est obligatoire',
                    ]),
                    new \Symfony\Component\Validator\Constraints\Length([
                        'max' => 50,
                        'maxMessage' => 'Le sujet ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'placeholder' => 'Entrez le contenu de votre message',
                    'class' => 'form-control',
                    'rows' => 5,
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Le contenu est obligatoire',
                    ]),
                    new \Symfony\Component\Validator\Constraints\Length([
                        'max' => 255,
                        'maxMessage' => 'Le contenu ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('sendAt', DateTimeType::class, [
                'label' => 'Date d\'envoi',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
                'data' => new \DateTimeImmutable(),
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotNull([
                        'message' => 'La date d\'envoi est obligatoire',
                    ]),
                ],
            ])
            ->add('recipient', EntityType::class, [
                'class' => Users::class,
                'choice_label' => function (Users $user) {
                    return $user->getFirstname() . ' ' . $user->getLastname() . ' (' . $user->getEmail() . ')';
                },
                'label' => 'Destinataire',
                'placeholder' => 'Choisissez un destinataire',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotNull([
                        'message' => 'Le destinataire est obligatoire',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Messages::class,
        ]);
    }
} 