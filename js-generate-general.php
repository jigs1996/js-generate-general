<?php
/* 
Plugin Name: Js Generate Journal
Author: Jignesh Sanghani
description: A plugin to create journal from multipal pdf
Version: 1.0
Author URL: https://www.github.com/jigs1996
 */
use \setasign\Fpdi;
require_once('vendor/autoload.php');
global $wpdb;
function js_journal_init(){
    $tar = wp_upload_dir( );
    wp_mkdir_p($tar['basedir'] .'/jsJournal_files');
    wp_mkdir_p($tar['basedir'] .'/journal');
    global $wpdb;   
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $table_pdfFile_logs = $wpdb->prefix.'pdfFile_logs';
    if($wpdb->get_var("show tables like '$table_pdfFile_logs'") != $table_pdfFile_logs){
		$sql = "CREATE TABLE " . $table_pdfFile_logs . " (
		id int(10) NOT NULL AUTO_INCREMENT,
        filename varchar(100),
        filepath TEXT NOT NULL,
        filesize bigint NOT NULL,
        postid mediumint(9),
        container VARCHAR(5),
        blogid mediumint(9),
		ptitle VARCHAR(200) ,
        date VARCHAR(11),
		UNIQUE KEY id (id)
		);";
		dbDelta($sql);
	}
    $table_journal_logs = $wpdb->prefix.'journal_logs';
    if($wpdb->get_var("show tables like '$table_journal_logs'") != $table_journal_logs){
		$sql = "CREATE TABLE " . $table_journal_logs . " (
		id int(10) NOT NULL AUTO_INCREMENT,
        journalname varchar(100),
        journalurl TEXT NOT NULL,
        journalsize bigint NOT NULL,
        date VARCHAR(11),
		UNIQUE KEY id (id)
		);";
		dbDelta($sql);
	}
}
 register_activation_hook( __FILE__, 'js_journal_init' );

