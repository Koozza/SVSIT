<?php
namespace SITBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class CustomProductAdmin extends AbstractAdmin
{

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('create'));
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('naam')
            ->add('prijs')
            ->add('aantal')
            ->add('btw')
            ->add('isIncBtw');
    }
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('naam')
            ->add('prijs')
            ->add('aantal')
            ->add('btw')
            ->add('isIncBtw');
        ;
    }
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('naam')
            ->add('prijs')
            ->add('aantal')
            ->add('btw')
            ->add('isIncBtw');
    }
}