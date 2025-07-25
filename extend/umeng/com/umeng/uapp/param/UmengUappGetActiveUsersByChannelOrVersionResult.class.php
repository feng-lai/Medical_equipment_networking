<?php

include_once ('../extend/umeng/com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('../extend/umeng/com/alibaba/openapi/client/entity/ByteArray.class.php');
include_once ('../extend/umeng/com/umeng/uapp/param/UmengUappCountData.class.php');

class UmengUappGetActiveUsersByChannelOrVersionResult {

        	
    private $activeUserInfo;
    
        /**
    * @return 
    */
        public function getActiveUserInfo() {
        return $this->activeUserInfo;
    }
    
    /**
     * 设置     
     * @param array include @see UmengUappCountData[] $activeUserInfo     
          
     * 此参数必填     */
    public function setActiveUserInfo(UmengUappCountData $activeUserInfo) {
        $this->activeUserInfo = $activeUserInfo;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "activeUserInfo", $this->stdResult )) {
    			$activeUserInfoResult=$this->stdResult->{"activeUserInfo"};
    				$object = json_decode ( json_encode ( $activeUserInfoResult ), true );
					$this->activeUserInfo = array ();
					for($i = 0; $i < count ( $object ); $i ++) {
						$arrayobject = new ArrayObject ( $object [$i] );
						$UmengUappCountDataResult=new UmengUappCountData();
						$UmengUappCountDataResult->setArrayResult($arrayobject );
						$this->activeUserInfo [$i] = $UmengUappCountDataResult;
					}
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    		if (array_key_exists ( "activeUserInfo", $this->arrayResult )) {
    		$activeUserInfoResult=$arrayResult['activeUserInfo'];
    			$this->activeUserInfo = new UmengUappCountData();
    			$this->activeUserInfo->setStdResult ( $activeUserInfoResult);
    		}
    		    	    		}

}
?>