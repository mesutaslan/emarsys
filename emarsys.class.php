<?php
/**
 * emarsys
 *
 * @copyright 2014
 * @author mesut ASLAN
 * @version 1.0 25/01/2014
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions
 * of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 * TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */


require dirname(__FILE__) .'/lib/simplerestclient.php';
require dirname(__FILE__) .'/lib/ajaxGateway.php';

class emarsys
{
	/**
     * @param $data = array
     * @return boolean
     */
	function eventTracking($data)
	{
		$arrayData = array(
			'id'			=> '602', // External Event ID
			'key_id'		=> '3', 		// Key ID
			'external_id'  	=> $data["email"],	//Email Address
			'data'   		=> array(
				'sepUrunVarx2' => array(   // External identifier name
					'mailContent' => $data["basketContent"]
				)
			)
		);
		$params = array();
		$params["url"] = "event/602/trigger";
		$gateway = new AjaxGateway($params);
		$result = $gateway->getResponse(json_encode($arrayData), 'POST');
		$obj	= json_decode($result);
		if($obj->replyText=="OK")
		{
			return true;
		} else {
			return false;
		}
	}

    function get_date_time()
    {
        return date("Y-m-d H:i:s");
    }


    function get_date()
    {
        return date("Y-m-d");
    }

    function get_date_sub($strDate)
    {
      return date("Y-m-d", strtotime($strDate));
    }

    /**
     * @param $length = integer
     * @return string
     */
    function couponGenerate($length)
    {
        $pass = "";
        $chars = "23456789ABCDEFGHJKLMNPRSTUVWXYZ";
        $charCount = strlen($chars);
        for ($ras = 0; $ras < $length; $ras++)
        {
            $char = rand(0,$charCount-1);
            $pass .= $chars[$char];
        }
        return $pass;
    }

    /**
     * @param $customerId = integer
     * @param $couponCampaignId = integer
     * @return string
     */
    function createCoupon($customerId, $couponCampaignId)
    {
        $today 			= date("Y-m-d");
        $expirationDate = $this->get_date_sub("+30 days");
        $codePart1		= $this->couponGenerate(3);
        $codePart2 		= $this->couponGenerate(3);
        $couponCode 	= $codePart1.$customerId.$codePart2;
        $sql = "INSERT INTO coupon_code (couponCampaignId, couponCode, expiration...) 
        		VALUES(couponCampaignId, couponCode, expiration.....)";
        if(mysql_query($sql))
        {
            return $couponCode;
        }
        else {
            return false;
        }
    }

