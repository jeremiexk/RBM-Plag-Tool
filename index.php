<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Plagiarism</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

    <!-- Custom styles for this template -->
    <link href="signin.css" rel="stylesheet">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link href="style.css" rel="stylesheet" type="text/css" />
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
	
<div class="wrapper"><!---Start wrapper-->
    	<div class="inner"><!--Start inner-->
        	<div class="sectionA"><!--Start SectionA-->
            	<div class="sectionA_left"><!--Start sectionA_left-->
                	<img src="img/logo.png" />
                </div><!--End sectionA_left-->
                    <div class="sectionA_right"><!--Start SectionA_right-->
                    	<ul>
                        <li><a class="active" href="#">HOME</a></li>
                        <li><a href="#">REFERENCE</a></li>
                        <li><a href="#">ABOUT</a></li>
                        <li><a href="#">CONTACTS</a></li>
                        <li><a class="ancher1" href="#">JEROME WATKINGS</a></li>
                        <li><a class="ancher2" href="#">LOG OUT</a></li>
                        </ul>
                    </div><!--End SectionA_right-->
            </div><!--End SectionA-->
            <div class="sectionB"><!--Start sectionB-->
            	<div class="sectionB_inner"><!--Start SectionB_inner-->
                	<div class="head"><!--Start HEAD-->
                    	<h3>Plagiarism checker</h3>
                    </div><!---END HEAD-->
                    <div class="sectionB_inner_inner"><!---Start--->
                    	
				<form action="process.post.php" method="post" enctype="multipart/form-data">

					<textarea class="form-control" rows="8" id="txt_TextToProcess" name="txt_TextToProcess" autofocus placeholder="Enter Your TEXT here"></textarea>
					
					<p>
						OR Upload a Document <input type="file" name="fle_text">
						&nbsp;
						
						<div class="ancher4">
				<button class="btn btn-primary" type="submit">Check Report</button>
                
                </div>
					</p>

				</form>
                    </div><!--ENd-->
                </div><!---End SectionB_inner-->
                
				
				
            </div><!--End SectionB-->
        </div><!--End inner-->
    </div><!--end wrapper-->
	

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
  </body>
</html>