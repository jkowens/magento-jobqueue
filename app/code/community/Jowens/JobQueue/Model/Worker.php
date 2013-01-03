<?php

set_include_path(get_include_path().PS.Mage::getBaseDir('lib').DS.'djjob');

require_once('DJJob.php');

class Jowens_JobQueue_Model_Worker extends Mage_Core_Model_Abstract 
{
	const DEFAULT_QUEUE = 'default';

	private $workerName;
	private $queue;

	public function __construct() {
		list($hostname, $pid) = array(trim(`hostname`), getmypid());
        $this->workerName = "host::$hostname pid::$pid";
        $this->queue = self::DEFAULT_QUEUE;	
	}

	public function getQueue() {
		return $this->queue;
	}

	public function setQueue($queue) {
		$this->queue = $queue;
	}

	public function getWorkerName() {
		return $this->workerName;
	}

	
	public function executeJobs($schedule=null) {
		if($schedule) {
			$jobsRoot = Mage::getConfig()->getNode('crontab/jobs');
	    	$jobConfig = $jobsRoot->{$schedule->getJobCode()};
	    	$this->queue = (string) $jobConfig->queue;
		}

		$this->setupDJJob();

		try {
			$collection = Mage::getModel('jobqueue/job')->getCollection();
			$collection->addFieldToFilter('queue', array('eq' => $this->getQueue()))
				->addFieldToFilter('run_at', array(
		                array('null' => true),
		                array('lteq'=> date('Y-m-d H:i:s', Mage::app()->getLocale()->storeTimeStamp()))					
				))
				->addFieldToFilter(array('locked_at', 'locked_by'), array(
		                array('locked_at', 'null' => true),
		                array('locked_by', 'eq' => $this->workerName)				
				))				
				->addFieldToFilter('failed_at', array('null' => true))
				->addFieldToFilter('attempts', array('lt' => (int)Mage::getStoreConfig('jobqueue/config/max_attempts')));

			// randomly order to prevent lock contention among workers
			$collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
			$collection->load();

			foreach($collection as $row) {
	            $job = new DJJob($this->workerName, $row->getId(), array(
	                "max_attempts" => Mage::getStoreConfig('jobqueue/config/max_attempts')
	            ));
	            if ($job->acquireLock()) {
	            	$job->run();
	            }
			}
		} catch (Exception $e) {
			Mage::log($e);
		}
	}

	protected function setupDJJob() {
		$config  = Mage::getConfig()->getResourceConnectionConfig("default_setup");
		DJJob::configure(
			"mysql:host=" . $config->host . ";dbname=" . $config->dbname . ";port=" . $config->port, 
			array('mysql_user' => $config->username, 'mysql_pass' => $config->password)
		);
	}	
}