<?php
namespace SITBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class FactuurProductKoppelingAdmin extends AbstractAdmin
{

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('create'));
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('product_original',null,array('label'=>'Product', 'required'=>'true'))
            ->add('maat_original',null,array('label'=>'Maat'))
            ->add('aantal',null,array('label'=>'Aantal'));
    }
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('aantal')
            ->add('maat_original')
            ->add('product_original')
        ;
    }
    protected function configureListFields(ListMapper $listMapper)
    {
        if (!$this->isChild())
            $listMapper->addIdentifier('id')->addIdentifier('factuur');

        $listMapper
            ->addIdentifier('product_original')
            ->add('maat')
            ->addIdentifier('aantal');
    }
}