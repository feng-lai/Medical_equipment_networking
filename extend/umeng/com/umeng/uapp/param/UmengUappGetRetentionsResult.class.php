<?php

include_once ('../extend/umeng/com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('../extend/umeng/com/alibaba/openapi/client/entity/ByteArray.class.php');
include_once ('../extend/umeng/com/umeng/uapp/param/UmengUappRetentionInfo.class.php');

class UmengUappGetRetentionsResult {

        	
    private $retentionInfo;
    
        /**
    * @return 
    */
        public function getRetentionInfo() {
        return $this->retentionInfo;
    }
    
    /**
     * 设置     
     * @param array include @see UmengUappRetentionInfo[] $retentionInfo     
          
     * 此参数必填     */
    public function setRetentionInfo(UmengUappRetentionInfo $retentionInfo) {
        $this->retentionInfo = $retentionInfo;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "retentionInfo", $this->stdResult )) {
    			$retentionInfoResult=$this->stdResult->{"retentionInfo"};
    				$object = json_decode ( json_encode ( $retentionInfoResult ), true );
					$this->retentionInfo = array ();
					for($i = 0; $i < count ( $object ); $i ++) {
						$arrayobject = new ArrayObject ( $object [$i] );
						$UmengUappRetentionInfoResult=new UmengUappRetentionInfo();
						$UmengUappRetentionInfoResult->setArrayResult($arrayobject );
						$this->retentionInfo [$i] = $UmengUappRetentionInfoResult;
					}
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    		if (array_key_exists ( "retentionInfo", $this->arrayResult )) {
    		$retentionInfoResult=$arrayResult['retentionInfo'];
    			$this->retentionInfo = new UmengUappRetentionInfo();
    			$this->retentionInfo->setStdResult ( $retentionInfoResult);
    		}
    		    	    		}

}
?>