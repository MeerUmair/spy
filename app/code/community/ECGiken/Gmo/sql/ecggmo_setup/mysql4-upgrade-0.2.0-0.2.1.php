<?php
$installer = $this;
$installer->startSetup();

$installer->AddAttribute(
    'quote_payment',
    'gmo_cvs_pay_limit',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_cvs_pay_limit',
    array(
        'visible'=> false
    )
);

