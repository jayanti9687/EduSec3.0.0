<?php 
	$this->breadcrumbs=array(
		'Employee Attendences',
);
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'employee-attendence-form',
	'enableAjaxValidation'=>true,
)); ?>

	
	<?php echo $form->errorSummary($model); ?>
	

     	<?php echo CHtml::hiddenField('user_id1'); ?>
	
	<div class="row">
	     <?php //echo $form->labelEx($model,'employee_id'); ?>	
	     <?php echo CHtml::label('Employee Name',''); ?>	
	     <?php 
		if(!isset($emp_id) && empty($_REQUEST['emp_id']))  
		{
		     $this->widget('CAutoComplete',array(
		     'name'=>'employee_id', 
		     'url'=>CController::createUrl('Dependent/autocompletelookup'),
		     'minChars'=>1,
		     'delay'=>10,
		     'matchCase'=>false,
		     'methodChain'=>".result(function(event,item){\$(\"#user_id1\").val(item[1]);})",
		    
		     )); 
		}
		else if(!empty($_REQUEST['emp_id']))
		{
		$employee = EmployeeInfo::model()->findByAttributes(array('employee_info_transaction_id'=>$_REQUEST['emp_id']));
		$emp_name = $employee['employee_first_name'].''.$employee['employee_middle_name'].''.$employee['employee_last_name'];
		$this->widget('CAutoComplete',array(
		     'name'=>'employee_id', 
		     'url'=>CController::createUrl('Dependent/autocompletelookup'),
		     'minChars'=>1,
		     'delay'=>10,
		     'value'=>$emp_name,
		     'matchCase'=>false,
		     'methodChain'=>".result(function(event,item){\$(\"#user_id1\").val(item[1]);})",
		    
		     ));
		}
		else
		{
		$employee = EmployeeInfo::model()->findByAttributes(array('employee_info_transaction_id'=>$emp_id));
		$emp_name = $employee['employee_first_name'].''.$employee['employee_middle_name'].''.$employee['employee_last_name'];
		$this->widget('CAutoComplete',array(
		     'name'=>'employee_id', 
		     'url'=>CController::createUrl('Dependent/autocompletelookup'),
		     'minChars'=>1,
		     'delay'=>10,
		     'value'=>$emp_name,
		     'matchCase'=>false,
		     'methodChain'=>".result(function(event,item){\$(\"#user_id1\").val(item[1]);})",
		    
		     ));
		}
		?><span class="status">&nbsp;</span>
		<?php //echo $form->error($model,'employee_id'); ?>
	</div>

	
<?php $months = array();

for( $i = 1; $i <= 12; $i++ ) {
    $months[ $i ] = strftime( '%B', mktime( 0, 0, 0, $i, 1 ) );
}?>

	<div class="row" id = "printid1"> 
		<?php echo $form->labelEx($model,'month'); ?>
		<?php if(!isset($month) && empty($_REQUEST['month'])) { 
		echo $form->dropDownList($model,'month', $months, array('prompt'=>'Select Month','tabindex'=>1));
		 }
		else if(!empty($_REQUEST['month'])) {
		$month = $_REQUEST['month'];
		 echo $form->dropDownList($model,'month', $months, array('prompt'=>'Select Month','options'=>array($month=>array('selected'=>'selected'))));
		}
		else {
		 echo $form->dropDownList($model,'month', $months, array('prompt'=>'Select Month','options'=>array($month=>array('selected'=>'selected'))));	
		}
	?>
		
		<?php echo $form->error($model,'month'); ?>
	</div>


	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Submit' : 'Save',array('class'=>'submit')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->



<?php 
	if((!empty($emp_id) && !empty($month)) || (!empty($_REQUEST['emp_id']) && !empty($_REQUEST['month'])))
	{

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('employee-attendence-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<?php
if(!empty($emp_id) && !empty($month))
{
$dataProvider = $model->month_empattendance($emp_id,$month);
}
else if($_REQUEST['emp_id'] && $_REQUEST['month'])
{
$emp_id = $_REQUEST['emp_id'];
$month = $_REQUEST['month'];
$dataProvider = $model->month_empattendance($emp_id,$month);
}
if(Yii::app()->user->getState("pageSize",@$_GET["pageSize"]))
$pageSize = Yii::app()->user->getState("pageSize",@$_GET["pageSize"]);
else
$pageSize = Yii::app()->params['pageSize'];
$dataProvider->getPagination()->setPageSize($pageSize);
?>

</div><!-- search-form -->

<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'employee-attendence-grid',
	'dataProvider'=>$dataProvider,
	//'filter'=>$model,
	'afterAjaxUpdate' => 'reInstallDatepicker',
	'columns'=>array(
		array(
		'header'=>'SI No',
		'class'=>'IndexColumn',
		),
		array(
		'name'=>'employee_no',		
                'value'=> '$data->Rel_Emp_Info->employee_no',
	          ),
		array(
		'name'=>'employee_attendance_card_id',
		'value'=> '$data->Rel_Emp_Info->employee_attendance_card_id',
	          ),
		'attendence',		
		array(
		'name'=>'employee_first_name',
		'value'=> '$data->Rel_Emp_Info->employee_first_name',
	          ),		
		array(
                        'name' => 'date',
			'value'=>'($data->date == 0000-00-00) ? "Not Set" : date_format(new DateTime($data->date), "d-m-Y")',
                         'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'model' => $model, 
                                'attribute' => 'date',
				//'id'=>'date',
                                'options'=>array(
				'dateFormat'=>'dd-mm-yy',
				'changeYear'=>'true',
				'changeMonth'=>'true',
				'showAnim' =>'slide',
				'yearRange'=>'1900:'.(date('Y')+1),
				'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png',			
		    ),
		    'htmlOptions'=>array(
			'id'=>'date',
		     ),
			
                        ), 
                        true),

                ),
		array(
                       'header'=>'Holiday Name',
                       'value'=>'(NationalHolidays::model()->findByAttributes(array("national_holiday_date"=>$data->date))) ? NationalHolidays::model()->findByAttributes(array("national_holiday_date"=>$data->date))->national_holiday_name :"Not Set"',
               ),
		'total_hour',
		array(
			'name'=>'time1',
			'value'=>'($data->time1 == 00-00-00) ? "Not Set" : date("h:i:s A",strtotime($data->time1))',
		),
		array(
			'name'=>'time2',
			'value'=>'($data->time2 == 00-00-00) ? "Not Set" : date("h:i:s A",strtotime($data->time2))',
		),
		'overtime_hour',
		array(
			'class'=>'MyCButtonColumn',
			'template' => '{update}',
			 'buttons'=>array(
                        
                        'update' => array(
				'url'=>'Yii::app()->createUrl("Employee_attendence/Singleemployeeupdate", array("id"=>$data->employee_attendence_id))',
				'options' => array('class'=>'fees', 'target'=>'_blank'),
                        ),
                ),
			
		),
		
		
	),
		'pager'=>array(
		'class'=>'AjaxList',
		'maxButtonCount'=>$model->count(),
		'header'=>''
	    ),
)); 
Yii::app()->clientScript->registerScript('for-date-picker',"
function reInstallDatepicker(id, data){
        $('#date').datepicker({'dateFormat':'dd-mm-yy'});
}
");

	}
	
?>
