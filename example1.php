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

$qResult = mysql_query("SELECT emarsys_sent_email.*, customers.email
FROM emarsys_sent_email, customers
WHERE emarsys_sent_email.sentFlag = '0' AND emarsys_sent_email.customerId = customers.id");

while($record = mysql_fetch_array($qResult)){

    $customer 		= {your customer details}
    $hkey = hash('sha256', md5($customer["email"]).md5($customer["id"]));
    $d   = explode(' ', $customer["register_dateTime"]);
    $registerDate   = $d[0];

    $dataUser = array(
        'name'		   => $customer["name"],
        'surname'		   => $customer["surname"],
        'email'		   => $customer["email"],
        'sex'	   => $customer["sex"],
        'city'		   => "",
        'registerDate' => $registerDate,
        'customerId'   => $customer["id"],
        'hKey' 		   => $hkey,
        'countryId'    => "",
        'basketAmount' => ''
    );
    if(!$emarsysAPI->isExistUser($customer["email"]))
    {
        $emarsysAPI->createUser($dataUser);
    } else {
        $emarsysAPI->updateUser($dataUser);
    }

    $data = array(
        'email'                   => $record['email'],
        'couponCode'              => $record['couponCode'],
        'couponCodeEmarsysField'  => $record["couponCodeEmarsysField"] // emarsys field id
    );
    if($emarsysAPI->couponCodeSetEmarsys($data))
    {
        $date = $emarsysAPI->get_date_time();
        mysql_query("UPDATE emarsys_sent_email SET emarsys_sent_email.sentFlag='1', emarsys_sent_email.sentDate='".$date."'
                     WHERE id='".$record['id']."' LIMIT 1");
    } else {
        echo "Failed!";
    }
}