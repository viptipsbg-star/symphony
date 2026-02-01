<?php

namespace Elearning\CompaniesBundle\Form;

use Elearning\CompaniesBundle\Entity\Administrator;
use PhpOption\Tests\Repository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeProfileFieldsType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options) {
        $types = array(
            "employee"=>"new_employee.fields.type_choices.employee",
            "manager"=>"new_employee.fields.type_choices.manager",
        );

        $employed = array(
            "employed" => "new_employee.fields.employed_choices.employed",
            "unemployed" => "new_employee.fields.employed_choices.unemployed",
        );

        if (!empty($options['isSupervisor']) && !empty($options['canCreateAdmin'])) {
            $types["administrator"] = "new_employee.fields.type_choices.administrator";
        }

        $minLevel = $options['minLevel'];
        $rootRgt = $options['rootRgt'];
        $rootLft = $options['rootLft'];

        $builder
            ->add('code', 'text', array(
                    "label"=>"new_employee.fields.code",
                    "required"=>false,
                    "attr"=>array("autocomplete"=>"off")
            ))
            ->add('name', 'text', array(
            		"label"=>"new_employee.fields.name",
            		"required"=>true,
                    "attr"=>array("autocomplete"=>"off")
            ))
            ->add('surname', 'text', array(
            		"label"=>"new_employee.fields.surname",
            		"required"=>true,
                    "attr"=>array("autocomplete"=>"off")
            ))
            ->add('username', 'text', array(
                "label"=>"new_employee.fields.username",
                "required"=>true,
                "attr"=>array("autocomplete"=>"off")
            ))            
            ->add('email', 'email', array(
            		"label"=>"new_employee.fields.email",
            		"required"=>false,
                    "attr"=>array("autocomplete"=>"off")
            ))
            ->add('employed', 'choice', array(
                "label"=>"new_employee.fields.employed",
                "required"=>false,
                "choices"=>$employed
            ))
            ->add('ckk_number', 'text', array(
                "label"=>"new_employee.fields.ckk_number",
                "required"=>false,
                "attr"=>array("autocomplete"=>"off")
            ))
            ->add('mpk_number', 'text', array(
                "label"=>"new_employee.fields.mpk_number",
                "required"=>false,
                "attr"=>array("autocomplete"=>"off")
            ))
            ->add('departament', 'text', array(
                "label"=>"new_employee.fields.departament",
                "required"=>false,
                "attr"=>array("autocomplete"=>"off")
            ))
            ->add('region', 'text', array(
                "label"=>"new_employee.fields.region",
                "required"=>false,
                "attr"=>array("autocomplete"=>"off")
            ))
            /*
            ->add('position', 'text', array(
                    "label"=>"new_employee.fields.position",
                    "required"=>false
            ))
            */
            ->add('position', 'elearning_companies_position', array(
                    "label"=>"new_employee.fields.position",
                    "required"=>false
            ))
            ->add('phone', 'text', array(
                "label"=>"new_employee.fields.phone",
                "required"=>false,
                "attr"=>array("autocomplete"=>"off")
            ))
            ->add('address', 'text', array(
                    "label"=>"new_employee.fields.address",
                    "required"=>false,
                    "attr"=>array("autocomplete"=>"off")
            ))
            ->add('contract', 'text', array(
                "label"=>"new_employee.fields.contract",
                "required"=>false,
                "attr"=>array("autocomplete"=>"off")
            ))
            ->add('birthday', 'date', array(
                    "label"=>"new_employee.fields.birthday",
                    "format"=>"yyyy.MM.dd",
                    "years"=>range(1920, date("Y")),
                    "attr"=>array("autocomplete"=>"off")
            ))
            ->add('type', 'choice', array(
            		"label"=>"new_employee.fields.type",
                    "required"=>true,
                    "choices"=>$types
            ))
            ->add('parent', 'entity', array(
                "label"=>"new_employee.fields.parent",
                "class"=>Administrator::class,
                "query_builder"=>function($rep) use ($minLevel, $rootRgt, $rootLft) {
                    $qb = $rep
                        ->createQueryBuilder('a')
                        ->select('a, u')
                        ->join('a.user', 'u')
                        ->leftJoin('u.employee', 'e')
                        ->where('a.lvl >= :min_level')
                        ->andWhere('u.enabled = 1')
                        ->setParameter('min_level', $minLevel);
                    if ($rootRgt && $rootLft) {
                        $qb->andWhere('a.lft >= :rootLft')
                            ->andWhere('a.rgt <= :rootRgt')
                            ->setParameter('rootLft', $rootLft)
                            ->setParameter('rootRgt', $rootRgt);
                    }
                    return $qb;
                },
                "required"=>false,
                "mapped" => false,
                "attr" => !$options['showParent'] ? array('style' => 'display:none;') : array(),
                "label_attr" => !$options['showParent'] ? array('style' => 'display:none;') : array()
            ))
            ->add('password', 'password', array(
                'required'=>false,
                'label'=>'new_employee.fields.password'
            ))
            ->add('password_repeat', 'password', array(
                'required'=>false,
                'label'=>'new_employee.fields.password_repeat'
            ))
            ->add('active', 'choice', array(
            		"label"=>"new_employee.fields.active",
                    "required"=>true,
                    "choices"=>array(
                        "active"=>"new_employee.fields.active_choices.active",
                        "inactive"=>"new_employee.fields.active_choices.inactive",
                    ),
                    'data' => "active",
                    "attr"=>array("autocomplete"=>"off")
            ))
            ->add('imagefile', 'file', array(
                'label'=>'new_employee.fields.image',
                'required'=> false,
                "attr"=>array("autocomplete"=>"off")
            ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
        		'allow_extra_fields'=>true,
                'isSupervisor' => false,
                'canCreateAdmin' => true,
                'minLevel' => 0,
                'rootLft' => null,
                'rootRgt' => null,
                'showParent' => false
        ));
    }

    public function getName() {
        return "employee";
    }
}
