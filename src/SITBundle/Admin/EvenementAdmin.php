<?php
namespace SITBundle\Admin;

use SITBundle\Entity\Evenement;
use SITBundle\Entity\Position;
use SITBundle\Exporter\Source\CustomDoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvenementAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    public $EventTypes = array('beer' => 'Feest/Borrel', 'book' => 'Studie', 'controller' => 'Game');

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
    }

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
        if($this->isGranted('FIELD_NAAM'))
            $formMapper->add('naam');
        if($this->isGranted('FIELD_OMSCHRIJVING'))
            $formMapper->add('omschrijving', 'ckeditor', array());
        if($this->isGranted('FIELD_DATUM'))
            $formMapper->add('datum') ;
        if($this->isGranted('FIELD_DUUR'))
            $formMapper->add('einddatum') ;
        if($this->isGranted('FIELD_TYPE'))
            $formMapper->add('type', ChoiceType::class, array('choices' => $this->EventTypes));
        if($this->isGranted('FIELD_LOCATIE_NAAM'))
            $formMapper->add('locatieNaam') ;
        if($this->isGranted('FIELD_LOCATIE_ADRES'))
            $formMapper->add('locatieAdres');
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $datagridMapper->add('naam');
        if($this->isGranted('FIELD_DATUM'))
            $datagridMapper->add('datum') ;
        if($this->isGranted('FIELD_DUUR'))
            $datagridMapper->add('einddatum') ;
        if($this->isGranted('FIELD_LOCATIE_NAAM'))
            $datagridMapper->add('locatieNaam') ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $listMapper->addIdentifier('naam') ;
        if($this->isGranted('FIELD_DATUM'))
            $listMapper->add('datum') ;
        if($this->isGranted('FIELD_DUUR'))
            $listMapper->add('einddatum') ;
        if($this->isGranted('FIELD_TYPE'))
            $listMapper->add('type', 'choice', array('choices' => $this->EventTypes));
        if($this->isGranted('FIELD_LOCATIE_NAAM'))
            $listMapper->add('locatieNaam') ;
        if($this->isGranted('FIELD_NAAM'))
            $listMapper->add('hasPositie', 'boolean', array('label' => 'Heeft Locatie')) ;
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_NAAM'))
            $showMapper->add('naam');
        if($this->isGranted('FIELD_OMSCHRIJVING'))
            $showMapper->add('omschrijving');
        if($this->isGranted('FIELD_DATUM'))
            $showMapper->add('datum') ;
        if($this->isGranted('FIELD_DUUR'))
            $showMapper->add('einddatum') ;
        if($this->isGranted('FIELD_TYPE'))
            $showMapper->add('type', 'choice', array('choices' => $this->EventTypes));
        if($this->isGranted('FIELD_LOCATIE_NAAM'))
            $showMapper->add('locatieNaam') ;
        if($this->isGranted('FIELD_LOCATIE_ADRES'))
            $showMapper->add('locatieAdres');
        if($this->isGranted('FIELD_NAAM'))
            $showMapper->add('hasPositie', 'boolean', array('label' => 'Heeft Locatie')) ;
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
        if($this->isGranted('FIELD_OMSCHRIJVING'))
            $exportFields["Omschrijving"] = 'omschrijving';
        if($this->isGranted('FIELD_DATUM'))
            $exportFields["Datum"] = 'datum';
        if($this->isGranted('FIELD_DUUR'))
            $exportFields["Eind"] = 'einddatum';
        if($this->isGranted('FIELD_TYPE'))
            $exportFields["Soort Evenement"] = 'type';
        if($this->isGranted('FIELD_LOCATIE_NAAM'))
            $exportFields["Locatie Naam"] = 'locatieNaam';
        if($this->isGranted('FIELD_LOCATIE_ADRES'))
            $exportFields["Locatie Adres"] = 'locatieAdres';
        if($this->isGranted('FIELD_NAAM'))
            $exportFields["Heeft Locatie"] = 'hasPositie';

        return $exportFields;
    }

    public function preUpdate($entity)
    {
        if (!$entity instanceof Evenement) {
            return;
        }


        if($entity->getLocatieAdres() != "") {
            $loc = $this->getGeoPosition($entity->getLocatieAdres());

            if($loc != null) {

                if($entity->getLocatiePositie() != null)
                    $this->getConfigurationPool()->getContainer()->get('Doctrine')->getEntityManager()->remove($entity->getLocatiePositie());

                $pos = new Position();
                $pos->setLat($loc[0]['geometry']['location']['lat']);
                $pos->setLng($loc[0]['geometry']['location']['lng']);

                $entity->setLocatiePositie($pos);
            }else{
                $oldPos = $entity->getLocatiePositie();
                $entity->setLocatiePositie(null);

                if($oldPos != null)
                    $this->getConfigurationPool()->getContainer()->get('Doctrine')->getEntityManager()->remove($oldPos);
            }
        }

    }

    public function prePersist($entity) {
        $this->preUpdate($entity);
    }

    public function getGeoPosition($address){
        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false" .
            "&address=" . urlencode($address);

        $json = file_get_contents($url);

        $data = json_decode($json, TRUE);

        if($data['status']=="OK"){
            return $data['results'];
        }

    }
}