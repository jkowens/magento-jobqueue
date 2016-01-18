#JobQueue

Do you need to run a process outside of a user generated request? Are you
working on an integration that requires "guaranteed delivery"? JobQueue
allows Magento applications to place tasks in a queue to be processed
asynchronously. It is built on DJJob and makes use of the existing MySQL backend. 
Some tasks this may be ideal for are:

* Downloading files
* Processing batch jobs
* Sending data to a back-office application or third party systems

###System Requirements

* PHP 5.1 or higher
* MySQL 4.1.20 or higher
* Magento CE1.6.0-1.9.x/EE1.7.0-1.14.x

###Usage

Jobs must extend Jowens_JobQueue_Model_Job_Abstract and implement the perform() method.

    class Foo_Bar_Model_Order_Job extends Jowens_JobQueue_Model_Job_Abstract
    {
      public function perform() {
        // implementation logic
      }
    }
	
That job can then be used like so:

    $job = Mage::getModel('bar/order_job');
    $job->setName('Order# 12345')
        ->enqueue();

Name is used to identify the job in backend, so be descriptive! The enqueue method can take two optional parameters, a string for queue name and timestamp to specify a time to run the job.

The job can also be attempted immediately. If it fails it is added to
the default queue for retry.

    $job = Mage::getModel('bar/order_job');
    $job->setName('Order# 12345')
        ->performImmediate();

To put the job on a queue other than the default one, performImmediate
takes an optional string value for the name of the retry queue.

###Running Jobs

JobQueue requires Magento cron to be configured in order to run pending jobs. By default a JobQueue worker executes the pending jobs every 5 minutes. If a job fails it will be retried up to 10 times. Both of these settings can be configured in the admin panel under System > Configuration > General > JobQueue.

Jobs in other queues can be executed by adding more cron entries to a custom module config.xml.

    <crontab>
	    <jobs>
	        <jobqueue_orders>
              <schedule>
                <config_path>jobqueue/config/cron_expr</config_path>
              </schedule>
              <run>
                <model>jobqueue/worker::executeJobs</model>
              </run>
              <queue>orders</queue>
	        </jobqueue_orders>
	    </jobs>
	</crontab>

Alternatively workers could be configured to run as they normally would using DJJob. See the [documentation](https://github.com/seatgeek/djjob#running-the-jobs).

###Monitoring Jobs

Pending and failed jobs can be monitored in the admin panel by going to System > JobQueue.
