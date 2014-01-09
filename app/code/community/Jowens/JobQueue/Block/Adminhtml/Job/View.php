<?php

class Jowens_JobQueue_Block_Adminhtml_Job_View extends Mage_Adminhtml_Block_Widget_Container
{

    protected $_job;

    public function __construct()
    {
        $this->_job = Mage::registry('jowens_jobqueue_job');

        $this->_blockGroup = 'jobqueue';
        $this->_controller = 'adminhtml_job';

        parent::__construct();

        $confirmMsg = $this->__('Are you sure you want to do this?');
        $resubmitUrl = $this->getUrl('*/*/resubmit', array('id' => $this->_job->getId()));
        $this->_addButton('resubmit', array(
            'label'     => $this->__('Resubmit'),
            'onclick'   => "confirmSetLocation('{$confirmMsg}', '{$resubmitUrl}')",
        ), 0, -10);

        if(!$this->_job->getFailedAt()) {
            $cancelUrl = $this->getUrl('*/*/cancel', array('id' => $this->_job->getId()));
            $this->_addButton('cancel', array(
                'label'     => $this->__('Cancel'),
                'onclick'   => "confirmSetLocation('{$confirmMsg}', '{$cancelUrl}')",
            ), 0, -5);
        }
    }

    public function getHeaderText()
    {
        return $this->__("Job: \"%s\"", $this->_job->getName()); 
    }

    protected function _toHtml()
    {
        $this->setJobIdHtml($this->escapeHtml($this->_job->getId()));
        $this->setJobNameHtml($this->escapeHtml($this->_job->getName()));
        $this->setJobNameHtml($this->escapeHtml($this->_job->getName()));

        $storeId = $this->_job->getStoreId();
        $store = Mage::app()->getStore($storeId);
        $this->setStoreNameHtml($this->escapeHtml($store->getName()));

        $this->setJobQueueHtml($this->escapeHtml($this->_job->getQueue()));
        $this->setAttemptsHtml($this->escapeHtml($this->_job->getAttempts()));

        $runAt = (strtotime($this->_job->getRunAt()))
            ? $this->formatDate($this->_job->getRunAt(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true)
            : $this->__('N/A');
        $this->setRunAtHtml($this->escapeHtml($runAt));

        $status = $this->__("Pending");
        if( $this->_job->getFailedAt()) {
            $status = $this->__('Failed');
        } else if($this->_job->getLockedAt()) {
             $status = $this->__('In Process');
        }
        $this->setStatusHtml($this->escapeHtml($status));

        $this->setErrorHtml($this->escapeHtml($this->_job->getError()));

        $createdAt = (strtotime($this->_job->getCreatedAt()))
            ? $this->formatDate($this->_job->getCreatedAt(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true)
            : $this->__('N/A');
        $this->setCreatedAtHtml($this->escapeHtml($createdAt));
        return parent::_toHtml();
    }
}
