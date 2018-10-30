(function ($) {
    $(document).ready(function($) {
        extendTasksTable();		
		registerAddNewTaskLinkClick();
		registerAddTaskPopupButtonClick();
    });
	
	function extendTasksTable(){
		$('#tasks_table').dynatable({
		  inputs: { queryEvent: 'keyup' }
		});
	}
	
	function registerAddNewTaskLinkClick(){
		$('#addNewTaskLink').click(function(){
			$('#addNewTask').dialog({
				modal: true,
				buttons: {
				Close: function() {
					  $( this ).dialog( "close" );
					}
				},
				title: jsRes.add_new_task_title,
				width:'auto'
			});
		});
	}
	
	function registerAddTaskPopupButtonClick(){
		$('#addTaskPopupButton').click(function(){
			var data = {
				'action': 'add_task_action',
				'taskTitle': $('#taskTitle').val(),
				'freelancerId': $('#freelancer_select').val()
			};

			jQuery.post(ajax_object.ajax_url, data, function(response) {
				alert(response);
				window.location.reload(false);
			});
		});
	}
}) (jQuery)

