<?php
// this class has been developed to convert various document format to simple text files so the 
// RBM System Plagiarism detection tool can analyse and process the user uploaded files with ease.
  class DocxConversion{
    private $filename;

    public function __construct($filePath) {
        $this->filename = $filePath;
    }

    // the private function read doc will analyse the files that has the extention of .doc 
    // using regualr expression to 
    private function read_doc() {
        $fileHandle = fopen($this->filename, "r");
        $line = @fread($fileHandle, filesize($this->filename));   //reading the contents of the uploaded file
        $lines = explode(chr(0x0D),$line); // this funtion splits the uploaded file line by line (string by strings) 
                                          //  and returns an array of strings 
        $outtext = ""; // this variable will hold the values of each string array 
        
        // perform a loop that will iterate through string arrays 
        foreach($lines as $thisline)
          {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== FALSE)||(strlen($thisline)==0))
              {
              } else {
                $outtext .= $thisline." ";
              }
          }
          
          // using Regular expression to remove anything thats not a text character 
          // ie images and anything else that does't follow this pattern will get removed
         $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
        return $outtext; // once the array has been cleared from unwanted characters we 
                         // return the value of the variable outtext and restart the loop
                         //with next variable 
    }

    
    // this function deals with the user uploading word document that has the extention of .docx
    private function read_docx(){

        $striped_content = '';
        $content = '';

        // because .Docx is a ziped format we need to use the zip_open php function to view the file
        $zip = zip_open($this->filename);

        //running an integrity check on the file
        if (!$zip || is_numeric($zip)) return false;

        while ($zip_entry = zip_read($zip)) {

            // if the document is not of word type we break out of the loop
            if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

            if (zip_entry_name($zip_entry) != "word/document.xml") continue;

            //retrieving the contents of the file 
            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }// end while

        zip_close($zip);

        // removing the images or other unwnated characters found the document
        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);

        return $striped_content;
    }

 /************************excel sheet************************************/

function xlsx_to_text($input_file){
    $xml_filename = "xl/sharedStrings.xml"; //content file name
    $zip_handle = new ZipArchive;
    $output_text = "";
    if(true === $zip_handle->open($input_file)){
        if(($xml_index = $zip_handle->locateName($xml_filename)) !== false){
            $xml_datas = $zip_handle->getFromIndex($xml_index);
            // using the php DOMDocument to load
            $xml_handle = DOMDocument::loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            $output_text = strip_tags($xml_handle->saveXML()); // removing the xml tags of the document
        }else{
            $output_text .="";
        }
        $zip_handle->close();
    }else{
    $output_text .="";
    }
    return $output_text;
}

/*************************power point files*****************************/
function pptx_to_text($input_file){
    $zip_handle = new ZipArchive;
    $output_text = "";
    if(true === $zip_handle->open($input_file)){
        $slide_number = 1; //loop through slide files
        while(($xml_index = $zip_handle->locateName("ppt/slides/slide".$slide_number.".xml")) !== false){
            $xml_datas = $zip_handle->getFromIndex($xml_index);
            $xml_handle = DOMDocument::loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            $output_text .= strip_tags($xml_handle->saveXML());
            $slide_number++;
        }
        if($slide_number == 1){
            $output_text .="";
        }
        $zip_handle->close();
    }else{
    $output_text .="";
    }
    return $output_text;
}


    public function convertToText() { // this function will convert and read the uploaded files 
                                      // only certain file format are accepted 
        if(isset($this->filename) && !file_exists($this->filename)) {
            return "File Not exists";
        }

        $fileArray = pathinfo($this->filename);
        $file_ext  = $fileArray['extension'];
        // acceptend file format
        if($file_ext == "doc" || $file_ext == "docx" || $file_ext == "xlsx" || $file_ext == "pptx")
        {
            if($file_ext == "doc") { // if uploaded file is of type doc
                return $this->read_doc(); // call the function that will read the doc files
            } elseif($file_ext == "docx") { // if uploaded file is of type docx 
                return $this->read_docx(); // call the function that will read the doc files 
            } elseif($file_ext == "xlsx") { // if uploaded file is of type xlsx 
                return $this->xlsx_to_text(); // calling the function that will convert excell file to text file
            }elseif($file_ext == "pptx") {    // if uploaded file is of type pptx
                return $this->pptx_to_text(); // calling the fucntion convert power point to text file
            }
        } else {
            return "Invalid File Type"; // if other type of file has been uploade to throw and exception
        }
    }

}