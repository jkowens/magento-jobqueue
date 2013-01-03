<?php

class Jowens_JobQueue_Model_Job extends Mage_Core_Model_Abstract 
{
    protected function _construct()
    {
        $this->_init('jobqueue/job');
    }

    public function resubmit() {
        $this->setFailedAt(null);
        $this->setRunAt(null);
        $this->setAttempts(0);
        $this->setError(null);
        $this->save();
    }

    public function cancel() {
        $this->setFailedAt(Mage::getModel('core/date')->timestamp(time()));
        $this->setError(Mage::helper('jobqueue')->__("Job canceled"));
        $this->save();
    }    
}