/* 
*Add Menu Item 
*/
    /* 
    *JS journal Content Layout 
    */
    function js_layout(){
        wp_enqueue_script( 'jsJournaltable', plugins_url( 'js/table_script.js', __FILE__ ), array('jquery'));
        
        echo '<tbody>';
        echo '<div><h4>Note: Use shortcode(Given in below table) to add journal in the post/page</h4></div>';
        echo '<table id="example" class="display hover" cellspacing="0" width="100%">
            <thead >
                <tr style="text-align:left;">
                    <th></th>
                    <th>Journal Name</th>
                    <th>URL</th>
                    <th>ShortCode</th>
                    <th>Date</th>
                </tr>
            </thead>';
            /* send data in jso forate to jquery table */
            $tar = wp_upload_dir( );
            $uploadedFiles = $tar['basedir'].'/journal\/';
            global $wp_filesystem,$wpdb;
            if (empty($wp_filesystem)) {
                require_once (ABSPATH . '/wp-admin/includes/file.php');
                WP_Filesystem();
            }
            $fileData = $wp_filesystem->dirlist($uploadedFiles);
            $i=1;
            foreach($fileData as $key){
                echo'<tr>';
                $result = $wpdb->get_results("select journalname,journalurl,date from ".$wpdb->prefix."journal_logs WHERE journalname='".$key['name']."'");

                echo '<td>'.$i.'</td>';
                $i++;   
                echo '<td><a href="'.$result[0]->journalurl.'" style="text-decoration:none;">'.$result[0]->journalname.'</a></td>';
                echo '<td>'.$result[0]->journalurl.'</td>';
                echo '<td>[js-journal journal="'.$result[0]->journalname.'"]</td>';
                echo '<td>'.$result[0]->date.'</td>';
                echo'</tr>';
            }
            echo '</tbody>';
            echo '</table>';
    }
    /* 
    *JS create journal Content Layout 
    */
    function js_journal_layout(){

        $tar = wp_upload_dir( );
        $uploadedFiles = $tar['basedir'].'/jsJournal_files\/';
        global $wp_filesystem,$wpdb;
        if (empty($wp_filesystem)) {
            require_once (ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        $fileData = $wp_filesystem->dirlist($uploadedFiles);
        wp_enqueue_script( 'jsJournaltable', plugins_url( 'js/table_script.js', __FILE__ ), array('jquery'));
        wp_localize_script( 'jsJournaltable', 'jsData', 
                                                array(
                                                   'ajaxurl' => admin_url( 'admin-ajax.php' ),
                                                   'homepath' => get_home_url()
                                                ));
        /* display file table */    
        echo'<div style="margin-top:2%;">
                <input type="text" id="jname" value="">
                <button type="buttn" id="gButton">Generate journal </button>
                <div class="sp">
                    <h5>Note: Enter new name of journal without extension(.pdf) if you want</h5>
                </div>
            </div>

            <table id="example" class="display hover" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th></th>
                    <th>File Name</th>
                    <th>Page/Post Title</th>
                    <th>Date</th>
                </tr>
            </thead>';
            /* send data in jso forate to jquery table */
            $data = array();
            echo '<tbody id="checkboxes">';
            foreach($fileData as $key){
                echo'<tr>';
                $result = $wpdb->get_results("select id,filename,ptitle,date from ".$wpdb->prefix."pdfFile_logs WHERE filename='".$key['name']."'");
                echo '<td><input class="ck_row" type="checkbox" name="'.$result[0]->id.'"></td>';
                echo '<td>'.$result[0]->filename.'</td>';
                echo '<td>'.$result['0']->ptitle.'</td>';
                echo '<td>'.$result[0]->date.'</td>';
                echo'</tr>';
            }
            echo '</tbody>';          
            wp_localize_script( 'jsFiletable', 'jsAjax', 
                                array(
                                    'jsFileup' => wp_create_nonce('js-uploader'),
                                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                                    'postid' => get_the_ID(),
                                    'blogid' => get_current_blog_id()
                                ) )  ;
            echo '<tfoot>
                <tr>
                    <th></th>
                    <th>File Name</th>
                    <th>Page/Post Title</th>
                    <th>Date</th>
                </tr>
            </tfoot>
            </table>
            <hr>';

            /*  */

    }
    /* Make plugin avilibale at admin page */
    function add_js_journal(){
        //  wp_enqueue_script( 'jsJournaltable', plugins_url( 'js/table_script.js', __FILE__ ), array('jquery'));
        wp_enqueue_script( 'jsFiletable', plugins_url( 'js/script.js', __FILE__ ), array('jquery'));
            wp_enqueue_style( 'jsFiletable', 'https://cdn.datatables.net/1.10.16/css/dataTables.jqueryui.min.css');
             wp_enqueue_script('prefix_datatable','https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js'); 
        add_menu_page( 'JS Journal', 'Journals',  'manage_options', __FILE__, 'js_layout', 'dashicons-book', 76 );
        add_submenu_page( __FILE__, 'Create Journals', 'Create Journals', 'manage_options','subenu2', 'js_journal_layout' );
    }
add_action( 'admin_menu', 'add_js_journal' );



/* ******* */
// Shortcode
/* ******* */

/*
Short Code for File Upload 
*/
    function js_uploader_code(){
        // CSS
        wp_enqueue_style('prefix_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        wp_enqueue_style( 'style-uploader', plugins_url( 'css/style.css', __FILE__ ));
        // JS
        wp_enqueue_script( 'jsscript', plugins_url('js/script.js', __File__),array('jquery'));
        wp_localize_script( 'jsscript', 'jsAjax', array( 
                    'jsFileup' => wp_create_nonce('js-uploader'),
                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                    'postid' => get_the_ID(),
                    'blogid' => get_current_blog_id()
                    ) );
        wp_enqueue_script('prefix_ajaxform', 'http://malsup.github.com/jquery.form.js');
        wp_enqueue_script('prefix_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
        
        
        echo '
	<div class="text-center">	
		<div style="border: 1px solid #a1a1a1;text-align: center;width: 500px;padding:30px;margin:0px auto">
			<form  enctype="multipart/form-data" class="form-horizontal" method="post">
				<div class="preview"></div>
				 <div class="progress" style="display:none">
				  <div class="progress-bar" role="progressbar" aria-valuenow="0"
				  aria-valuemin="0" aria-valuemax="100" style="width:0%">
				    
				  </div>
				</div>
                <div class="msg"></div>
				<input type="file" name="pdfFile" class="form-control" />
                <input type="submit" name="submit" value="Upload Image" class="btn btn-primary upload-file">
			</form>
		</div>
	</div>';
    }
add_shortcode( 'js-file-uploader', 'js_uploader_code' );

/* 
*shorcode for display journal in post 
*/
function js_journal_code( $att ){
    $fileName = shortcode_atts( 
                    array(
                        'journal' => ''
                    ), $att, 'js-journal' );
                    global $wpdb;
    $result = $wpdb->get_results('select journalurl from '.$wpdb->prefix.'journal_logs WHERE journalname="'.$att['journal'].'"');
    $html = '<div>
                <a href="'.$result[0]->journalurl.'">'.$att['journal'].'</a>
            </div>';
    return $html;
}

add_shortcode( 'js-journal', 'js_journal_code' );


/* **** */
//Ajax requsts
/* **** */

/* 
*Ajax for uploading file 
*/
function uploadFile(){    
    // $file = $_FILES["pdfFile"];
    $target_dir = wp_upload_dir();
    $target_file = $target_dir['basedir'] .'/jsJournal_files\/'. basename($_FILES["pdfFile"]["name"]);
    $nonce = 'js-uploader';
    /*
    * Check wether file comming from valid user
    *(Check the nonce fr that verification)
    */
    require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
    require_once( ABSPATH . 'wp-admin' . '/includes/file.php' );
    require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );
    // $plugin_file = plugin_basename( __FILE__ );
     if( isset($_POST['jsNonce']) && wp_verify_nonce( $_POST['jsNonce'], $nonce )){
        if(isset($_FILES['pdfFile'])){
            if(!empty($_FILES['pdfFile']['name'])){

                $type = array('application/pdf');
                $fileT = wp_check_filetype(basename($_FILES['pdfFile']['name']));
                $upload_type = $fileT['type'];

                if(in_array($upload_type, $type)){
                    if($flag = move_uploaded_file($_FILES['pdfFile']['tmp_name'], $target_file)){
                        $postid = $_POST['postid'];
                        $blogid = $_POST['blogid'];
                        global $wpdb;
                        $wpdb->insert($wpdb->prefix.'pdfFile_logs',  
                                            array(
                                                'filename' => $_FILES['pdfFile']['name'],
                                                'filepath' => $target_file,
                                                'filesize' => $_FILES['pdfFile']['size'],
                                                'postid' => $postid,
                                                'blogid' => $blogid,
                                                'ptitle' => get_the_title($postid),
                                                'date' => date( 'd-m-Y')
                                            )); 
                    echo "1";
                    }
                        
                    else
                        echo "0";
                } else {
                    wp_die("The file type that you've uploaded is not a PDF.");
                }
            }
        }
    } 
    /* Entry in db as log */
     die;
}
add_action( 'wp_ajax_upload_file', 'uploadFile');
add_action( 'wp_ajax_nopriv_upload_file', 'uploadFile');

/* 
*ajax request for creating journal
*/
function journalFile(){
    try{
        echo "hello";
    $selectedFiles = $_POST['array'];
    
    $target_dir =  wp_upload_dir();
    $target_file =  $target_dir['basedir'] .'/jsJournal_files' ;
    $files = array();
    global $wpdb;
    foreach ($selectedFiles as $fname){
        $result = $wpdb->get_results("select filename from ".$wpdb->prefix."pdfFile_logs WHERE id=".$fname);
        array_push($files,$target_file.'/'.$result[0]->filename);
    }
    print_r($files);
    // initiate FPDI
    $pdf = new Fpdi\Fpdi();
    // iterate through the files
    foreach ($files AS $file) {
    // get the page count
    $pageCount = $pdf->setSourceFile($file);
    // iterate through all pages
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        // import a page
        $templateId = $pdf->importPage($pageNo);
        // get the size of the imported page
        $size = $pdf->getTemplateSize($templateId);
        // add a page with the same orientation and size
        $pdf->AddPage($size['orientation'], $size);

        // use the imported page
        $pdf->useTemplate($templateId);

        $pdf->SetFont('Helvetica');
        $pdf->SetXY(5, 5);
        $pdf->Write(8, 'Journal_'.date('Y-m-d'));
    }
}
    // Output the new PDF
    $jName = $_POST['journalName'];
    print_r($jName);
    if($jName == ".pdf"){
        $jName ='journal'.time().'.pdf';
    }
    // $journalName = 'journal'.time().'.pdf';
    // store log in db
    $wpdb->insert($wpdb->prefix.'journal_logs', array(
        'journalname' => $jName,
        'journalurl' => $target_dir['baseurl'].'/journal/'.$jName, 
        'journalsize' => filesize($target_dir['basedir'].'/journal\/'.$jName),
        'date' => date('Y-m-d')
    )) or die("error");
    $pdf->Output($target_dir['basedir'].'/journal\/'.$jName,'F');   
    
    }catch(Exception $e){
        print_r($e);
    }
    die;
}
add_action( 'wp_ajax_journalFiles', 'journalFile');
add_action( 'wp_ajax_nopriv_journalFiles', 'journalFile');