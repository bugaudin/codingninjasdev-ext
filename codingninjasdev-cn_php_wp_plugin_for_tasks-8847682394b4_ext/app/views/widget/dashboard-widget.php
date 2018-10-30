 <?php if($show_info) {?>
 <div class="row dashboard-shortcode-info">
	<p>Shortcode <b>[cn_dashboard]</b> usage:</p>
	<p>Insert this shortcode to the page and the widget with current number of freelancers and tasks in the system will be displayed.</p>
	<p>Params <b>show_info</b>, default value is <b>true</b>.</p>
	<p>To turn off this description and display widget only insert shortcode with show_info as false.</p>
	<p>Example: <b>[cn_dashboard show_info=false]</b></p>
 </div>
 <?php } ?>
 
 <div class="row">
 
	<?php
	foreach ($data as $d) {
	?>
		<div class="col-lg-3 col-md-6">
			<div class="panel <?php echo $d['panel_class'];?>">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="fa <?php echo $d['panel_icon'];?> fa-5x"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge"><?php echo $d['items_count'];?></div>
							<div><?php echo $d['widget_item_name'];?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php
	}
	?>
</div>