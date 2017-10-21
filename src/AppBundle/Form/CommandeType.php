<?php
namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre prénom :',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez renseigner votre prénom.',
                ]),
                'attr' => [
                    'value' => 'Steve',
                    'class' => 'sm-form-control',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email :',
                'constraints' => [new NotBlank([
                    'message' => 'Veuillez renseigner votre adresse email.',
                ]), new Email([
                    'message' => 'L\'adresse email rentrée n\'est pas valide.',
                ])],
                'attr' => [
                    'value' => 'test@test.com',
                    'class' => 'sm-form-control',
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Votre numéro de téléphone :',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez renseigner votre numéro de téléphone.',
                ]),
                'attr' => [
                    'value' => '0465987548',
                    'class' => 'sm-form-control',
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom de famille :',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez renseigner votre nom de famille.',
                ]),
                'attr' => [
                    'value' => 'David',
                    'class' => 'sm-form-control',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Je confirme et je paye',
                'attr' => [
                    'class' => 'button button-3d',
                ],
            ])
        ;
    }
}