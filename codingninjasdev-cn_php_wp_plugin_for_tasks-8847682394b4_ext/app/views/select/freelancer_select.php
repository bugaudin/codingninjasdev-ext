<select name="<?php echo $select_id;?>" id="<?php echo $select_id;?>" class="postbox">
	<option value=""><?php echo $def_val;?></option>
<?php
foreach ($freelancers as $id => $name) {
	echo sprintf("<option value='%s' %s>%s</option>", $id, selected($value, $id), $name); 
}
?>
</select>