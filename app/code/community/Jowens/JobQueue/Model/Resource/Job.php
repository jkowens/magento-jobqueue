<?php

class Jowens_JobQueue_Model_Resource_Job extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('jobqueue/job', 'id');
    } 
}