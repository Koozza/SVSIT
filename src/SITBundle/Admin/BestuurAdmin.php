<?php

namespace SITBundle\Admin;

use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class BestuurAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    /**
     * Sonata admin default method
     * Create's create/edit forms for Bestuur entity
     *
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $image = $this->getSubject();
        $fileFieldOptions = array('required' => true);
        if ($image && ($webPath = $image->getWebPath())) {
            $fileFieldOptions['help'] = '<img src="' . $image->getWebPath() . '" style="max-width: 350px;" />';
            $fileFieldOptions['required'] = false;
        }

        if ($this->isGranted('FIELD_NAAM')) {
            $formMapper->add('naam');
        }
        if ($this->isGranted('FIELD_BESTUURSLEDEN')) {
            $formMapper->add('bestuursleden');
        }
        if ($this->isGranted('FIELD_ISHUIDIGEBESTUUR')) {
            $formMapper->add('isHuidigeBestuur');
        }
        if ($this->isGranted('FIELD_FILE')) {
            $formMapper->add('file', 'file', $fileFieldOptions);
        }
    }


    /**
     * Sonata admin default method
     * Create filter fields for Bestuur entity
     *
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if ($this->isGranted('FIELD_NAAM')) {
            $datagridMapper->add('naam');
        }
        if ($this->isGranted('FIELD_ISHUIDIGEBESTUUR')) {
            $datagridMapper->add('isHuidigeBestuur');
        }
    }


    /**
     * Sonata admin default method
     * Create list fields for Bestuur entity
     *
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        if ($this->isGranted('FIELD_NAAM')) {
            $listMapper->addIdentifier('naam');
        }
        if ($this->isGranted('FIELD_ISHUIDIGEBESTUUR')) {
            $listMapper->add('isHuidigeBestuur');
        }
        if ($this->isGranted('FIELD_BESTUURSLEDEN')) {
            $listMapper->add('bestuursleden');
        }
    }


    /**
     * Sonata admin default method
     * Create show fields for Bestuur entity
     *
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if ($this->isGranted('FIELD_NAAM')) {
            $showMapper->add('naam');
        }
        if ($this->isGranted('FIELD_BESTUURSLEDEN')) {
            $showMapper->add('bestuursleden');
        }
        if ($this->isGranted('FIELD_ISHUIDIGEBESTUUR')) {
            $showMapper->add('isHuidigeBestuur');
        }
    }


    /**
     * Sonata admin default method
     * Pre-persist method to save bestuur pictures to database (New object)
     *
     * @param mixed $object
     */
    public function prePersist($object)
    {
        $this->saveFile($object);
        $this->preUpdate($object);
    }


    /**
     * Sonata admin default method
     * Pre-update method to save bestuur pictures to database (Existing object)
     *
     * @param mixed $object
     */
    public function preUpdate($object)
    {
        $this->saveFile($object);
        $object->setBestuursleden($object->getBestuursleden());
    }


    /**
     * Sonata admin default method
     * Allowed export formats
     *
     * @return array
     */
    public function getExportFormats()
    {
        return array(
            'json',
            'csv',
            'xls'
        );
    }


    /**
     * Sonata admin default method
     *
     * @return CustomDoctrineORMQuerySourceIterator
     */
    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();
        $fields = $this->getExportFields();
        $query = $datagrid->getQuery();


        $query->select('DISTINCT ' . $query->getRootAlias());
        $query->setFirstResult(null);
        $query->setMaxResults(null);


        if ($query instanceof ProxyQueryInterface) {
            $query->addOrderBy($query->getSortBy(), $query->getSortOrder());

            $query = $query->getQuery();
        }


        return new CustomDoctrineORMQuerySourceIterator($query, $fields, 'd F y');
    }


    /**
     * Sonata admin default method
     * Set all fields to be exported
     *
     * @return array
     */
    public function getExportFields()
    {
        $exportFields = array();

        if ($this->isGranted('FIELD_NAAM')) {
            $exportFields["Naam"] = 'naam';
        }
        if ($this->isGranted('FIELD_BESTUURSLEDEN')) {
            $exportFields["Bestuursleden"] = 'bestuursleden';
        }
        if ($this->isGranted('FIELD_ISHUIDIGEBESTUUR')) {
            $exportFields["Is Huidige Bestuur"] = 'isHuidigeBestuur';
        }

        return $exportFields;
    }


    /**
     * Call the upload method of a given object
     *
     * @param $object
     */
    public function saveFile($object)
    {
        $basepath = $this->getRequest()->getBasePath();
        $object->upload($basepath);
    }


    /**
     * Call the delete method of a given object
     *
     * @param $object
     */
    public function deleteFile($object)
    {
        $object->delete();
    }
}