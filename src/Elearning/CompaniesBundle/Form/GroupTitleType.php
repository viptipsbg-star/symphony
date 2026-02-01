<?php

namespace Elearning\CompaniesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GroupTitleType extends AbstractType {
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('title', 'text', array(
                'label'=>'new_group.title_label',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank()
                )
            ))
            ->add('group_id', 'hidden', array(
                'constraints' => array(
                    new Assert\NotNull(),
                    new Assert\Type('Numeric')
                )
            ));
    }

    public function getName() {
        return "group_title";
    }
}
