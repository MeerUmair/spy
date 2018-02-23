<?php
$installer = $this;
$installer->startSetup();

$installer->AddAttribute(
    'quote_payment',
    'gmo_cc_card_seq',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_cc_card_seq',
    array(
        'visible'=> false
    )
);

$installer->AddAttribute(
    'quote_payment',
    'gmo_cc_card_data',
    array(
        'visible'=> false
    )
);
$installer->AddAttribute(
    'order_payment',
    'gmo_cc_card_data',
    array(
        'visible'=> false
    )
);
