<?php

error_reporting(E_ALL);

require_once ( "API_SCRIPT/Client.php" ) ;
$client = new Client('jeremie50@hotmail.co.uk','Yh&_7s');
$response = null ;
$str_HASH = "" ;

$string_TEXT_TO_BE_PROCESSED = "" ;

if ( ! empty( $_POST["txt_TextToProcess"] )) {
	$string_TEXT_TO_BE_PROCESSED = $_POST["txt_TextToProcess"] ;
} elseif ( ! empty ($_FILES["fle_text"]["name"]) ) {
	$file_name_to_save = "collections/".date('Y-M-d_H-i-s').$_FILES["fle_text"]["name"] ;
    move_uploaded_file ( $_FILES["fle_text"]["tmp_name"] , $file_name_to_save ) or die("Cannot Upload file in specified folder.");
	if (stripos($_FILES["fle_text"]["name"] , ".docx")) {
		require_once ( "class.doc_convert.php" ) ;
		$docObj = new DocxConversion($file_name_to_save);
		$string_TEXT_TO_BE_PROCESSED = $docText= $docObj->convertToText();
	} elseif( stripos($_FILES["fle_text"]["name"] , ".txt") ) {
		$string_TEXT_TO_BE_PROCESSED = file_get_contents ($file_name_to_save) ;
	}
	
    
	/*
		$response = $client->addFileForChecking($file_name_to_save);
		$str_HASH = $response->getData() ;
		$result_Data = $client->getResult($str_HASH) ;
	*/
} else {
	header("location:index.php") ;
	exit ( ) ;
}

if ( empty ( $string_TEXT_TO_BE_PROCESSED ) ) 
		exit ("No TEXT found to be processed.") ;
	
$response = $client->addTextForChecking($string_TEXT_TO_BE_PROCESSED);
$str_HASH = $response->getData() ;
$result_Data = $client->getResult($str_HASH) ;


?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/favicon.ico">

    <title>Plagirism</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	<style>
		.text_found {
			cursor:pointer;
		}
		.text_found:hover {
			background:rgb(249, 51, 51);
		}
	</style>
  </head>

  <body>

    <div class="container">
		
		<div class="row">
			<div class="col-md-8">
				<h1>RBM System Plagiarism check Report</h1>
				<div class="well well-lg" id="txt_User">
					<?php echo $string_TEXT_TO_BE_PROCESSED; ?>
				</div>
			</div>
			<div class="col-md-4">
				<h3>Plagiarism Check Completed</h3>
				<h4>Contents appears in <span class="label label-default" id="spnTotalLinks"></span></h4>
				
				<div id="progress" style="background: url('loading.gif') no-repeat center top;padding: 40px 0 0 0;text-align: center; text-align:center;">
					<h1>0% Completed</h1>
				</div>
				<div id="result" style="display:none">
					<h3>Sources Found</h3>
					<p><em>Click on the link below to see all sentences from this source</em></p>
					
					<div class="list-group" id="div_links"></div>
					
					<a href="javascript:void(0);" onclick="return show_all_links();">View All Sources</a>
					
					
				</div>
				
			</div>
		</div>
      
    </div> <!-- /container -->


    
	<script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
	<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<script>
		var timer = null ;
		var json_result = null ;
		$(document).ready(function() {
			timer = setInterval(function () {getStatus()}, 2000);
			
		});
		
		function updateProgress(percentage){
			if(percentage >= 100) {
				$('#progress').remove();
				return ;
			}
			$('#progress>h1').html(percentage+'% Completed');
		}
		
		function getStatus() {
			var links_count = 0 ;
			$.getJSON( "ajax.result.php", { "hash" : "<?php echo $str_HASH ?>"} , function( data ) {
				json_result = data ;
				updateProgress(parseInt(json_result.header.percent));
				if (parseInt(json_result.header.percent) >= 100) {
					
					$("#result").show();
					window.clearInterval(timer) ;
					$.each( json_result, function( key, val ) {
						if ( key != "header" ) {
							str = $("#txt_User").html() ;
							index_pos = str.search(val.text) ;
							if ( index_pos > -1 ) {
								n = str.replace(val.text , "<span class='text_found' id=text"+key+">"+val.text+"</span>") ;
								$("#txt_User").html(n) ;
							}
							links_count += parseInt ( val.links.length ) ;
						}
					} ) ;
				}
				$("#spnTotalLinks").text(links_count+' sources');
				$("#txt_User>span").hover(function() {
					$("#div_links>*").remove();
					var id = $(this).attr("id") ;
					id = id.replace("text","");
					$.each( json_result, function( k, v ) {
						if (k==id){
							$.each ( v.links , function(lk, lv) {
								link_name = lv.substring(lv.indexOf(".")+1,30) ;
								$("#div_links").append('<a href="'+lv+'" target="_blank" class="list-group-item">'+link_name+'<span class="glyphicon glyphicon-new-window pull-right"></span></a>');
							} ) ;
						}
					});
				}) ;
			} ) ;
		}
		
		function show_all_links() {
			$("#div_links>*").remove();
			$.each( json_result, function( k, v ) {
				if (k!="header") {
					$.each ( v.links , function(lk, lv) {
						link_name = lv.substring(lv.indexOf(".")+1,30) ;
						$("#div_links").append('<a href="'+lv+'" target="_blank" class="list-group-item">'+link_name+'<span class="glyphicon glyphicon-new-window pull-right"></span></a>');
					} ) ;
				}
			});
		}
		
	</script>
	
  </body>
</html>