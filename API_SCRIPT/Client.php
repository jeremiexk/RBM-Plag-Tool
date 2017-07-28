<?php

//namespace Plagtool\Rest\V2;

/**
 * Rest client for plagrtracker
 * 
 * 21.03.2013 - added method batch and class Response
 *
 * @author miroslav.kosinskii
 */
class Client
{    
    const MSG_WRONG_LOGIN_OR_PASSWORD = 'Wrong login or password';
    const MSG_URL_NOT_FOUND = 'URL not found';
    const MSG_HTTPS_HAVE_TO_BE_USED = 'https have to be used';
    const MSG_ADD_NGRAMS_ERROR = 'Add ngrams error';
    const MSG_ADD_TEXT_ERROR = 'Add text error';
    const MSG_TEXT_IS_EMPTY = 'Text is empty';
    const MSG_TEXT_NOT_FOUND = 'Text not found';
    const MSG_TEXT_IS_NOT_CHECKED_YET = 'Text is not checked yet';
    const MSG_LIMIT_OF_CHECKS_AT_THE_SAME_TIME_IS_EXCEEDED = 'Limit of checks at the same time is exceeded';
    const MSG_WRONG_SUBSCRIPTION_TYPE = 'Wrong subscription type';
    const MSG_LIMIT_OF_CHECKS_IS_EXCEEDED = 'Limit of checks is exceeded';
    const MSG_GETTING_DATA_FROM_URL_ERROR = 'Getting data from url error';
    const MSG_GETTING_DATA_FROM_FILE_ERROR = 'Getting text from file error';
    const MSG_WRONG_FILE_EXTENSION = 'Sorry, you can only upload .doc, .csv, .html, .ods, .odt, .pdf, .ppt, .rtf, .txt, .xls, .docx, .xlsx files';
    const MSG_FILE_IS_TOO_BIG = 'Maximum size of uploading document is 10 MB.';
    
    const CURL_TIMEOUT = 120; //120 sec
    
    const SAMPLE_TEXT = 'The Simpsons is an American animated sitcom created by Matt Groening for the Fox Broadcasting Company. The series is a satirical parody of a middle class American lifestyle epitomized by its family of the same name, which consists of Homer, Marge, Bart, Lisa and Maggie. The show is set in the fictional town of Springfield and parodies American culture, society and many aspects of the human condition. Since its debut on December 17, 1989, the show has broadcast 500 episodes and the twenty-third season started airing on September 25, 2011. The Simpsons is the longest-running American sitcom, the longest-running American animated program, and in 2009 it surpassed Gunsmoke as the longest-running American primetime, scripted television series. The Simpsons Movie, a feature-length film, was released in theaters worldwide on July 26 and July 27, 2007, and grossed over $527 million.';
    
    const COMPLETED_PERCENT = 100;
    
    const MAX_UPLOADED_FILE_SIZE = 10485760; //10 mb
    
    private static $_allowed_extensions = array('doc', 'csv', 'html', 'ods', 'odt', 'pdf', 'ppt', 'rtf', 'txt', 'xls', 'docx', 'xlsx');
    
    private $_login = null;
    private $_password = null;
    private $_response = null;
    private $_api_domain = '';
    
    /**
     *
     * @param string $login
     * @param string $password 
     */
    public function __construct($login, $password, $api_domain = 'server.plagtracker.com')
    {
        $this->_login = $login;
        $this->_password = $password;
        $this->_api_domain = $api_domain;
    }
    
