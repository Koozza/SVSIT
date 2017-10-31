<?php
namespace SITBundle\Admin;

use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class MollieCustomerAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
        $collection->add('edit',  $this->getRouterIdParameter().'/show');

        $collection->clearExcept(array('list', 'show', 'export', 'create', 'edit'));
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        die("Je kan geen mollie klanten aanmaken");
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('MOLLIE')) {
            $datagridMapper->add('name');
            $datagridMapper->add('email');
        }
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if ($this->isGranted('MOLLIE')) {
            $listMapper->add('name');
            $listMapper->add('email');
            $listMapper->add('mode');
            $listMapper->add('created');
            $listMapper->add('_action', null, array(
                'actions' => array(
                    'show' => array(
                        'template' => 'SITBundle:Admin:show_button.html.twig'
                    ),
                )
            ));
            ;
        }
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('MOLLIE')) {
            $showMapper->add('customerid');
            $showMapper->add('name');
            $showMapper->add('email');
            $showMapper->add('gebruiker');
            $showMapper->add('mode');
            $showMapper->add('created');
            $showMapper->add('payments');
            $showMapper->add('mandates');
            $showMapper->add('metadata');
        }
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


        return new CustomDoctrineORMQuerySourceIterator($query, $fields,'d F y H:i:s');
    }

    public function getExportFields()
    {
        $exportFields = array();

        if($this->isGranted('MOLLIE')) {
            $exportFields["Customer ID"] = 'customerid';
            $exportFields["Name"] = 'name';
            $exportFields["E-mailadres"] = 'email';
            $exportFields["Gebruiker"] = 'gebruiker';
            $exportFields["Mode"] = 'mode';
            $exportFields["Created"] = 'created';
            $exportFields["Mandates"] = 'mandates';
            $exportFields["Payments"] = 'payments';
            $exportFields["Metadata"] = 'metadata';
        }

        return $exportFields;
    }
}