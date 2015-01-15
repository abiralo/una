<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 *
 * @defgroup    TridentCore Trident Core
 * @{
 */

bx_import('BxTemplCaptchaReCAPTCHA');

/**
 * reCAPTCHA representation.
 * @see BxDolCaptcha
 */
class BxBaseCaptchaReCAPTCHANew extends BxTemplCaptchaReCAPTCHA
{
	protected $sApiUrl;
	protected $sVerifyUrl;

    public function __construct ($aObject, $oTemplate)
    {
        parent::__construct ($aObject, $oTemplate);

        $this->sApiUrl = 'https://www.google.com/recaptcha/api.js';
        $this->sVerifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    }

    /**
     * Display captcha.
     */
    public function display ($bDynamicMode = false)
    {
        $sCode = '';
        $aApiParams = array();
        if($bDynamicMode)  {
        	$sPostfix = time() . rand(0, PHP_INT_MAX);
        	$sId = 'sys-captcha-' . $sPostfix;

        	$sOnLoadFunction = 'onLoadCallback' . $sPostfix;
        	$sOnLoadCode = "
	        	var " . $sOnLoadFunction . " = function() {
					grecaptcha.render('" . $sId . "', {
						'sitekey': '" . $this->_sKeyPublic . "',
						'theme': '" . $this->_sSkin . "'
					});
				};
		        ";

        	$aApiParams = array(
        		'onload' => $sOnLoadFunction,
        		'render' => 'explicit'
        	);

        	$sCode .= $this->_oTemplate->_wrapInTagJsCode($sOnLoadCode);
        	$sCode .= '<div id="' . $sId . '">';
        }
        else {
        	$aApiParams = array(
        		'render' => 'onload'
        	);

        	$sCode .= '<div class="g-recaptcha" data-sitekey="' . $this->_sKeyPublic . '" data-theme="' . $this->_sSkin . '"></div>';
        }

        bx_import('BxDolLanguages');
        $aApiParams['hl'] = BxDolLanguages::getInstance()->getCurrentLanguage();

        return $this->_oTemplate->addJs(bx_append_url_params($this->sApiUrl, $aApiParams), $bDynamicMode) . $sCode;
    }

    /**
     * Check captcha.
     */
    public function check ()
    {
    	$mixedResponce = bx_file_get_contents($this->sVerifyUrl, array(
    		'secret' => $this->_sKeyPrivate, 
    		'response' => bx_process_input(bx_get('g-recaptcha-response')),
    		'remoteip' => getVisitorIP()
    	));
    	if($mixedResponce === false)
    		return false;

    	$aResponce = json_decode($mixedResponce, true); 	
    	if(isset($aResponce['success']) && $aResponce['success'] === true)
    		return true;

		if(!empty($aResponce['error-codes']))
			$this->_error = $aResponce['error-codes'];

		return false;
    }
}

/** @} */
