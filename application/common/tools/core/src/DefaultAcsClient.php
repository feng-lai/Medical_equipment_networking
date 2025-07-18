<?php namespace app\common\tools\core\src;

/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
use app\common\tools\Core\src\Exceptions\ClientException;
use app\common\tools\Core\src\Exceptions\ServerException;
use app\common\tools\Core\src\Profile\DefaultProfile;
use app\common\tools\core\src\Http\HttpHelper;
use app\common\tools\core\src\Regions\EndpointProvider;

class DefaultAcsClient implements IAcsClient
{

    /**
     * @var DefaultProfile
     */
    public $iClientProfile;


    public function  __construct($iClientProfile)
    {
        $this->iClientProfile = $iClientProfile;
    }


    public function getAcsResponse($request, $iSigner = null, $credential = null, $autoRetry = true, $maxRetryNumber = 3)
    {
        $httpResponse = $this->doAction($request, $iSigner, $credential, $autoRetry, $maxRetryNumber);
        /**
         * @var AcsRequest $request
         */
        $respObject = $this->parseAcsResponse($httpResponse->getBody(), $request->getAcceptFormat());

        if (false == $httpResponse->isSuccess()) {
            $this->buildApiException($respObject, $httpResponse->getStatus());
        }

        return $respObject;
    }


    public function doAction($request, $iSigner = null, $credential = null, $autoRetry = true, $maxRetryNumber = 3)
    {
        /**
         * @var AcsRequest $request
         */
        if (null == $this->iClientProfile && ( null == $iSigner || null == $credential || null == $request->getRegionId() || null == $request->getAcceptFormat() )) {
            throw new ClientException("No active profile found.", "SDK.InvalidProfile");
        }
        if (null == $iSigner) {

            $iSigner = $this->iClientProfile->getSigner();
        }
        if (null == $credential) {
            $credential = $this->iClientProfile->getCredential();
        }
        $request = $this->prepareRequest($request);
//        $domain  = EndpointProvider::findProductDomain($request->getRegionId(), $request->getProduct());
        $domain="sts.aliyuncs.com";
        if (null == $domain) {
            throw new ClientException("Can not find endpoint to access.", "SDK.InvalidRegionId");
        }
        $requestUrl   = $request->composeUrl($iSigner, $credential, $domain);
        $httpResponse = HttpHelper::curl($requestUrl, $request->getMethod(), $request->getHeaders());
//        var_dump($httpResponse);die;
        $retryTimes   = 1;
        while (500 <= $httpResponse->getStatus() && $autoRetry && $retryTimes < $maxRetryNumber) {
            $requestUrl   = $request->composeUrl($iSigner, $credential, $domain);
            $httpResponse = HttpHelper::curl($requestUrl, null, $request->getHeaders());
            $retryTimes++;
        }

        return $httpResponse;
    }


    private function prepareRequest($request)
    {
        /**
         * @var AcsRequest $request
         */
        if (null == $request->getRegionId()) {
            $request->setRegionId($this->iClientProfile->getRegionId());
        }
        if (null == $request->getAcceptFormat()) {
            $request->setAcceptFormat($this->iClientProfile->getFormat());
        }
        if (null == $request->getMethod()) {
            $request->setMethod("GET");
        }

        return $request;
    }


    private function buildApiException($respObject, $httpStatus)
    {
        if (500 <= $httpStatus) {
            throw new ServerException($respObject->Message, $respObject->Code);
        } else {
            throw new ClientException($respObject->Message, $respObject->Code);
        }
    }


    private function parseAcsResponse($body, $format)
    {
        $respObject = null;

        if ("JSON" == $format) {
            $respObject = json_decode($body);
        } else {
            if ("XML" == $format) {
                $respObject = @simplexml_load_string($body);
            } else {
                if ("RAW" == $format) {
                    $respObject = $body;
                }
            }
        }

        return $respObject;
    }
}
