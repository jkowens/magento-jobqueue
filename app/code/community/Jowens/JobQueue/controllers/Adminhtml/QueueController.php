<?php

class Jowens_JobQueue_Adminhtml_QueueController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_init()
            ->renderLayout();
    }

    protected function _init()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/jowens_jobqueue_queue')
            ->_title($this->__('System'))->_title($this->__('JobQueue'))
            ->_addBreadcrumb($this->__('System'), $this->__('System'))
            ->_addBreadcrumb($this->__('JobQueue'), $this->__('JobQueue'));

        return $this;
    }

    public function viewAction()
    {
        $id  = $this->getRequest()->getParam('id');
        $job = Mage::getModel('jobqueue/job');

        if ($id) {
            $job->load($id);

            if (!$job->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This job no longer exists.'));
                $this->_redirect('*/*/index');
                return;
            }
        }

        $this->_title($job->getId() ? $job->getName() : "Job Details");

        $data = Mage::getSingleton('adminhtml/session')->getJobData(true);
        if (!empty($data)) {
            $job->setData($data);
        }

        Mage::register('jowens_jobqueue_job', $job);

        $this->_init()
            ->renderLayout();
    }

    public function resubmitAction()
    {
        $id  = $this->getRequest()->getParam('id');
        $job = Mage::getModel('jobqueue/job');

        if ($id) {
            $job->load($id);

            if (!$job->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This job no longer exists.'));
                $this->_redirect('*/*/index');
                return;
            } 

            try {
                $job->resubmit();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job "%s" has been resubmitted', $job->getName()));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Job "%s" could not be resubmitted', $job->getName()));
            }
        } 
        $this->_redirect('*/*/index');
    }

    public function cancelAction()
    {
        $id  = $this->getRequest()->getParam('id');
        $job = Mage::getModel('jobqueue/job');

        if ($id) {
            $job->load($id);

            if (!$job->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This job no longer exists.'));
                $this->_redirect('*/*/index');
                return;
            } 

            try {
                $job->cancel();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job "%s" has been canceled', $job->getName()));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Job "%s" could not be canceled', $job->getName()));
            }
        } 
        $this->_redirect('*/*/index');
    }

    public function deleteAction()
    {
        $id  = $this->getRequest()->getParam('id');
        $job = Mage::getModel('jobqueue/job');

        if ($id) {
            $job->load($id);

            if (!$job->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This job no longer exists.'));
                $this->_redirect('*/*/index');
                return;
            }

            try {
                $job->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job "%s" has been deleted', $job->getName()));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Job "%s" could not be deleted', $job->getName()));
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massResubmitJobAction()
    {
        $jobIds = $this->getRequest()->getParam('job_id');
        $success = 0;
        $error = 0;

        foreach($jobIds as $jobId) {
            $job = Mage::getModel('jobqueue/job')->load($jobId);
            try {
                $job->resubmit();
                $success++;
            } catch (Exception $e) {
                Mage::log($e);
                $error++;
            }
        }


        if($error) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('%s job(s) could not be resubmitted', $error));
        }

        if($success) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s job(s) resubmitted', $success));
        }

        $this->_redirect('*/*/index');
    }

    public function massCancelJobAction()
    {
        $jobIds = $this->getRequest()->getParam('job_id');
        $success = 0;
        $error = 0;

        foreach($jobIds as $jobId) {
            $job = Mage::getModel('jobqueue/job')->load($jobId);
            try {
                if($job->getFailedAt()) {
                    $error++;
                } else {
                    $job->cancel();
                    $success++;
                }
            } catch (Exception $e) {
                Mage::log($e);
                $error++;
            }
        }


        if($error) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('%s job(s) could not be canceled', $error));
        }

        if($success) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s job(s) canceled', $success));
        }

        $this->_redirect('*/*/index');
    }

    public function massDeleteJobAction()
    {
        $jobIds = $this->getRequest()->getParam('job_id');
        $success = 0;
        $error = 0;

        foreach($jobIds as $jobId) {
            $job = Mage::getModel('jobqueue/job')->load($jobId);
            try {
                $job->delete();
                $success++;
            } catch (Exception $e) {
                Mage::log($e);
                $error++;
            }
        }


        if($error) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('%s job(s) could not be deleted', $error));
        }

        if($success) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s job(s) deleted', $success));
        }

        $this->_redirect('*/*/index');
    }
}
