<?php

namespace Basster\MovieDbBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MovieType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title','text',array('label' => 'Titel'))
            ->add('storageLocation', null, array('label' => 'Aufbewahrung'))
            ->add('movieDbLink', 'hidden')
        ;
    }

    public function getName()
    {
        return 'movie';
    }
}
