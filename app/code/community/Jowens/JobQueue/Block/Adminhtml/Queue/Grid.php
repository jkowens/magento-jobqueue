<?php

class Jowens_JobQueue_Block_Adminhtml_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('created_at');
        $this->setId('jowens_jobqueue_grid');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }
     
    protected function _getCollectionClass()
    {
        return 'jobqueue/job_collection';
    }
     
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('jobqueue/job')->getCollection();
        //$collection->getSelect()->columns('(`main_table`.`failed_at` is null) as status');
        $collection->getSelect()->columns("(case when main_table.locked_at is not null then 2 when main_table.failed_at is null then 1 else 0 end) as status");        
        $this->setCollection($collection);
         
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'status') {
            $value = $column->getFilter()->getValue();
            if($value == '2') {
                $this->getCollection()->addFieldToFilter('locked_at', array('notnull'=> true));
            } else {
                $condition = $value == '1' ? 'null' : 'notnull';
                $this->getCollection()->addFieldToFilter('failed_at', array($condition => true));
                $this->getCollection()->addFieldToFilter('locked_at', array('null'=> true));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

     
    protected function _prepareColumns()
    {
        $this->addColumn('id',
            array(
                'header'=> $this->__('ID'),
                'align' => 'right',
                'type'  => 'number',
                'width' => '50px',
                'index' => 'id'
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => $this->__('Store'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'width' => '200px',                
            ));
        }        
         
        $this->addColumn('name',
            array(
                'header'=> $this->__('Name'),
                'index' => 'name'
            )
        );
         
        $this->addColumn('queue',
            array(
                'header'=> $this->__('Queue'),
                'index' => 'queue',
                'align' => 'center',
                'width' => '80px',
            )
        ); 

        $this->addColumn('created_at',
            array(
                'header'=> $this->__('Created At'),
                'index' => 'created_at',
                'type'  => 'datetime',
                'width' => '175px',
                'align' => 'center',
            )
        );         

        $this->addColumn('run_at',
            array(
                'header'=> $this->__('Run At'),
                'index' => 'run_at',
                'type'  => 'datetime',
                'align' => 'center',
            )
        );             

        $this->addColumn('attempts',
            array(
                'header'=> $this->__('Attempts'),
                'index' => 'attempts',
                'type'  => 'number',
                'align' => 'center',
                'width' => '100px',
            )
        );  

        $this->addColumn('status',
            array(
                'header'=> $this->__('Status'),
                'index' => 'status',
                'type'  => 'options',
                'options'   => array('1'=>'Pending', '2'=>'In Process', '0'=>'Failed'),
                'align' => 'center',
                'width' => '80px',
            )
        );                  

        $this->addColumn('action',
            array(
              'header'    => $this->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $this->__('View'),
                        'url'     => array('base'=>'*/*/view'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'align' => 'center',                
            )
        );                
         
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('job_id');

        $this->getMassactionBlock()->addItem('resubmit_job', array(
             'label'    => $this->__('Resubmit Job'),
             'url'      => $this->getUrl('*/*/massResubmitJob'),
             'confirm'  => $this->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('cancel_job', array(
             'label'    => $this->__('Cancel Job'),
             'url'      => $this->getUrl('*/*/massCancelJob'),
             'confirm'  => $this->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('delete_job', array(
             'label'    => $this->__('Delete Job'),
             'url'      => $this->getUrl('*/*/massDeleteJob'),
             'confirm'  => $this->__('Are you sure?')
        ));        

        return $this;
    }    
     
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }   
}