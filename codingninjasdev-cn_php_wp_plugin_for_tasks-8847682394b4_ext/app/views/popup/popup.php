<div id="addNewTask">
	<div class="addNewTaskContainer">
		<div class="row">
		  <div class="col-xs-4 text-right"><?php echo $task_title;?></div>
		  <div class="col-xs-8"><input id="taskTitle" type="text"/></div>
		</div>
		<div class="row">
		  <div class="col-xs-4 text-right"><?php echo $freelancer_title;?></div>
		  <div class="col-xs-8">
			<?php echo $freelancer_select;?>
		  </div>
		</div>
			<div class="row">
		  <div class="col-xs-4 text-right"></div>
		  <div class="col-xs-8"><button type="button" class="btn btn-primary btn-xs" id="addTaskPopupButton"><?php echo $add_button_title;?></button></div>
		</div>
	</div>
</div>