    /**
     *  Method runs methods in batch
     * 
     *  Example of input parameter:
     *  $methods = array(
     *      'some key (not required)' => array('addTextForChecking' => array('text example 1')),
     *      'some key (not required)' => array('addTextForChecking' => array('text example 2')),
     *      'some key (not required)' => array('getResult' => array('some hash')),
     *      'some key (not required)' => array('getResult' => array('some hash')),
     *      'some key (not required)' => array('getPlagiarismPercent' => array('some hash', 'some domain')),
     *  );
     * 
     * 
     *  Method creates array of results
     * 
     *  Example:
     *  $result = array(
     *      'some key (not required)' => array with response for method,
     *      'some key (not required)' => array with response for method,
     *      'some key (not required)' => array with response for method,
     *      'some key (not required)' => array with response for method,
     *      'some key (not required)' => array with response for method,
     *  )
     * 
     * @param array $methods
     * @return array
     */
    public function batch($methods)
    {
        $url = "https://".$this->_api_domain."/rest/v2.batch";  
        $post_data = http_build_query(array('methods' => $methods));
        
        $response = $this->_execHttpRequest($url, $post_data);
        
        $this->_response = $this->_makeResponse($response, true); 
                
        return $this->_response;
    }
    
    /**
     * Add text for checking
     * 
     * @param string $text
     * @param TextSettings|null $custom_text_settings
     * @return stdClass 
     */
    public function addTextForChecking($text, $custom_text_settings = null)
    {
        $url = "https://".$this->_api_domain."/rest/v2.add-text-for-checking";  
        $post_data = 'text='.urlencode($text);
        $post_data = $this->_addTextSettingsToPost($post_data, $custom_text_settings);
        
        $response = $this->_execHttpRequest($url, $post_data);
        $this->_response = $this->_makeResponse($response); 
                
        return $this->_response;
    }
    
    /**
     * Add url for checking
     * 
     * @param string $url_for_checking
     * @param TextSettings|null $custom_text_settings
     * @return stdClass 
     */
    public function addUrlForChecking($url_for_checking, $custom_text_settings = null)
    {
        $url = "https://".$this->_api_domain."/rest/v2.add-url-for-checking";  
        $post_data = 'url='.urlencode($url_for_checking);
        $post_data = $this->_addTextSettingsToPost($post_data, $custom_text_settings);
        
        $response = $this->_execHttpRequest($url, $post_data);
        $this->_response = $this->_makeResponse($response); 
        
        return $this->_response;
    }
    
    /**
     * Add file for checking
     * 
     * @param string $file_path
     * @param TextSettings|null $custom_text_settings
     * @return stdClass 
     */
    public function addFileForChecking($file_path, $custom_text_settings = null)
    {
        if (!in_array(pathinfo($file_path, PATHINFO_EXTENSION), self::$_allowed_extensions))
        {
            throw new \Exception(self::MSG_WRONG_FILE_EXTENSION);
        }
        
        $file_data = file_get_contents($file_path);
        
        if (self::MAX_UPLOADED_FILE_SIZE < strlen($file_data))
        {
            throw new \Exception(self::MSG_FILE_IS_TOO_BIG);
        }
        
        $url = "https://".$this->_api_domain."/rest/v2.add-file-for-checking";  
        $post_data = 'file_name='.urlencode(basename($file_path)).'&file_data='.urlencode(base64_encode($file_data));
        $post_data = $this->_addTextSettingsToPost($post_data, $custom_text_settings);
        
        $response = $this->_execHttpRequest($url, $post_data);
        $this->_response = $this->_makeResponse($response); 
        
        return $this->_response;
    }
    
    /**
     * @param string $post_data
     * @param TextSettings|null $custom_text_settings
     * @return string
     */
    private function _addTextSettingsToPost($post_data, $custom_text_settings)
    {
        if($custom_text_settings instanceof TextSettings)
        {
            $post_data .= '&custom_text_settings='.urlencode(json_encode($custom_text_settings));
        }
        
        return $post_data;
    }
    
    /**
     * Returns completed percentage of text checking
     * 
     * @param string $hash
     * @return stdClass 
     */
    public function getTextStatus($hash)
    {
        $url = "https://".$this->_api_domain."/rest/v2.get-text-status?hash=".$hash;  

        $response = $this->_execHttpRequest($url);
        $this->_response = $this->_makeResponse($response); 
        
        return $this->_response;
    }
    
    /**
     * Returns checking result
     * array of ngrams and sources for ngrams
     * 
     * @param string $hash
     * @return stdClass 
     */
    public function getResult($hash)
    {
        $url = "https://".$this->_api_domain."/rest/v2.get-result?hash=".$hash;  
        
        $response = $this->_execHttpRequest($url);
        $this->_response = $this->_makeResponse($response); 
        
        return $this->_response;
    }
    
