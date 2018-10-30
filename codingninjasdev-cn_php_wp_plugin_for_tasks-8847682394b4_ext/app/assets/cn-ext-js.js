(function ($) {
    $(document).ready(function($) {
        $('#tasks_table').dynatable({
		  inputs: { queryEvent: 'keyup' }
		});
    });
}) (jQuery)

