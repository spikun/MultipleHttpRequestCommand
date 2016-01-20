<?php

  namespace Kcio\Util;

  /**
   * MultipleHttpResquestCommand allows to encapsulate
   * many asyncronous requests to be executed at once.
   *
   * @author Cassio
   */
  class MultipleHttpResquestCommand {

    private $requests;
	private $maxRequestPerExecution;

    public function __construct() {
      $this->requests = array();
	  $this->maxRequestPerExecution = 100;
    }

    /**
     * Add a POST request to be processed in the execute() method
     * @param any key is required for the execution result; NB! it must be unique
     * @param string url
     * @param array POST parameters
     */
    public function addPostRequest($key, $url, $params) {

      $postData = http_build_query($params);

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_POST, count($postData));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

      $this->requests[$key] = $ch;
    }

    public function setMaxRequestsPerExecution($maxRequestPerExecution) {
      $this->maxRequestPerExecution = $maxRequestPerExecution;
	}
	
    /**
     * Executes all stored requests
     * The function will executes max $maxRequestPerExecution per time to avoid timeout
     * @result array requests results
     */
    public function execute() {
      $result = array();
      $requestLists = array_chunk($this->requests, $maxRequestPerExecution, true);
      foreach ($requestLists as $requests) {
        $mh = curl_multi_init();
        foreach ($requests as $key => $ch) {
          curl_multi_add_handle($mh, $ch);
        }
        $partial = $this->executeMultiCurl($mh, $requests);
        $result = array_merge($result, $partial);
        curl_multi_close($mh);
        set_time_limit(20);
      }

      return $result;
    }

    private function executeMultiCurl($mh, $list) {
      $running = null;
      do {
        curl_multi_exec($mh, $running);
      } while ($running > 0);

      return $this->processMultiCurlResult($mh, $list);
    }

    private function processMultiCurlResult($mh, $list) {
      $result = array();
      foreach ($list as $key => $ch) {
        $result[$key] = curl_multi_getcontent($ch);
        curl_multi_remove_handle($mh, $ch);
      }
      return $result;
    }

  }
  