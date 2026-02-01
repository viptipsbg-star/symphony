<?php

namespace Elearning\CompaniesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType {
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('subject', 'text', array(
                'label'=>'employees.send_email.subject'
            ))
            ->add('text', 'textarea', array(
                'label'=>'employees.send_email.text'
            ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array());
    }

    public function getName() {
        return "message";
    }
}
