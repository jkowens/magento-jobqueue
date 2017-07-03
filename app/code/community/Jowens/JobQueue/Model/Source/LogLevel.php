<?php
if(file_exists(Mage::getBaseDir('lib').DS.'DJJob')){
    set_include_path(get_include_path().PS.Mage::getBaseDir('lib').DS.'DJJob');
    require_once('DJJob.php');
}
else if(!class_exists('\DJJob')){
    Mage::throwException('The class DJJob does not exist. Please add the class to /lib/DJJob/DJJob.php OR install DJJob via magento-composer-installer. See https://github.com/jkowens/magento-jobqueue for more details.');
}

class Jowens_JobQueue_Model_Source_LogLevel
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            DJBase::DEBUG => 'DEBUG',
            DJBase::INFO => 'INFO',
            DJBase::WARN => 'WARN',
            DJBase::ERROR => 'ERROR',
            DJBase::CRITICAL => 'CRITICAL'
        ];
    }
}