<?php

class Jowens_JobQueue_Block_Adminhtml_Queue extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'jobqueue';
        $this->_controller = 'adminhtml_queue';
        $this->_headerText = $this->__('JobQueue');
         
        parent::__construct();

        $this->removeButton('add');
    }	
}