    /**
     * @param $emailAddress = string
     * @return boolean
     */
	function isExistUser($emailAddress)
	{
		$arrayData = array(
			'keyId'		=> '3',
			'keyValues' => array($emailAddress)
		);
		$params = array();
		$params["url"] = "contact/getdata";
		$gateway = new AjaxGateway($params);
        $result	= json_decode($gateway->getResponse(json_encode($arrayData), 'POST'));
		if($result->data->result[0]->{3}===$emailAddress)
		{
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * @param $data = array('name'=>'mesut',
	 * 						'surname'=>'aslan',
	 * 						'email'=>'mesaslan@gmail.com', 
	 * 						'sex'=>'m',
	 * 						'city'=>'İstanbul',
	 						'registerDate'=>'2013-12-25',
	 						'hKey'=>'e0b5ef61bca5ff27436egh44d78bc31594c4',
	 						'customerId'=>'34634633',
	 						'basketAmount'=>'203,80',
	 * 				  );
	 * @return boolean
	 */
	
	function createUser($data)
	{
		$sex  = array('e' => '1', 'k'=>'2' );
        $data_CountryId = (strlen($data['countryId'])>0) ? $data['countryId'] : '178'; // turkey: 178
        $arrayData = array(
			'key_id'=> '3',
			'1'		=> $data["name"],
			'2'		=> $data["surname"],
			'3'		=> $data["email"], 
			'5'		=> $sex[$data["sex"]], 
			'12'	=> $data["city"],
            '8838'	=> $data["registerDate"],
            '7849'	=> $data["hKey"],
            '6735'	=> $data["customerId"],
            '14'	=> $data_CountryId,
			'8779'  => $data["basketAmount"]
		);
		$params = array();
		$params["url"] = "contact";
		$gateway = new AjaxGateway($params);
		$result = json_decode($gateway->getResponse(json_encode($arrayData), 'POST'));
		if($result->replyText=="OK")
		{
			return true;
		} else {
			return false;
		}	
	}
	
	function updateUser($data)
	{
		$sex  = array('e' => '1', 'k'=>'2' );
        $data_CountryId = (strlen($data['countryId'])>0) ? $data['countryId'] : '178';
        $arrayData = array(
			'key_id'=> '3',
			'1'		=> $data["name"],
			'2'		=> $data["surname"],
			'3'		=> $data["email"], 
			'5'		=> $sex[$data["sex"]], 
			'12'	=> $data["city"],
            '8838'	=> $data["registerDate"],
            '7849'	=> $data["hKey"],
            '6735'	=> $data["customerId"],
            '14'	=> $data_CountryId,
			'8779'  => $data["basketAmount"]
		);
		$params = array();
		$params["url"] = "contact";
		$gateway = new AjaxGateway($params);
		$result = json_decode($gateway->getResponse(json_encode($arrayData), 'PUT'));
		if($result->replyText=="OK")
		{
			return true;
		} else {
			return false;
		}	
	}

    function getCustomerDetailFromDB($customerID)
    {
        $customerResult = mysql_query("SELECT * FROM customers WHERE id=".$customerID);
        while($customer = mysql_fetch_array($customerResult)){
            return $customer;
        }
    }

    function couponCodeSetEmarsys($data)
    {
        $arrayData = array(
            'key_id'                        => '3',
            '3'		                        => $data['email'],
            $data['couponCodeEmarsysField'] => $data['couponCode']
        );
        $params = array();
        $params["url"] = "contact";
        $gateway = new AjaxGateway($params);
        $result = json_decode($gateway->getResponse(json_encode($arrayData), 'PUT'));
        if($result->replyText=="OK")
        {
            return true;
        } else {
            return false;
        }
    }

    function setLastPurchaseDate($data)
    {
        $arrayData = array(
            'key_id'=> '3',
            '3'		=> $data["email"],
            '8835'	=> $data["lastPurchaseDate"],
            '9394'  => $data["language"]
        );
        $params = array();
        $params["url"] = "contact";
        $gateway = new AjaxGateway($params);
        $result = json_decode($gateway->getResponse(json_encode($arrayData), 'PUT'));
        if($result->replyText=="OK")
        {
            return true;
        } else {
            return false;
        }
    }

    function getBasketAmount($basket_id)
    {
        $productList    = mysql_query('your SQL');
        $totalBasketAmount = 0;
        while($product = mysql_fetch_array($productList)) {
            $totalBasketAmount = $totalBasketAmount + floatval($product['quantity']*$product['price']);
        }
        return $totalBasketAmount;
    }
	
	function getBasketHTML($basket_id, $email, $hkey, $id)
	{
		$body = " Generate customer basket from your database"; // result HTML
		return $body;
	}

    function getEmarsysCountryId($yourCountryId)
    {
        $countryList= array('1'=>'178',
                            '2'=>'65',
                            '3'=>'10',
                            '4'=>'17',
                            '5'=>'26',
                            '6'=>'48',
                            '7'=>'61',
                            '9'=>'124',
                            '10'=>'184',
                            '11'=>'81',
                            '12'=>'162',
                            '13'=>'168',
                            '14'=>'83',
                            '15'=>'81',
                            '16'=>'100',
                            '17'=>'102',
                            '18'=>'116',
                            '19'=>'67',
                            '20'=>'1',
                            '21'=>'11',
                            '22'=>'183',
                            '23'=>'22',
                            '24'=>'64',
                            '25'=>'44',
                            '26'=>'80',
                            '27'=>'82',
                            '28'=>'141',
                            '29'=>'87',
                            '30'=>'46',
                            '31'=>'93',
                            '32'=>'53',
                            '33'=>'179',
                            '34'=>'151',
                            '35'=>'187',
                            '36'=>'182',
                            '37'=>'32',
                            '38'=>'185',
                            '39'=>'9',
                            '40'=>'167',
                            '41'=>'2',
                            '42'=>'129',
                            '43'=>'95',
                            '44'=>'142',
                            '45'=>'177',
                            '46'=>'118',
                            '47'=>'86',
                            '48'=>'156',
                            '49'=>'106',
                            '50'=>'143',
                            '51'=>'75',
                            '52'=>'85',
                            '53'=>'139',
                            '54'=>'60',
                            '55'=>'140',
                            '56'=>'113',
                            '57'=>'131',
                            '59'=>'169',
                            '60'=>'92',
                            '61'=>'78',
                            '62'=>'79',
                            '63'=>'77',
                            '64'=>'13',
                            '65'=>'76',
                            '66'=>'255',
                            '67'=>'101',
                            '68'=>'96',
                            '69'=>'103',
                            '70'=>'153',
                            '71'=>'157',
                            '72'=>'158',
                            '73'=>'130',
                            '74'=>'3',
                            '75'=>'193',
                            '76'=>'125'
        );
        return $countryList[$yourCountryId];
    }
}