    /**
     * Returns checking result with quotes
     * array of ngrams and sources with quotes for ngrams
     * 
     * @param string $hash
     * @return stdClass 
     */
    public function getResultWithQuotes($hash)
    {
        $url = "https://".$this->_api_domain."/rest/v2.get-result-with-quotes?hash=".$hash;  
        
        $response = $this->_execHttpRequest($url);
        $this->_response = $this->_makeResponse($response); 
        
        return $this->_response;
    }
    
    /**
     * Returns plagiarism percent
     * 
     * @param string $hash
     * @param string $filter_host
     * @return stdClass 
     */
    public function getPlagiarismPercent($hash, $filter_host='')
    {
        $url = "https://".$this->_api_domain."/rest/v2.get-plagiarism-percent?hash=".$hash."&filter_host=".$filter_host;  
        
        $response = $this->_execHttpRequest($url);
        $this->_response = $this->_makeResponse($response); 
        
        return $this->_response;
    }
    
    /**
     * Returns text
     * 
     * @param string $hash
     * @return stdClass 
     */
    public function getText($hash)
    {
        $url = "https://".$this->_api_domain."/rest/v2.get-text?hash=".$hash;
        
        $response = $this->_execHttpRequest($url);
        $this->_response = $this->_makeResponse($response); 
        
        return $this->_response;
    }
    /**
     * Return count of api checks left
     * 
     * @return stdClass
     */
    public function checkBalance()
    {
        $url = "https://" . $this->_api_domain . "/rest/v2.get-check-left";
        $response = $this->_execHttpRequest($url);
        $this->_response = $this->_makeResponse($response); 
        
        return $this->_response;
    }
    /**
     *
     * @param string $url
     * @param string $post_data
     * @return \stdClass 
     */
    private function _execHttpRequest($url, $post_data = null)
    {
        $ch = curl_init();  
        
        if($post_data)
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->_login}:{$this->_password}");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($result);
    }
    
    /**
     *
     * @param \stdClass $raw_response
     * @param bool $recursive
     * @return mixed
     */
    private function _makeResponse($raw_response, $recursive = false)
    {
        $result = null;
        
        if($raw_response)
        {
            if($recursive)
            {
                $raw_response->data = (array)$raw_response->data;
                foreach($raw_response->data as $key => $sub_raw_response)
                {
                    $raw_response->data[$key] = $this->_makeResponse($sub_raw_response);
                }
            }

            $result = new Response ($raw_response->status, $raw_response->message, $raw_response->data);
        }
        
        return $result;
    }
    
    /**
     * Returns last response
     * 
     * @return stdClass 
     */
    public function getResponse()
    {
        return $this->_response;
    }
}


/**
 * Response for REST client
 * 
 */
class Response
{
    const OK = 200;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const INTERNAL_SERVER_ERROR = 500;
    const SERVICE_UNAVAILABLE = 503;
    
    
    private $_status;
    private $_message;
    private $_data;
    
    /**
     *
     * @param int $status
     * @param string $message
     * @param mixed $data 
     */
    public function __construct($status, $message, $data)
    {
        $this->_status = $status;
        $this->_message = $message;
        $this->_data = $data;
    }
    
    /**
     * 
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * 
     * 
     * @return mixed 
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * 
     * 
     * @return int 
     */
    public function getStatus()
    {
        return $this->_status;
    }
    
    /**
     * Returns true if last response was successful
     * 
     * @return bool 
     */
    public function isSuccessfully()
    {
        return $this->_status == self::OK;
    }
    
    /**
     * Returns true if happened temporary error
     * 
     * @return bool 
     */
    public function isTemporaryError()
    {
        return $this->_status == self::SERVICE_UNAVAILABLE;
    }
}

/**
 * Settings for text check
 */
class TextSettings
{
    public $ignore_quotation = false;
    public $search_on_site = null;
    public $internal_check = false;
}
