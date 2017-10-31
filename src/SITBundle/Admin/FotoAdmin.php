<?php
namespace SITBundle\Admin;

use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Vich\UploaderBundle\Form\Type\VichImageType;

class FotoAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $fileFieldOptions = array('required' => true);
        $image = $this->getSubject();
        if ($image && ($webPath = $image->getWebPath())) {
            $fileFieldOptions['help'] = '<img src="'.$image->getWebPath().'" />';
        }
        if($this->isGranted('FIELD_FILE'))
            $formMapper->add('file', 'file', $fileFieldOptions);
        $formMapper->add('album');
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {

    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $image = $this->getSubject();
        if ($image && ($webPath = $image->getWebPath())) {
            $fileFieldOptions['help'] = '<img src="'.$image->getWebPath().'" />';
        }

        if($this->isGranted('FIELD_FOTO'))
            $showMapper->add('imageFile', VichImageType::class);
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

        return $exportFields;
    }

    public function prePersist($photo) {
        die("test");
        $this->saveFile($photo);
    }

    public function preUpdate($photo) {
        die("test");
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