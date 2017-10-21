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

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre nom et prénom *',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez renseigner vos nom et prénom.',
                ]),
                'attr' => [
                    'class' => 'sm-form-control',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email *',
                'constraints' => [new NotBlank([
                    'message' => 'Veuillez renseigner votre adresse email.',
                ]), new Email([
                    'message' => 'L\'adresse email rentrée n\'est pas valide.',
                ])],
                'attr' => [
                    'class' => 'sm-form-control',
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Votre numéro de téléphone',
                'required' => false,
                'attr' => [
                    'class' => 'sm-form-control',
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet de votre email *',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez renseigner le sujet de votre email',
                ]),
                'attr' => [
                    'class' => 'sm-form-control',
                ],
            ])
            ->add('service', EntityType::class, [
                'placeholder' => '--- Faites votre choix ---',
                'required' => false,
                'class' => 'AppBundle:Service',
                'choice_label' => 'name',
                'label' => 'Service concerné',
                'attr' => [
                    'class' => 'sm-form-control',
                ],
                'group_by' => 'categoryName',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu de votre message *',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez renseigner le contenu de votre message.',
                ]),
                'attr' => [
                    'class' => 'sm-form-control',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'row' => 6,
                    'class' => 'button button-3d nomargin',
                ],
            ])
        ;
    }
}