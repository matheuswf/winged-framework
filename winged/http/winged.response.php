<?php

class Response
{

    public $request = false;
    private $output = null;
    private $response_code = false;
    private $cURL_resorce = null;

    public function __construct($cURL_resorce, Request $request)
    {
        $this->request = $request;
        $this->cURL_resorce = $cURL_resorce;
        $this->output = curl_exec($this->cURL_resorce);
        $this->response_code = curl_getinfo($this->cURL_resorce, CURLINFO_HTTP_CODE);
    }

    public function output()
    {
        if ($this->output) {
            return $this->output;
        }
        return $this->cURL_resorce;
    }

    public function status()
    {
        return $this->response_code;
    }

    public function error(){
        return curl_error($this->cURL_resorce);
    }

    public function ok()
    {
        if ($this->response_code == 200) {
            return true;
        }
        return false;
    }

    public function created()
    {
        if ($this->response_code == 201) {
            return true;
        }
        return false;
    }

    public function accepted()
    {
        if ($this->response_code == 202) {
            return true;
        }
        return false;
    }

    public function nonAuthoritative()
    {
        if ($this->response_code == 203) {
            return true;
        }
        return false;
    }

    public function noContent()
    {
        if ($this->response_code == 204) {
            return true;
        }
        return false;
    }

    public function resetContent()
    {
        if ($this->response_code == 205) {
            return true;
        }
        return false;
    }


    public function partialContent()
    {
        if ($this->response_code == 206) {
            return true;
        }
        return false;
    }


    public function multipleChoices()
    {
        if ($this->response_code == 300) {
            return true;
        }
        return false;
    }


    public function movedPermanently()
    {
        if ($this->response_code == 301) {
            return true;
        }
        return false;
    }

    public function found()
    {
        if ($this->response_code == 302) {
            return true;
        }
        return false;
    }

    public function seeOther()
    {
        if ($this->response_code == 303) {
            return true;
        }
        return false;
    }

    public function notModified()
    {
        if ($this->response_code == 304) {
            return true;
        }
        return false;
    }

    public function useProxy()
    {
        if ($this->response_code == 305) {
            return true;
        }
        return false;
    }

    public function unused()
    {
        if ($this->response_code == 306) {
            return true;
        }
        return false;
    }

    public function temporaryRedirect()
    {
        if ($this->response_code == 307) {
            return true;
        }
        return false;
    }

    public function badRequest()
    {
        if ($this->response_code == 400) {
            return true;
        }
        return false;
    }

    public function unauthorized()
    {
        if ($this->response_code == 401) {
            return true;
        }
        return false;
    }

    public function paymentRequired()
    {
        if ($this->response_code == 402) {
            return true;
        }
        return false;
    }

    public function forbidden()
    {
        if ($this->response_code == 403) {
            return true;
        }
        return false;
    }

    public function notFound()
    {
        if ($this->response_code == 404) {
            return true;
        }
        return false;
    }

    public function methodNotAllowed()
    {
        if ($this->response_code == 405) {
            return true;
        }
        return false;
    }

    public function notAcceptable()
    {
        if ($this->response_code == 406) {
            return true;
        }
        return false;
    }

    public function proxyAuthenticationRequired()
    {
        if ($this->response_code == 407) {
            return true;
        }
        return false;
    }

    public function requestTimeout()
    {
        if ($this->response_code == 408) {
            return true;
        }
        return false;
    }

    public function conflict()
    {
        if ($this->response_code == 409) {
            return true;
        }
        return false;
    }

    public function gone()
    {
        if ($this->response_code == 410) {
            return true;
        }
        return false;
    }

    public function lengthRequired()
    {
        if ($this->response_code == 411) {
            return true;
        }
        return false;
    }

    public function preconditionFailed()
    {
        if ($this->response_code == 412) {
            return true;
        }
        return false;
    }

    public function requestEntityTooLarge()
    {
        if ($this->response_code == 413) {
            return true;
        }
        return false;
    }

    public function requestUriToLong()
    {
        if ($this->response_code == 414) {
            return true;
        }
        return false;
    }

    public function unsupportedMediaType()
    {
        if ($this->response_code == 415) {
            return true;
        }
        return false;
    }

    public function requestRangeNotSatisfiable()
    {
        if ($this->response_code == 416) {
            return true;
        }
        return false;
    }

    public function expectationFailed()
    {
        if ($this->response_code == 417) {
            return true;
        }
        return false;
    }

    public function internalServerError()
    {
        if ($this->response_code == 500) {
            return true;
        }
        return false;
    }

    public function notImplemented()
    {
        if ($this->response_code == 501) {
            return true;
        }
        return false;
    }

    public function badGateway()
    {
        if ($this->response_code == 502) {
            return true;
        }
        return false;
    }

    public function serviceUnavaliable()
    {
        if ($this->response_code == 503) {
            return true;
        }
        return false;
    }

    public function gatewayTimeout()
    {
        if ($this->response_code == 504) {
            return true;
        }
        return false;
    }

    public function httpVersionNotSupported()
    {
        if ($this->response_code == 505) {
            return true;
        }
        return false;
    }

    public function close()
    {
        curl_close($this->cURL_resorce);
    }

}