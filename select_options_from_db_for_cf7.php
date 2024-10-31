<?php

/*
Plugin Name: Select Options from database for Contact Form 7 
Plugin URI: https://wordpress.org/plugins/select-options-from-database-for-contact-form-7/
Description: Extend contact form 7 with new tag type : select options from database. Any column in your database can be a source to this select options element.
Version: 1.2
Author: Dynamic Plugin
Author URI: http://dynamicplugin.com/
License: GPL2
*/

/*  Copyright 2014 - 2015 Pasquale Bucci (email : paky.bucci@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


include_once dirname(__FILE__) .'/Logic/CF7_CMFuncs.php';  
/*
* Loads Scripts
*/
function cf7dfe_load_scripts() {
	
}

function cf7dfe_admin_load_scripts() {

	wp_enqueue_script( 'cf7dfe-plugin-script', plugins_url( '/js/script.js', __FILE__ ));
}
add_action( 'wp_enqueue_scripts', 'cf7dfe_load_scripts' );
add_action( 'admin_enqueue_scripts', 'cf7dfe_admin_load_scripts' );



 add_action('wp_ajax_nopriv_CF7DBRequestt', 'prefix_ajax_CF7DBRequest');
add_action('wp_ajax_CF7DBRequest', 'prefix_ajax_CF7DBRequest');
 
 function prefix_ajax_CF7DBRequest()
 {
        $CF7_CMFuncs = new CF7_CMFuncs();
		echo $CF7_CMFuncs->Manage($request);
        die();
        
 }

function load_cf7dfe_wp_admin_style() {
    wp_enqueue_style( 'cf7dfe-plugin-style', plugins_url( '/css/pakystyle.css', __FILE__ ));
}
add_action( 'admin_enqueue_scripts', 'load_cf7dfe_wp_admin_style' );


function cf7dfe_init(){
	if(function_exists('wpcf7_add_form_tag')){
		/* Shortcode handler */
        wpcf7_add_form_tag( 'dbactext', 'cf7dfe_shortcode_handler', true );
        wpcf7_add_form_tag( 'dbactext*', 'cf7dfe_shortcode_handler', true );
	
	}
    if(function_exists('wpcf7_add_shortcode')){
        /* Shortcode handler */
        wpcf7_add_shortcode( 'dbactext', 'cf7dfe_shortcode_handler', true );
        wpcf7_add_shortcode( 'dbactext*', 'cf7dfe_shortcode_handler', true );

    }
	add_filter( 'wpcf7_validate_dbactext', 'cf7dfe_validation_filter', 10, 2 );
	add_filter( 'wpcf7_validate_dbactext*', 'cf7dfe_validation_filter', 10, 2 );
}
add_action( 'plugins_loaded', 'cf7dfe_init' , 20 );

