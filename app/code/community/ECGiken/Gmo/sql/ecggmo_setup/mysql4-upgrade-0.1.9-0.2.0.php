<?php
$installer = $this;
$installer->startSetup();

$installer->AddAttribute(
    'quote_payment',
    'gmo_cvs_name',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_cvs_name',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'quote_payment',
    'gmo_cvs_code',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_cvs_code',
    array(
        'visible'=> false
    )
);

