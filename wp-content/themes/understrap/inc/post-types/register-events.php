<?php
 
$events = new CPT(array(
    'post_type_name' => 'events',
    'singular' => __('Events', 'bootstrapwp'),
    'plural' => __('Events', 'bootstrapwp'),
    'slug' => 'events'
),
	array(
    'supports' => array('title', 'editor', 'thumbnail', 'comments'),
    'menu_icon' => 'dashicons-portfolio'
));

$events->register_taxonomy(array(
    'taxonomy_name' => 'events_tags',
    'singular' => __('Events Tag', 'bootstrapwp'),
    'plural' => __('Events Tags', 'bootstrapwp'),
    'slug' => 'event-tag'
));