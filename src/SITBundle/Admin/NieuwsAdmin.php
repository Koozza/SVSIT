<?php
namespace SITBundle\Admin;

use SITBundle\Entity\Evenement;
use SITBundle\Entity\Nieuwsbericht;
use SITBundle\Entity\Position;
use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class NieuwsAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'SITBundle:AdminDatetimeFix:edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_TITEL'))
            $formMapper->add('titel');
        if($this->isGranted('FIELD_AUTEUR'))
            $formMapper->add('auteur');
        if($this->isGranted('FIELD_BERICHT'))
            $formMapper->add('bericht', 'ckeditor', array());
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_TITEL'))
            $datagridMapper->add('titel');
        if($this->isGranted('FIELD_AUTEUR'))
            $datagridMapper->add('auteur');
        if($this->isGranted('FIELD_GEPUBLICEERD'))
            $datagridMapper->add('gepubliceerd');
        if($this->isGranted('FIELD_EDITED'))
            $datagridMapper->add('edited') ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_TITEL'))
            $listMapper->addIdentifier('titel');
        if($this->isGranted('FIELD_AUTEUR'))
            $listMapper->add('auteur');
        if($this->isGranted('FIELD_GEPUBLICEERD'))
            $listMapper->add('gepubliceerd');
        if($this->isGranted('FIELD_EDITED'))
            $listMapper->add('edited') ;
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_TITEL'))
            $showMapper->add('titel');
        if($this->isGranted('FIELD_AUTEUR'))
            $showMapper->add('auteur');
        if($this->isGranted('FIELD_BERICHT'))
            $showMapper->add('bericht') ;
        if($this->isGranted('FIELD_GEPUBLICEERD'))
            $showMapper->add('gepubliceerd');
        if($this->isGranted('FIELD_EDITED'))
            $showMapper->add('edited') ;
    }

    public function getExportFormats()
    {
        return array(
            'json', 'csv', 'xls'
        );
    }


    public function getDataSourceIterator()
    {

        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();
        $fields=$this->getExportFields();
        $query = $datagrid->getQuery();


        $query->select('DISTINCT ' . $query->getRootAlias());
        $query->setFirstResult(null);
        $query->setMaxResults(null);



        if ($query instanceof ProxyQueryInterface) {
            $query->addOrderBy($query->getSortBy(), $query->getSortOrder());

            $query = $query->getQuery();
        }


        return new CustomDoctrineORMQuerySourceIterator($query, $fields,'d F y G:i');
    }

    public function getExportFields()
    {
        $exportFields = array();

        if($this->isGranted('FIELD_TITEL'))
            $exportFields["Titel"] = 'titel';
        if($this->isGranted('FIELD_AUTEUR'))
            $exportFields["Auteur"] = 'auteur';
        if($this->isGranted('FIELD_GEPUBLICEERD'))
            $exportFields["Gepubliceerd"] = 'gepubliceerd';
        if($this->isGranted('FIELD_EDITED'))
            $exportFields["Edited"] = 'edited';
        if($this->isGranted('FIELD_BERICHT'))
            $exportFields["Bericht"] = 'bericht';

        return $exportFields;
    }

    public function preUpdate($entity)
    {
        if (!$entity instanceof Nieuwsbericht) {
            return;
        }

        $entity->setEdited(new \DateTime());
    }
}