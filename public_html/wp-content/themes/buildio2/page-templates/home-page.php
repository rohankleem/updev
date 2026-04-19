<?php
/*
Template Name: Home Page
Template Post Type: page
*/

$has_hero = true;

?>


<?php get_header(); ?>

<?php include get_template_directory() . '/inc/hero-home.php'; ?>

<?php include get_template_directory() . '/inc/home/outcomes.php'; ?>

<?php include get_template_directory() . '/inc/home/skillsets.php'; ?>

<?php include get_template_directory() . '/inc/home/approaches8.php'; ?>

<?php include get_template_directory() . '/inc/home/what.php'; ?>

<?php include get_template_directory() . '/inc/home/brands2.php'; ?>

<?php include get_template_directory() . '/inc/blog-snippets.php'; ?>

<?php //include get_template_directory() . '/inc/home/approaches4.php'; ?>

<?php //include get_template_directory() . '/inc/home/cases.php'; ?>


<?php get_footer(); ?>