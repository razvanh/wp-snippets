<?php 
// prepare to get a list of events sorted by the event start date
global $post;     
$args = array(
	'post_type' => 'event', //This uses an "event" custom post type
	'posts_per_page' => -1,
	'meta_query' => array(
		array(
			'key' => '_eve_sta_date',
			'value' => time(),
			'compare' => '>=',
					                //'type'    => 'DATE' 
			),
		),
	'orderby' => 'meta_value_num',
	'meta_key'=>'_eve_sta_date',
	'order' => 'ASC',
	);	
$day = date("j F, Y");// Current date				 
$query = new WP_Query( $args);
if ($query->have_posts()){	
	// The Loop
	while ( $query->have_posts() ) :
		$query->the_post();?>
	<?php
	$post_id = get_the_ID(); 
	$start_date=date("j F, Y",get_post_meta( $post_id, '_eve_sta_date', true ));
	$start_time=get_post_meta( $post_id, '_eve_sta_time', true );
	$end_date=date("j F, Y",get_post_meta( $post_id, '_eve_end_date', true ));
	$end_time=get_post_meta( $post_id, '_eve_end_time', true );
	$all_day=get_post_meta( $post_id, '_eve_all_day', true );
	?>
	<?php if ($start_date != $day) {echo "<h2>$start_date</h2>";$day=$start_date;} else {}  //This is used to display the event start date. If it's different, display it ?> 
	<h3><a href="<?php echo the_permalink()?>"><?php echo get_the_title()?> </h3></a>
	<p style="margin:5px 0 0;"><?php if ($all_day == 'on') {echo "All day event";} else {if ($start_date == $end_date ) {echo "<strong>$start_time</strong> to <strong>$end_time</strong>";} elseif ($start_date<$end_date) { echo "$start_date";}} 
?></p>
<?php  endwhile;} else{echo"<p>No upcoming events</p>";}?>	