<?php namespace angelleye\PayPal\rest\vault;

class CreditCardAPI {
    private $_api_context;
    public function __construct($configArray)
    {   // setup PayPal api context 
        $this->_api_context = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential($configArray['ClientID'],$configArray['ClientSecret'])
            );
    }
    
    public function StoreCreditCard($requestData){
        $creditCard = new \PayPal\Api\CreditCard();
        if ($this->checkEmptyObject($requestData['creditCard'])) {
            $this->setArrayToMethods(array_filter($requestData['creditCard']), $creditCard);
        }
        if ($this->checkEmptyObject($requestData['payerInfo'])) {
            $this->setArrayToMethods(array_filter($requestData['payerInfo']), $creditCard);    
        }
        if ($this->checkEmptyObject($requestData['billingAddress'])) {
            $this->setArrayToMethods(array("BillingAddress"=>array_filter($requestData['billingAddress'])), $creditCard); 
        }
        if ($this->checkEmptyObject($requestData['optionalArray'])) {
            $this->setArrayToMethods($requestData['optionalArray'], $creditCard);                     
        }
        try {
            $requestArray = clone $creditCard;
            $creditCard->create($this->_api_context);     
            $returnArray=$creditCard->toArray();
            $returnArray['REQUESTDATA']=$requestArray->toArray();
            $returnArray['RAWREQUEST']=$requestArray->toJSON();
            $returnArray['RAWRESPONSE']=$creditCard->toJSON();            
            return $returnArray;
        }
        catch (\PayPal\Exception\PayPalConnectionException $ex) {
            $errorReturnArray['ERRORS']=  json_decode($ex->getData(),true);
            $errorReturnArray['REQUESTDATA']=$requestArray->toArray();
            $errorReturnArray['RAWREQUEST']=$requestArray->toJSON();
            $errorReturnArray['RAWRESPONSE']='';
            return $errorReturnArray;
        }
    }
        
    public function listAllCards($requestData) {   
        $creditCard = new \PayPal\Api\CreditCard();
        try {            
            $params = array_filter($requestData);
            $requestArray = json_encode($params);
            $cards = $creditCard->all($params, $this->_api_context);
            $returnArray=$cards->toArray();
            $returnArray['REQUESTDATA']=$params;
            $returnArray['RAWREQUEST']=$requestArray;
            $returnArray['RAWRESPONSE']=$cards->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            $errorReturnArray['ERRORS']=  json_decode($ex->getData(),true);
            $errorReturnArray['REQUESTDATA']=$params;
            $errorReturnArray['RAWREQUEST']=$requestArray;
            $errorReturnArray['RAWRESPONSE']='';
            return $errorReturnArray;
        }
    }
    
    public function showByID($requestData){        
        $creditCard = new \PayPal\Api\CreditCard();
            try {
                if(empty($requestData['credit_card_id'])){
                    $errorReturnArray['ERRORS']= "Card ID is Empty.";                    
                    return $errorReturnArray;
                }
                $requestArray = clone $creditCard->setId($requestData['credit_card_id']);
                $card = $creditCard->get($requestData['credit_card_id'], $this->_api_context);
                $returnArray=$card->toArray();
                $returnArray['REQUESTDATA']=$requestArray->toArray();
                $returnArray['RAWREQUEST']=$requestArray->toJSON();
                $returnArray['RAWRESPONSE']=$card->toJSON();
                return $returnArray;
            } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
                $errorReturnArray['ERRORS']=  json_decode($ex->getData(),true);
                $errorReturnArray['REQUESTDATA']=$requestArray->toArray();
                $errorReturnArray['RAWREQUEST']=$requestArray->toJSON();
                $errorReturnArray['RAWRESPONSE']='';
                return $errorReturnArray;
            }                    
    }
    
    public function deleteByID($requestData){        
        $creditCard = new \PayPal\Api\CreditCard();        
        try {
            if(empty($requestData['credit_card_id'])){
                $errorReturnArray['ERRORS']= "Card ID is Empty.";                    
                return $errorReturnArray;
            }
            $creditCard->setId($requestData['credit_card_id']);
            return $creditCard->delete($this->_api_context);                
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $ex->getData();                
        }
    }
        
    public function UpdateCreditCard($requestData,$credit_card_id){
            if(empty($credit_card_id)){
                $errorReturnArray['ERRORS']= "Card ID is Empty.";                    
                return $errorReturnArray;
            }
            $creditCard = new \PayPal\Api\CreditCard();
            $pathRequest = new \PayPal\Api\PatchRequest();
        try {
            $creditCard->setId($credit_card_id);
            $id = $creditCard->getId();
            $i=0;
            foreach ($requestData as $value) {  
                if(is_array($value['value'])){
                    if(!empty($value['operation']) && !empty($value['path']) && count($value['value'])>3){
                        $ob=(object)  array_filter($value['value']);
                        $pathOperation = new \PayPal\Api\Patch();
                        $pathOperation->setOp($value['operation'])
                                         ->setPath("/".$value['path'])
                                         ->setValue($ob);
                        $pathRequest->addPatch($pathOperation);
                        $i++;
                    }
                }
                else{
                    if(!empty($value['operation']) && !empty($value['path']) && !empty($value['value'])){
                        $pathOperation = new \PayPal\Api\Patch();
                        $pathOperation->setOp($value['operation'])
                                         ->setPath("/".$value['path'])
                                         ->setValue($value['value']);
                        $pathRequest->addPatch($pathOperation);
                        $i++;
                    }
                }
            } 
            if($i>0) {
               
                $card = $creditCard->update($pathRequest,$this->_api_context);
                $returnArray=$card->toArray();
                $returnArray['REQUESTDATA']=$pathRequest->toArray();
                $returnArray['RAWREQUEST']= $pathRequest->toJSON();
                $returnArray['RAWRESPONSE']=$card->toJSON();
                return $returnArray;                
            }
            else{
                return "Fill Atleast One Array Field/Element";
            }
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            $errorReturnArray['ERRORS']=  json_decode($ex->getData(),true);
            $errorReturnArray['REQUESTDATA']=$pathRequest->toArray();
            $errorReturnArray['RAWREQUEST']= $pathRequest->toJSON();
            $errorReturnArray['RAWRESPONSE']='';
            return $errorReturnArray;            
        }
    }
    
    public function setArrayToMethods($array,$object){
        foreach ($array as $key => $val){
            $method = 'set'.$key;
            if(!empty($val)){
                if (method_exists($object, $method))
                {                   
                     $object->$method($val);
                }            
            }
        }
        return TRUE;
    }
    
    public function checkEmptyObject($array){
        if(count(array_filter($array)) > 0){
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
}
