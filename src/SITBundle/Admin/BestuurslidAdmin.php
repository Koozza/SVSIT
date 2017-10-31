<?php
namespace SITBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class BestuurslidAdmin extends AbstractAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 'All');

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $image = $this->getSubject();
        $fileFieldOptions = array('required' => true);
        if ($image && ($webPath = $image->getWebPath())) {
            $fileFieldOptions['help'] = '
            <div style="width: 192px; height: 192px; overflow: hidden; position: relative;" id="bestuursImageContainer">
                <img src="'.$image->getWebPath().'" style="position: relative; left: 0px; top: 0px;"  id="bestuursImage" />
                <img src="/bundles/sit/images/bestuur_overlay.png" style="position: absolute; left: 0px; top: 0px; cursor: move" id="overlayImage" />
            </div>
            
            <script type="text/javascript">
                var xMousePos = 0;
                var yMousePos = 0;
                var lastScrolledLeft = 0;
                var lastScrolledTop = 0;
                
                function adjustEdges() {
                    if(parseInt($("#bestuursImage").css("left".replace("px",""))) > 0) {
                        $("#bestuursImage").css("left", "0px");
                    }
                    if(parseInt($("#bestuursImage").css("top".replace("px",""))) > 0) {
                        $("#bestuursImage").css("top", "0px");
                    }
                    if($("#bestuursImage").height() - parseInt($("#bestuursImage").css("top".replace("px",""))) * -1 < 192) {
                        $("#bestuursImage").css("top", "-" +($("#bestuursImage").height() - 192)+"px");
                    }
                    if($("#bestuursImage").width() - parseInt($("#bestuursImage").css("left".replace("px",""))) * -1 < 192) {
                        $("#bestuursImage").css("left", "-" +($("#bestuursImage").width() - 192)+"px");
                    }
                }
                
                $(document).ready(function() {
                    if($(".hidden_width").val() != 0)
                        $("#bestuursImage" ).css("width", $(".hidden_width").val() + "px");
                    $("#bestuursImage" ).css("left", $(".hidden_left").val() + "px");
                    $("#bestuursImage" ).css("top", $(".hidden_top").val() + "px");
                    $("#bestuursImage" ).draggable({
                        cursor: "move",
                        stop: function( event, ui ){
                            adjustEdges();
                            
                            $(".hidden_width").val(parseInt($("#bestuursImage").css("width".replace("px",""))));
                            $(".hidden_left").val(parseInt($("#bestuursImage").css("left".replace("px",""))));
                            $(".hidden_top").val(parseInt($("#bestuursImage").css("top".replace("px",""))));
                        }
                    });
                });
                
                $(\'#overlayImage\').mousedown(function(ev) {
                    $(\'#bestuursImage\').trigger(ev);
                });
                
                $(document).bind(\'mousewheel\', function(e){
                    
                    var parentOffset = $("#bestuursImageContainer").parent().offset(); 
                    var relX = xMousePos - parentOffset.left;
                    var relY = yMousePos - parentOffset.top;
                    
                    if(relX < 0 || relX < 0 || relX < $("#bestuursImageContainer").width() || relY < $("#bestuursImageContainer").height()) {
                        if(e.originalEvent.wheelDelta /120 > 0) { //Scroll up
                            if($("#bestuursImage").width() + 20 >= 192) {
                                var orgHeight = $("#bestuursImage").height();
                                $("#bestuursImage").css("width", $("#bestuursImage").width() + 20);
                                $("#bestuursImage").css("left", (parseInt($("#bestuursImage").css("left").replace("px","")) - 10) + "px");
                                $("#bestuursImage").css("top", (parseInt($("#bestuursImage").css("top".replace("px",""))) + (orgHeight - $("#bestuursImage").height()) / 2));
                            }
                            //Terugzetten als het te klein is geworden
                            if($("#bestuursImage").height() < 192){
                                var orgHeight = $("#bestuursImage").height();
                                $("#bestuursImage").css("width", $("#bestuursImage").width() - 20);
                                $("#bestuursImage").css("left", (parseInt($("#bestuursImage").css("left").replace("px","")) + 10) + "px");
                                $("#bestuursImage").css("top", (parseInt($("#bestuursImage").css("top".replace("px",""))) + (orgHeight - $("#bestuursImage").height()) / 2));
                            }   
                        }else{
                            if($("#bestuursImage").width() - 20 >= 192) {
                                var orgHeight = $("#bestuursImage").height();
                                $("#bestuursImage").css("width", $("#bestuursImage").width() - 20);
                                $("#bestuursImage").css("left", (parseInt($("#bestuursImage").css("left").replace("px","")) + 10) + "px");
                                $("#bestuursImage").css("top", (parseInt($("#bestuursImage").css("top".replace("px",""))) + (orgHeight - $("#bestuursImage").height()) / 2));
                            }
                            //Terugzetten als het te klein is geworden
                            if($("#bestuursImage").height() < 192){
                                var orgHeight = $("#bestuursImage").height();
                                $("#bestuursImage").css("width", $("#bestuursImage").width() + 20);
                                $("#bestuursImage").css("left", (parseInt($("#bestuursImage").css("left").replace("px","")) - 10) + "px");
                                $("#bestuursImage").css("top", (parseInt($("#bestuursImage").css("top".replace("px",""))) + (orgHeight - $("#bestuursImage").height()) / 2));
                            }     
                        }
                        adjustEdges();
                            
                        $(".hidden_width").val(parseInt($("#bestuursImage").css("width".replace("px",""))));
                        $(".hidden_left").val(parseInt($("#bestuursImage").css("left".replace("px",""))));
                        $(".hidden_top").val(parseInt($("#bestuursImage").css("top".replace("px",""))));
                    }
                    
                });
                
                $(document).on(\'scroll\', function(e) {
                    if(lastScrolledLeft != $(document).scrollLeft()){
                        xMousePos -= lastScrolledLeft;
                        lastScrolledLeft = $(document).scrollLeft();
                        xMousePos += lastScrolledLeft;
                    }
                    if(lastScrolledTop != $(document).scrollTop()){
                        yMousePos -= lastScrolledTop;
                        lastScrolledTop = $(document).scrollTop();
                        yMousePos += lastScrolledTop;
                    }
                });
                
                $(document).mousemove(function(event) {
                    captureMousePosition(event);
                });
                
                function captureMousePosition(event){
                    xMousePos = event.pageX;
                    yMousePos = event.pageY;
                }
                
            </script>';
            $fileFieldOptions['required'] = false;
        }
        if($this->isGranted('FIELD_NAAM'))
            $formMapper->add('naam');
        if($this->isGranted('FIELD_FUNCTIE'))
            $formMapper->add('functie');
        if($this->isGranted('FIELD_POSITIE'))
            $formMapper->add('positie');
        if($this->isGranted('FIELD_FBLINK'))
            $formMapper->add('fblink', null, array('label' => 'Facebook URL'));
        if($this->isGranted('FIELD_INLINK'))
            $formMapper->add('inlink', null, array('label' => 'Linkedin URL'));
        if($this->isGranted('FIELD_FILE'))
            $formMapper->add('file', 'file', $fileFieldOptions);

        $formMapper->add('l', 'hidden', array('attr' => ["class" => "hidden_left"]));
        $formMapper->add('t', 'hidden', array('attr' => ["class" => "hidden_top"]));
        $formMapper->add('w', 'hidden', array('attr' => ["class" => "hidden_width"]));

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
        if($this->isGranted('FIELD_FUNCTIE'))
            $exportFields["Functie"] = 'functie';
        if($this->isGranted('FIELD_FBLINK'))
            $exportFields["Facebook"] = 'fblink';
        if($this->isGranted('FIELD_INLINK'))
            $exportFields["LinkedIn"] = 'inlink';

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