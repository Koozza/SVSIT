<?php
namespace SITBundle\Admin;

use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class StudierichtingAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_STUDIERICHTING')) {
            $formMapper->add('studierichting');
            $formMapper->add('afkorting');
        }
        if($this->isGranted('FIELD_LIDMAATSCHAPPEN')) {
            $formMapper->add('lidmaatschappen');
        }
        if($this->isGranted('FIELD_STARTJAAR_RELEVANT'))
            $formMapper->add('startjaarRelevant', null, array('label' => 'Studiegegevens Nodig'));
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_STUDIERICHTING')) {
            $datagridMapper->add('studierichting');
            $datagridMapper->add('afkorting');
        }
        if($this->isGranted('FIELD_STARTJAAR_RELEVANT'))
            $datagridMapper->add('startjaarRelevant', null, array('label' => 'Studiegegevens Nodig'));
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_STUDIERICHTING')) {
            $listMapper->addIdentifier('studierichting');
            $listMapper->addIdentifier('afkorting');
        }
        if($this->isGranted('FIELD_LIDMAATSCHAPPEN')) {
            $listMapper->add('lidmaatschappen');
        }
        if($this->isGranted('FIELD_STARTJAAR_RELEVANT'))
            $listMapper->add('startjaarRelevant', null, array('label' => 'Studiegegevens Nodig'));

        if($this->isGranted('FIELD_GEBRUIKERS'))
            $listMapper->add('aantalGebruikers', null, array('label' => 'Leden', 'mapped' => false));
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_STUDIERICHTING')) {
            $showMapper->add('studierichting');
            $showMapper->add('afkorting');
        }
        if($this->isGranted('FIELD_LIDMAATSCHAPPEN')) {
            $showMapper->add('lidmaatschappen');
        }
        if($this->isGranted('FIELD_STARTJAAR_RELEVANT'))
            $showMapper->add('startjaarRelevant', null, array('label' => 'Studiegegevens Nodig'));

        if($this->isGranted('FIELD_GEBRUIKERS'))
            $showMapper->add('aantalGebruikers', null, array('label' => 'Aantal Leden', 'mapped' => false));

        if($this->isGranted('FIELD_GEBRUIKERS'))
            $showMapper->add('gebruikers');
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


        return new CustomDoctrineORMQuerySourceIterator($query, $fields,'d F y');
    }

    public function getExportFields()
    {
        $exportFields = array();

        if($this->isGranted('FIELD_STUDIERICHTING')) {
            $exportFields["Studierichting"] = 'studierichting';
            $exportFields["Afkorting"] = 'afkorting';
        }

        if($this->isGranted('FIELD_LIDMAATSCHAPPEN'))
            $exportFields["Lidmaatschappen"] = 'lidmaatschappen';
        if($this->isGranted('FIELD_STARTJAAR_RELEVANT'))
            $exportFields["Startjaar Relevant"] = 'startjaarRelevant';
        if($this->isGranted('FIELD_GEBRUIKERS'))
            $exportFields["Aantal Gebruikers"] = 'aantalGebruikers';
        if($this->isGranted('FIELD_GEBRUIKERS'))
            $exportFields["Gebruikers"] = 'gebruikers';

        return $exportFields;
    }
}