<?php
class Rack_Fpcmod_Model_Observer
{
    public function modifyParams($ob)
    {
        $parameters = $ob->getEvent()->getParameters();
        $value = $parameters->getValue();

        $request = Mage::app()->getRequest();
        $urlKey = Mage::getStoreConfig('mana/ajax/url_key_filter');
        $routeSeparator = Mage::getStoreConfig('mana/ajax/route_separator_filter');

        $params = Mage::helper('fpc')->getCSStoreConfigs(Lesti_Fpc_Helper_Data::XML_PATH_URI_PARAMS);
        $path = ltrim($request->getServer('REDIRECT_URL'), '/');

        if(strpos($path, $urlKey . '/') || strpos($path, '/'. $routeSeparator.'/'))
        {
            $value['mana_key'] = $urlKey;
            $value['mana_separator'] = $routeSeparator;
        }

        $uri = explode('/', $request->getServer('REQUEST_URI'));
        $keys = array_flip($uri);

        foreach($keys as $key => $_value) {
            if(in_array(urldecode($key), $params)) {
                $value['url_' . $key] = preg_replace('/\.html/', '', $uri[$_value+1]);
            }
        }
        $parameters->setValue($value);
    }
}