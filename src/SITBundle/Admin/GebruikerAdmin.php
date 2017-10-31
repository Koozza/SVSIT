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

class GebruikerAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    protected $datagridValues = array(
        '_page' => 1,
        '_per_page' => 'All',
        '_sort_order' => 'DESC',
    );

    protected $maxPerPage = 'All';

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('incasseren', $this->getRouterIdParameter().'/incasseren');
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

    public function getBatchActions()
    {
        // retrieve the default batch actions (currently only delete)
        $actions = parent::getBatchActions();

        if (
            $this->isGranted('MOLLIE')
        ) {
            $actions['incasseren'] = array(
                'label' => 'Incasseren',
                'translation_domain' => 'SonataAdminBundle',
                'ask_confirmation' => true
            );

        }

        return $actions;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->isGranted('FIELD_VOORNAAM'))
            $formMapper->add('voornaam');
        if($this->isGranted('FIELD_TUSSENVOEGSEL'))
            $formMapper->add('tussenvoegsel', null, array('required' => false));
        if($this->isGranted('FIELD_ACHTERNAAM'))
            $formMapper->add('achternaam');
        if($this->isGranted('FIELD_GESLACHT'))
            $formMapper->add('geslacht', 'choice',  array('choices' => self::getGeslachtTypes()));
        if($this->isGranted('FIELD_GEBOORTEDATUM'))
            $formMapper->add('geboortedatum', 'birthday', array('years' => range(1980, date('Y')), 'format' => 'dd MM yyyy'));
        if($this->isGranted('FIELD_ADRES'))
            $formMapper->add('adres');
        if($this->isGranted('FIELD_POSTCODE'))
            $formMapper->add('postcode');
        if($this->isGranted('FIELD_WOONPLAATS'))
            $formMapper->add('woonplaats');
        if($this->isGranted('FIELD_TELEFOONNUMMER'))
            $formMapper->add('telefoonnummer');
        if($this->isGranted('FIELD_EMAILADRES'))
            $formMapper->add('emailadres');
        if($this->isGranted('FIELD_STUDENTNUMMER'))
            $formMapper->add('studentnummer');
        if($this->isGranted('FIELD_STARTJAAR'))
            $formMapper->add('startjaar');
        if($this->isGranted('FIELD_INSCHRIJFDATUM'))
            $formMapper->add('inschrijfdatum', null, array('years' => range(2015, date('Y')), 'format' => 'dd MM yyyy'));
        if($this->isGranted('FIELD_HEEFT_BEVESTIGING'))
            $formMapper->add('heeftBevestiging');
        if($this->isGranted('FIELD_STUDIERICHTING'))
            $formMapper->add('studierichting', 'sonata_type_model', array('required' => false, 'expanded' => false, 'multiple' => false, 'label' => 'Studierichting'));
        if($this->isGranted('FIELD_LIDMAATSCHAP'))
            $formMapper->add('lidmaatschap', 'sonata_type_model', array('required' => false, 'expanded' => false, 'multiple' => false, 'label' => 'Lidmaatschap'));
        ;
    }

    public static function getGeslachtTypes()
    {
        return array(
            'Man' => 'Man', 'Vrouw' => 'Vrouw'
        );
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if($this->isGranted('FIELD_VOORNAAM'))
            $datagridMapper->add('voornaam');
        if($this->isGranted('FIELD_ACHTERNAAM'))
            $datagridMapper->add('achternaam');
        if($this->isGranted('FIELD_GESLACHT'))
            $datagridMapper->add('geslacht','doctrine_orm_choice',array('label' => 'Geslacht'),'choice',array('choices' => self::getGeslachtTypes()));
        if($this->isGranted('FIELD_STUDENTNUMMER'))
            $datagridMapper->add('studentnummer');
        if($this->isGranted('FIELD_HEEFT_BEVESTIGING'))
            $datagridMapper->add('heeftBevestiging');
        if($this->isGranted('FIELD_STUDIERICHTING'))
            $datagridMapper->add('studierichting');
        if($this->isGranted('FIELD_LIDMAATSCHAP'))
            $datagridMapper->add('lidmaatschap');
        if($this->isGranted('FIELD_BETAALD'))
            $datagridMapper->add('betaald', 'doctrine_orm_callback', array(
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!isset($value['value'])) {
                        return;
                    }

                    $q = $this->getConfigurationPool()->getContainer()->get('Doctrine')->getManager()->createQueryBuilder();

                    $date = new \DateTime();
                    $q
                        ->select(array('g.id'))
                        ->from('SITBundle\Entity\Gebruiker', 'g')
                        ->leftJoin('g.betalingen', 'b')
                        ->leftJoin('b.periodes', 'p')
                        ->leftJoin('b.molliePayment', 'mp')
                        ->where($q->expr()->orX(
                            $q->expr()->eq('b.isLifetime', '1'),
                            $q->expr()->gte('p.einddatum', ':time')
                        ))
                        ->andWhere(
                            $q->expr()->orX(
                                $q->expr()->isNull('b.molliePayment'),
                                $q->expr()->eq('mp.status', ':status')
                            )
                        )
                        ->setParameter('time', $date, \Doctrine\DBAL\Types\Type::DATETIME)
                        ->setParameter('status', 'paid')
                        ->groupBy('g.id');

                    //Change to flat array
                    $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($q->getQuery()->getArrayResult()));
                    $s = '';
                    foreach($it as $v) {
                        $s .= $v.',';
                    }

                    if($value['value'] == 1)
                        $queryBuilder->andWhere('o.id IN ('.rtrim($s, ",").')');
                    else
                        $queryBuilder->andWhere('o.id NOT IN ('.rtrim($s, ",").')');

                    return true;
                }
            ), 'choice', array(
                    'translation_domain' => 'SonataCoreBundle',
                    'choices' => array(
                        1 => 'Yes',
                        0 => 'No'
                    )
                )
            );
        if($this->isGranted('FIELD_LIDMAATSCHAP'))
            $datagridMapper->add('geactiveerd', 'doctrine_orm_callback', array(
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!isset($value['value'])) {
                        return;
                    }

                    if($value['value'] == 1)
                        $queryBuilder->andWhere('o.activatiecode IS NULL');
                    else
                        $queryBuilder->andWhere('o.activatiecode IS NOT NULL');

                    return true;
                }
            ), 'choice', array(
                    'translation_domain' => 'SonataCoreBundle',
                    'choices' => array(
                        1 => 'Yes',
                        0 => 'No'
                    )
                )
            );
        ;
    }


    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        if($this->isGranted('FIELD_VOORNAAM'))
            $listMapper->addIdentifier('voornaam');
        if($this->isGranted('FIELD_TUSSENVOEGSEL'))
            $listMapper->add('tussenvoegsel');
        if($this->isGranted('FIELD_ACHTERNAAM'))
            $listMapper->add('achternaam');
        if($this->isGranted('FIELD_GESLACHT'))
            $listMapper->add('geslacht');
        if($this->isGranted('FIELD_GEBOORTEDATUM'))
            $listMapper->add('geboortedatum', null, array('format' => 'd M Y'));
        if($this->isGranted('FIELD_EMAILADRES'))
            $listMapper->add('emailadres');
        if($this->isGranted('FIELD_STUDENTNUMMER'))
            $listMapper->add('studentnummer');
        if($this->isGranted('FIELD_STARTJAAR'))
            $listMapper->add('startjaar' ,'text');
        if($this->isGranted('FIELD_INSCHRIJFDATUM'))
            $listMapper->add('inschrijfdatum', null, array('format' => 'd M Y'));
        if($this->isGranted('FIELD_BETAALD'))
            $listMapper->add('betaald', 'boolean');
        if($this->isGranted('MOLLIE'))
            $listMapper->add('checkIncassoPossibleInFuture', 'boolean', array('label'=>'Incasso'));
        if($this->isGranted('FIELD_HEEFT_BEVESTIGING'))
            $listMapper->add('heeftBevestiging', null, array('label' => 'Welkom'));
        if($this->isGranted('FIELD_GEACTIVEERD'))
            $listMapper->add('geactiveerd', 'boolean', array('label' => 'Actief'));
        if($this->isGranted('FIELD_STUDIERICHTING'))
            $listMapper->add('studierichting.afkorting', null, array('label' => 'Richting'));
        if($this->isGranted('FIELD_LIDMAATSCHAP'))
            $listMapper->add('lidmaatschap.afkorting', null, array('label' => 'Type'));

        if($this->isGranted('MOLLIE')) {
            $listMapper->add('_action', null, array(
                'actions' => array(
                    'incasseren' => array(
                        'template' => 'SITBundle:CRUD:list__action_incasseren.html.twig'
                    )
                )
            ));
        }

    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        if($this->isGranted('FIELD_VOORNAAM'))
            $showMapper->add('voornaam');
        if($this->isGranted('FIELD_TUSSENVOEGSEL'))
            $showMapper->add('tussenvoegsel');
        if($this->isGranted('FIELD_ACHTERNAAM'))
            $showMapper->add('achternaam');
        if($this->isGranted('FIELD_GESLACHT'))
            $showMapper->add('geslacht');
        if($this->isGranted('FIELD_GEBOORTEDATUM'))
            $showMapper->add('geboortedatum');
        if($this->isGranted('FIELD_ADRES'))
            $showMapper->add('adres');
        if($this->isGranted('FIELD_POSTCODE'))
            $showMapper->add('postcode');
        if($this->isGranted('FIELD_WOONPLAATS'))
            $showMapper->add('woonplaats');
        if($this->isGranted('FIELD_TELEFOONNUMMER'))
            $showMapper->add('telefoonnummer');
        if($this->isGranted('FIELD_EMAILADRES'))
            $showMapper->add('emailadres');
        if($this->isGranted('FIELD_STUDENTNUMMER'))
            $showMapper->add('studentnummer');
        if($this->isGranted('FIELD_STARTJAAR'))
            $showMapper->add('startjaar');
        if($this->isGranted('FIELD_INSCHRIJFDATUM'))
            $showMapper->add('inschrijfdatum', null, array('format' => 'd M Y'));
        if($this->isGranted('FIELD_HEEFT_BEVESTIGING'))
            $showMapper->add('heeftBevestiging');
        if($this->isGranted('FIELD_STUDIERICHTING'))
            $showMapper->add('studierichting');
        if($this->isGranted('FIELD_LIDMAATSCHAP'))
            $showMapper->add('lidmaatschap');
        ;
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

        if($this->isGranted('FIELD_VOORNAAM'))
            $exportFields["Voornaam"] = 'voornaam';
        if($this->isGranted('FIELD_TUSSENVOEGSEL'))
            $exportFields["Tussenvoegsel"] = 'tussenvoegsel';
        if($this->isGranted('FIELD_ACHTERNAAM'))
            $exportFields["Achternaam"] = 'achternaam';
        if($this->isGranted('FIELD_GESLACHT'))
            $exportFields["Geslacht"] = 'geslacht';
        if($this->isGranted('FIELD_GEBOORTEDATUM'))
            $exportFields["Geboortedatum"] = 'geboortedatum';
        if($this->isGranted('FIELD_EMAILADRES'))
            $exportFields["E-mailadres"] = 'emailadres';
        if($this->isGranted('FIELD_STUDENTNUMMER'))
            $exportFields["Studentnummer"] = 'studentnummer';
        if($this->isGranted('FIELD_STARTJAAR'))
            $exportFields["Startjaar"] = 'startjaar';
        if($this->isGranted('FIELD_INSCHRIJFDATUM'))
            $exportFields["Inschrijfdatum"] = 'inschrijfdatum';
        if($this->isGranted('FIELD_BETAALD'))
            $exportFields["Betaald"] = 'betaald';
        if($this->isGranted('MOLLIE'))
            $exportFields["Incasso Mogelijk"] = 'checkIncassoPossibleInFuture';
        if($this->isGranted('FIELD_HEEFT_BEVESTIGING'))
            $exportFields["Heeft Bevestiging"] = 'heeftBevestiging';
        if($this->isGranted('FIELD_GEACTIVEERD'))
            $exportFields["Geactiveerd"] = 'geactiveerd';
        if($this->isGranted('FIELD_STUDIERICHTING'))
            $exportFields["Studierichting"] = 'studierichting.studierichting';
        if($this->isGranted('FIELD_LIDMAATSCHAP'))
            $exportFields["Lidmaatschap"] = 'lidmaatschap.afkorting';

        return $exportFields;
    }
}