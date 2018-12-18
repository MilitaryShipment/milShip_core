<?php

class RequestEmail{

    const FRAMEWORK = '/srv/www/htdocs/classes/data/tonnageRequest_framework.html';
    const CONTENT = '/srv/www/htdocs/classes/data/tonnageRequest_content.html';
    const UNIGROUP = '/srv/www/htdocs/classes/data/tonnageRequest_unigroupContent.html';
    const INTERLINE = '/srv/www/htdocs/classes/data/tonnageRequest_interlineContent.html';

    public $msgBody;
    protected $user;
    protected $loads;
    protected $frameWork;
    protected $content;

    public function __construct($user,$loads){
        $this->user = $user;
        $this->loads = $loads;
        $this->frameWork = file_get_contents(self::FRAMEWORK);
        $this->_buildContent()
            ->_replaceFrameWork();
    }
    protected function _replaceFrameWork(){
        //$this->frameWork = preg_replace("/{COMPANY}/",$this->user->company,$this->frameWork);
        $this->frameWork = preg_replace("/{FIRST_NAME}/",$this->user->firstName,$this->frameWork);
        $this->frameWork = preg_replace("/{LAST_NAME}/",$this->user->lastName,$this->frameWork);
        $this->frameWork = preg_replace("/{EMAIL}/",$this->user->email,$this->frameWork);
        $this->frameWork = preg_replace("/{PHONE}/",$this->user->phone,$this->frameWork);
        $this->frameWork = preg_replace("/{CONTENTHTML}/",$this->content,$this->frameWork);
        $this->msgBody = $this->frameWork;
        return $this;
    }
    protected function _buildContent(){
        foreach($this->loads as $load){
            $directLoad = $load->formData->directLoad ? 'Yes' : 'No';
            $gpuApu = is_null($load->formData->anticipatedLoadDate) ? 'No' : 'Yes, Anticipated Pickup: ' . $load->formData->anticipatedLoadDate;
            $template = file_get_contents(self::CONTENT);
            $template = preg_replace("/{REGNUMBER}/",$load->order_number,$template);
            $template = preg_replace("/{ORIG_CITY}/",$load->orig_city,$template);
            $template = preg_replace("/{ORIG_STATE}/",$load->orig_state,$template);
            $template = preg_replace("/{DEST_CITY}/",$load->dest_city,$template);
            $template = preg_replace("/{DEST_STATE}/",$load->dest_state,$template);
            $template = preg_replace("/{DIRECTLOAD}/",$directLoad,$template);
            $template = preg_replace("/{G11APU}/",$gpuApu,$template);
            if(!empty($load->formData->agencyWorkflowNumber)){
                $unigroup = file_get_contents(self::UNIGROUP);
                $unigroup = preg_replace("/{WORKFLOWNUMBER}/",$load->formData->agencyWorkflowNumber,$unigroup);
                $template .= $unigroup;
            }
            if(!empty($load->formData->haulingAuthorityScac)){
                $interLine = file_get_contents(self::INTERLINE);
                $interLine = preg_replace("/{DRIVERNAME}/",$load->formData->driverName,$interLine);
                $interLine = preg_replace("/{DRIVERPHONE}/",$load->formData->driverPhone,$interLine);
                $interLine = preg_replace("/{HAULINGSCAC}/",$load->formData->haulingAuthorityScac,$interLine);
                $template .= $interLine;
            }
            $this->content .= $template;
            $this->_insertBreak();
        }
        return $this;
    }
    protected function _insertBreak(){
        $this->content .= "<div class='space'><br><br></div>";
        return $this;
    }
}
