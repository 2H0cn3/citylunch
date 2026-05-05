<?php

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('address', TextType::class, ['label' => 'Adresse', 'required' => false])
            ->add('phone', TextType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 8, 'minMessage' => '8 caractères minimum.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Customer::class]);
    }
}