/*
* CityFieldText Shortcode
*/
function cf7dfe_shortcode_handler( $tag ) {
	
$wpcf7_contact_form = WPCF7_ContactForm::get_current();

	if ( ! is_array( $tag ) )
		//return '';

	$type = $tag['type'];
	$name = $tag['name'];
    $filter = $tag['filter'];
	$options = (array) $tag['options'];
	$values = (array) $tag['values'];

	if ( empty( $name ) )
		return '';

	$atts = '';
	$id_att = '';
	$class_att = '';
	$aria='';

	$class_att .= ' wpcf7-text';
	$tableName ='';
	$columnNameShow='';
	$columnNameValue='';
    $filter='';
	$id_att = 'autocomplete';

	if ( 'dbactext*' == $type ) {
		$class_att .= ' wpcf7-validates-as-required';
		$aria="true";
	}

	foreach ( $options as $option ) {
		if ( preg_match( '%^class:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$class_att .= ' ' . $matches[1];
		}
		
		if ( preg_match( '%^tableName:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$tableName =  $matches[1];
		}
		
		if ( preg_match( '%^columnNameShow:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$columnNameShow = $matches[1];
		}
		if ( preg_match( '%^columnNameValue:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$columnNameValue = $matches[1];
		}

        if ( preg_match( '%^filter:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
            $filter = $matches[1];
        }
	}

	if ( $id_att )
		$atts .= ' id="' . trim( $id_att ) . '"';

	if ( $class_att )
		$atts .= ' class="' . trim( $class_att ) . '"';


	if ( is_a( $wpcf7_contact_form, 'WPCF7_ContactForm' ) && $wpcf7_contact_form->is_posted() ) {
		if ( isset( $_POST['_wpcf7_mail_sent'] ) && $_POST['_wpcf7_mail_sent']['ok'] )
			$value = '';
		else
			$value = stripslashes_deep( $_POST[$name] );
	} else {
		$value = isset( $values[0] ) ? $values[0] : '';
	}
	
	$scval = do_shortcode('['.$value.']');
	if($scval != '['.$value.']') $value = $scval;
	
	$readonly = '';
	if(in_array('uneditable', $options)){
		$readonly = 'readonly="readonly"';
	}

$html = '<select aria-required="' . $aria . '" name="' . $name . '" value="' . esc_attr( $value ) . '"' . $atts . ' '. $readonly.' >';
	if($tableName!="" && $columnNameShow!="" && $columnNameValue!="")
	{
		$db=new CF7_CMFuncs();
		$columnNameShowCorrect=str_replace($tableName."-","",$columnNameShow);
		$columnNameValueCorrect=str_replace($tableName."-","",$columnNameValue);
		if($filter!='')
        {
            $query = "select ".$columnNameShowCorrect.",".$columnNameValueCorrect." from ".$tableName." where 
            ".$columnNameValueCorrect." like '%".Trim( $filter)."%'
              group by ".$columnNameShowCorrect.",".$columnNameValueCorrect;
        }
        else {
            $query = "select " . $columnNameShowCorrect . "," . $columnNameValueCorrect . " from " . $tableName . " 
            group by " . $columnNameShowCorrect . "," . $columnNameValueCorrect;
        }
		$optionValues = $db->getResults($query);

		foreach($optionValues as $optionValue)
		{
			$html.="<option value='".$optionValue[$columnNameValueCorrect]."'  > ".$optionValue[$columnNameShowCorrect]." </option>";
			}
	}
	
	$html .="</select>";
	

	$validation_error = '';
	if ( is_a( $wpcf7_contact_form, 'WPCF7_ContactForm' ) )
		$validation_error = $wpcf7_contact_form->validation_error( $name );

	$html = '<span class="wpcf7-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';

	return $html;
}

/*
* CityFieldText Validation filter
*/
function cf7dfe_validation_filter( $result, $tag ) {

	$wpcf7_contact_form = WPCF7_ContactForm::get_current();

	$type = $tag['type'];
	$name = $tag['name'];
    $filter = $tag['filter'];
	$value = isset( $_POST[$name] ) ? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) ) : '';

	if ( 'dbactext*' == $type ) {
		if ( '' == $value ) {
			$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
		}
	}

	return $result;
}


/*
* CityFieldText Tag generator
*/
add_action( 'admin_init', 'wpcf7_add_tag_generator_dbactext', 45 );

function wpcf7_add_tag_generator_dbactext() {
	if (class_exists('WPCF7_TagGenerator')) {
		$tag_generator = WPCF7_TagGenerator::get_instance();
		$tag_generator->add( 'dbactext', __( ' Select options from database  ', 'contact-form-7' ),
            'wpcf7_tg_pane_dbactext' );
	} else if (function_exists('wpcf7_add_tag_generator')) {
		wpcf7_add_tag_generator( 'dbactext', __( ' Select options from database ', 'wpcf7' ),
            'wpcf7_tg_pane_dbactext', 'wpcf7_tg_pane_dbactext' );
	}
}

function wpcf7_tg_pane_dbactext($contact_form, $args = '') {
	$args = wp_parse_args( $args, array() );
	$db = new CF7_CMFuncs();
	$tables = $db->GetTableNames();
	$columns = $db->GetColumns();
	$description = __( "Generate a form tag for select options from database.", 'contact-form-7' );
	//$desc_link = wpcf7_link( __( 'https://wordpress.org/plugins//', 'contact-form-7' ), __( 'the plugin page on WordPress.org', 'contact-form-7' ), array('target' => '_blank' ) );
?>
<div class="control-box">
	<fieldset>
		<legend><?php printf( esc_html( $description ) ); ?></legend>

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?>
                            </legend>
							<label><input type="checkbox" name="required" />
                                <?php echo esc_html( __( 'Required field', 'contact-form-7' ) ); ?>
                            </label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>">
                            <?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
					<td><input type="text" name="name" class="tg-name oneline"
                               id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
				</tr>

				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-tableName' ); ?>">
                            <?php echo esc_html( __( 'Table Name', 'contact-form-7' ) ); ?></label></th>
					<td><select name="tableNameSelected" class="tg-tableNameSelected oneline"
                                id="<?php echo esc_attr( $args['content'] . '-tableNameSelected' ); ?>"
					onchange="cpf7CopyValueAndFilter('<?php echo esc_attr( $args['content'] . '-tableNameSelected' ); ?>',
                            '<?php echo esc_attr( $args['content'] . '-tableName' ); ?>',
                            '<?php echo esc_attr( $args['content'] . '-columnNameShowSelected' ); ?>',
                            '<?php echo esc_attr( $args['content'] . '-columnNameShow' ); ?>',
                            '<?php echo esc_attr( $args['content'] . '-columnNameValueSelected' ); ?>',
                            '<?php echo esc_attr( $args['content'] . '-columnNameValue' ); ?>')" />
					<?php
						foreach ($tables as $tableDetails)
						{
							?>
							<option value="<?php echo $tableDetails["TABLE_NAME"] ;?>"
                                    selected="selected" > <?php echo $tableDetails["TABLE_NAME"] ;?> </option>
							<?php
							}
							
					?>
					<td><input type="text"  name="tableName" class="tableNamevalue oneline option" id="<?php echo
                        esc_attr( $args['content'] . '-tableName' ); ?>" style="visibility:hidden;" /></td>
					</select>
					
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-columnNameShowName' ); ?>">
                            <?php echo esc_html( __( 'Column name to show', 'contact-form-7' ) ); ?></label></th>
					<td><select name="columnNameShowSelected" class="tg-columnNameShowSelected oneline"
                                id="<?php echo esc_attr( $args['content'] . '-columnNameShowSelected' ); ?>"
					onchange="cpf7CopyValue('<?php echo esc_attr( $args['content'] . '-columnNameShowSelected' ); ?>',
                            '<?php echo esc_attr( $args['content'] . '-columnNameShow' ); ?>')" />
					<?php
						foreach ($columns as $columnDetails)
						{
							?>
							<option value="<?php echo $columnDetails["TABLE_NAME"]."-". $columnDetails["COLUMN_NAME"];?>"
                                    selected="selected" > <?php echo $columnDetails["TABLE_NAME"]."-".
                                    $columnDetails["COLUMN_NAME"];?> </option>
							<?php
							}
							
					?>
					<td><input type="text"  name="columnNameShow" class="columnNameShowvalue oneline option"
                               id="<?php echo esc_attr( $args['content'] . '-columnNameShow' ); ?>" style="visibility:hidden;" /></td>
					</select>
					
					</td>
				</tr>
				
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-columnNameValueName' ); ?>">
                            <?php echo esc_html( __( 'Column name to value', 'contact-form-7' ) ); ?></label></th>
					<td><select name="columnNameValueSelected" class="tg-columnNameValueSelected oneline"
                                id="<?php echo esc_attr( $args['content'] . '-columnNameValueSelected' ); ?>"
					onchange="cpf7CopyValue('<?php echo esc_attr( $args['content'] . '-columnNameValueSelected' ); ?>',
                            '<?php echo esc_attr( $args['content'] . '-columnNameValue' ); ?>')" />
					<?php
						foreach ($columns as $columnDetails)
						{
							?>
							<option value="<?php echo $columnDetails["TABLE_NAME"]."-". $columnDetails["COLUMN_NAME"];?>"
                                    selected="selected" > <?php echo $columnDetails["TABLE_NAME"]."-".
                                    $columnDetails["COLUMN_NAME"];?> </option>
							<?php
							}
							
					?>
					<td><input type="text"  name="columnNameValue"
                               class="columnNameValuevalue oneline option"
                               id="<?php echo esc_attr( $args['content'] . '-columnNameValue' ); ?>"
                               style="visibility:hidden;" /></td>
					</select>
					
					</td>
				</tr>
                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-filter' ); ?>">
                            <?php echo esc_html( __( 'Filter', 'contact-form-7' ) ); ?></label></th>
                    <td><input type="text" name="filter" class="filtervalue oneline option"
                               id="<?php echo esc_attr( $args['content'] . '-filter' ); ?>" />
                        <br/>   <?php echo esc_html( __( 'One word only', 'contact-form-7' ) ); ?>
                    </td>
                </tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>">
                            <?php echo esc_html( __( 'Class (optional)', 'contact-form-7' ) ); ?></label></th>
					<td><input type="text" name="class" class="classvalue oneline option"
                               id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
</div>
	<div class="insert-box">
		<input type="text" name="dbactext" class="tag code" readonly="readonly" onfocus="this.select()" />

		<div class="submitbox">
			<input type="button" class="button button-primary insert-tag"
                   value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
		</div>

		<br class="clear" />

		<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>">
                <?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, 
                you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ),
                    '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden"
                                                                                  readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
	</div>

<?php
}
