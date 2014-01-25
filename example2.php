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

require "emarsys.class.php";
$emarsysAPI = new emarsys;

$date_time = $emarsysAPI->get_date_time();

$basketResult = mysql_query("
SELECT 
        * 
    FROM 
        basket_emarsys as be
    WHERE mail_sent=0 and 
    be.date_add > DATE_SUB(CURRENT_TIMESTAMP(),INTERVAL 4 HOUR)"
);
  while($basket = mysql_fetch_array($basketResult)){
    $customer       = $emarsysAPI->getCustomerDetailFromDB($basket["customer"]);
    $hkey = hash('sha256', md5($customer["email"]).md5($customer["id"]));
    $basketContent  = $emarsysAPI->getBasketHTML($basket["basket_id"], $customer["email"], $hkey, $customer["id"]);
    $basketAmount   = $emarsysAPI->getBasketAmount($basket["basket_id"]);
    $countryId      = $emarsysAPI->getEmarsysCountryId($basket["ship_country"]);
    $d   = explode(' ', $customer["date_register"]);
    $registerDate   = $d[0];
    if(!$emarsysAPI->isExistUser($customer["email"]))
    {
        $dataCreateUser = array(
            'name'        => $customer["name"], 
            'surname'     => $customer["surname"], 
            'email'     => $customer["email"], 
            'sex'  => $customer["sex"], 
            'sehir'     => "",
            'registerDate' => $registerDate,
            'customerId'=> $customer["id"], 
            'hKey'      => $hkey,
            'countryId' => $countryId,
            'basketAmount' => $basketAmount
        );
        $emarsysAPI->createUser($dataCreateUser);
    } else {
        $dataUpdateUser = array(
            'name'        => $customer["name"], 
            'surname'     => $customer["surname"], 
            'email'     => $customer["email"], 
            'sex'  => $customer["sex"], 
            'sehir'     => "",
            'registerDate' => $registerDate,
            'customerId'=> $customer["id"], 
            'hKey'      => $hkey,
            'countryId' => $countryId,
            'basketAmount' => $basketAmount
        );
        $emarsysAPI->updateUser($dataUpdateUser);

    }
    $dataEvent = array(
            'email'=> $customer["email"],
            'basketContent'=> $basketContent
    );

    if($emarsysAPI->eventTracking($dataEvent))
    {
        
        mysql_query("UPDATE basket_emarsys SET mail_sent=1, mail_sent_date='".$date_time."' WHERE id='".$basket["id"]."'");
    }
  }