<?php
$installer = $this;
$installer->startSetup();

$installer->AddAttribute(
    'quote_payment',
    'gmo_conf_no',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_conf_no',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'quote_payment',
    'gmo_receipt_no',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_receipt_no',
    array(
        'visible'=> false
    )
);

