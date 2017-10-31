<?php
namespace SITBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class SponsorAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $image = $this->getSubject();
        $fileFieldOptions = array('required' => true);
        if ($image && ($webPath = $image->getWebPath())) {
            $fileFieldOptions['help'] = '<img src="'.$image->getWebPath().'" style="max-height: 400px; max-width: 400px;" />';
            $fileFieldOptions['required'] = false;
        }
        if($this->isGranted('FIELD_NAAM'))
            $formMapper->add('naam');
        if($this->isGranted('FIELD_URL')) {
            $formMapper->add('website');
            $formMapper->add('url');
        }
        if($this->isGranted('FIELD_BESCHRIJVING'))
            $formMapper->add('beschrijving', 'ckeditor', array());
        if($this->isGranted('FIELD_FILE'))
            $formMapper->add('file', 'file', $fileFieldOptions);

    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if ($this->isGranted('FIELD_NAAM'))
            $datagridMapper->add('naam');
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $listMapper->addIdentifier('naam');
        if($this->isGranted('FIELD_URL')) {
            $listMapper->add('website');
            $listMapper->add('url');
        }
        if($this->isGranted('FIELD_FILE'))
            $listMapper->add('webPath', null, array('template' => 'SITBundle:Admin:list_image.html.twig'));
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_FILE'))
            $showMapper->add('naam') ;
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

        if($this->isGranted('FIELD_NAAM'))
            $exportFields["Naam"] = 'naam';
        if($this->isGranted('FIELD_URL')) {
            $exportFields["Website"] = 'website';
            $exportFields["Link"] = 'url';
        }
        if($this->isGranted('FIELD_BESCHRIJVING'))
            $exportFields["Beschrijving"] = 'beschrijving';

        return $exportFields;
    }

    public function prePersist($photo) {
        $this->saveFile($photo);
    }

    public function preUpdate($photo) {
        $this->saveFile($photo);
    }

    public function saveFile($photo)
    {
        $basepath = $this->getRequest()->getBasePath();
        $photo->upload($basepath);
    }

    public function deleteFile($photo){
        $photo->delete();
    }
}