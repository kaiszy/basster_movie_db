<?php

namespace Basster\MovieDbBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class StorageLocationType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('label' => 'Name'))
        ;
    }

    public function getName()
    {
        return 'storagelocation';
    }
}
