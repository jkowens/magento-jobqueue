<?php
if(file_exists(Mage::getBaseDir('lib').DS.'DJJob')){
    set_include_path(get_include_path().PS.Mage::getBaseDir('lib').DS.'DJJob');
    require_once('DJJob.php');
}
else if(!class_exists('\DJJob')){
    Mage::throwException('The class DJJob does not exist. Please add the class to /lib/DJJob/DJJob.php OR install DJJob via magento-composer-installer. See https://github.com/jkowens/magento-jobqueue for more details.');
}

class Jowens_JobQueue_Model_Worker extends Mage_Core_Model_Abstract 
{
    const DEFAULT_QUEUE = 'default';

    private $workerName;
    private $queue;
            
    public function __construct() {
        list($hostname, $pid) = array(trim(`hostname`), getmypid());
        $this->workerName = "host::$hostname pid::$pid";
        $this->queue = Mage::getStoreConfig('jobqueue/config/queue');
        if(empty($this->queue)) {
            $this->queue = self::DEFAULT_QUEUE; 
        }
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
        if(!Mage::getStoreConfig('jobqueue/config/enabled')) {
            return;
        }

        if($schedule) {
            $jobsRoot = Mage::getConfig()->getNode('crontab/jobs');
            $jobConfig = $jobsRoot->{$schedule->getJobCode()};
            $queue = $jobConfig->queue;
            if($queue) {
                $this->setQueue($queue);
            }
        }

        $this->setupDJJob();

        try {
            $collection = Mage::getModel('jobqueue/job')->getCollection();
            $collection->addFieldToFilter('queue', array('eq' => $this->getQueue()))
            ->addFieldToFilter('run_at', array(
                array('null' => true),
                array('lteq' => now())
                ))
            ->addFieldToFilter(array('locked_at', 'locked_by'), array(
                array('locked_at', 'null' => true),
                array('locked_by', 'eq' => $this->workerName)               
                ))              
            ->addFieldToFilter('failed_at', array('null' => true))
            ->addFieldToFilter('attempts', array('lt' => (int)Mage::getStoreConfig('jobqueue/config/max_attempts')));

            if (Mage::getStoreConfigFlag('jobqueue/config/sort_random')) {
                // randomly order to prevent lock contention among workers
                $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
            } else {
                $collection->setOrder('id','ASC');
            }

            $limit = Mage::getStoreConfig('jobqueue/config/limit_jobs');
            if ($limit > 0) {
                $collection->getSelect()->limit($limit);
            }

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
            Mage::logException($e);
        }
    }

    protected function setupDJJob() {
        $config  = Mage::getConfig()->getResourceConnectionConfig("default_setup");
        
        $dsn = "";
        if (strpos($config->host, '/') !== false) {
            $dsn = "mysql:unix_socket=" . $config->host . ";dbname=" . $config->dbname;
        }
        elseif (strpos($config->host, ':') !== false) {
            list($host, $port) = explode(':', $config->host);
            $dsn = "mysql:host=" . $host . ";dbname=" . $config->dbname . ";port=" . $port;
        } else {
            $dsn = "mysql:host=" . $config->host . ";dbname=" . $config->dbname . ";port=" . $config->port;
        } 

        DJJob::configure(
            $dsn, 
            array('mysql_user' => $config->username, 'mysql_pass' => $config->password),
            Mage::getSingleton('core/resource')->getTableName('jobqueue/job')
        );

        if(!empty($config->initStatements)) {
            DJJob::runQuery($config->initStatements);
        }

        $logLevel = (int) Mage::getStoreConfig('jobqueue/config/log_level');
        DJBase::setLogLevel($logLevel);
    }
}
