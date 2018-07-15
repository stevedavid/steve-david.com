<?php
namespace AdminBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du service',
                'attr' => [
                    'class' => 'form-control m-input',
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug du service',
                'attr' => [
                    'class' => 'form-control m-input',
                ],
            ])
            ->add('subtitle', TextType::class, [
                'label' => 'Sous-titre du service',
                'attr' => [
                    'class' => 'form-control m-input',
                ],
            ])
            ->add('price', TextType::class, [
                'label' => 'Prix du service',
                'attr' => [
                    'class' => 'form-control m-input',
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => 'AppBundle:Category',
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'attr' => [
                    'class' => 'form-control m-input',
                ],
            ])
            ->add('image', TextType::class, [
                'data_class' => null,
                'required' => false,
                'attr' => [
                    'class' => '',
                ],
            ])
            ->add('remoteMode', CheckboxType::class, [
                'label' => 'Consultation à distance possible',
                'required' => false,
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => 'Description introductive',
                'attr' => [
                    'class' => 'form-control  m-input',
                    'data-provide' => 'markdown',
                    'row' => 20,
                ],
            ])
            ->add('longDescription', TextareaType::class, [
                'label' => 'Description globale',
                'attr' => [
                    'class' => 'form-control  m-input',
                    'data-provide' => 'markdown',
                    'row' => 20,
                ],
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'Mis en avant',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg',
                    'style' => 'display: inline'
                ]
            ])
            ->add('cancel', ButtonType::class, [
                'label' => 'Annuler',
                'attr' => [
                    'class' => 'btn btn-secondary btn-lg',
                    'style' => 'display: inline'
                ]
            ])
        ;
    }
}