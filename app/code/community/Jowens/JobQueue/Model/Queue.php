<?php

class Jowens_JobQueue_Model_Queue extends Mage_Core_Model_Abstract
{
    public function enqueue($handler, $queue = "default", $run_at = null) {
    	$job = Mage::getModel('jobqueue/job');
    	$job->setStoreId($handler->getStoreId());
    	$job->setName($handler->getName());
    	$job->setHandler(serialize($handler));
    	$job->setQueue($queue);
    	$job->setRunAt($run_at);
    	$job->setCreatedAt(now());
    	$job->save();
    }	

	public function executeJobs($schedule=null) {
		$queue = null;
		if($schedule) {
			$jobsRoot = Mage::getConfig()->getNode('crontab/jobs');
	    	$jobConfig = $jobsRoot->{$schedule->getJobCode()};
	    	$queue = (string) $jobConfig->queue;
		}

		$worker = Mage::getModel('jobqueue/worker');
		$worker->setQueue($queue);
		$worker->executeJobs();
	}
}