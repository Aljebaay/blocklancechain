<?php
declare(strict_types=1);

$generatedFile = __DIR__ . DIRECTORY_SEPARATOR . 'endpoints.generated.php';
$generated = is_file($generatedFile) ? (require $generatedFile) : [];
if (!is_array($generated)) {
    $generated = [];
}

$moduleOverrides = [
    'requests.active_request' => [
        'path' => 'requests/active_request.php',
        'handler' => 'app/Modules/Requests/active_request.php',
    ],
    'requests.buyer_requests' => [
        'path' => 'requests/buyer_requests.php',
        'handler' => 'app/Modules/Requests/buyer_requests.php',
    ],
    'requests.crypto_charge' => [
        'path' => 'requests/crypto_charge.php',
        'handler' => 'app/Modules/Requests/crypto_charge.php',
    ],
    'requests.delete_request' => [
        'path' => 'requests/delete_request.php',
        'handler' => 'app/Modules/Requests/delete_request.php',
    ],
    'requests.dusupay_charge' => [
        'path' => 'requests/dusupay_charge.php',
        'handler' => 'app/Modules/Requests/dusupay_charge.php',
    ],
    'requests.fetch_subcategory' => [
        'path' => 'requests/fetch_subcategory.php',
        'handler' => 'app/Modules/Requests/fetch_subcategory.php',
    ],
    'requests.insert_offer' => [
        'path' => 'requests/insert_offer.php',
        'handler' => 'app/Modules/Requests/insert_offer.php',
    ],
    'requests.load_category_data' => [
        'path' => 'requests/load_category_data.php',
        'handler' => 'app/Modules/Requests/load_category_data.php',
    ],
    'requests.load_search_data' => [
        'path' => 'requests/load_search_data.php',
        'handler' => 'app/Modules/Requests/load_search_data.php',
    ],
    'requests.manage_requests' => [
        'path' => 'requests/manage_requests.php',
        'handler' => 'app/Modules/Requests/manage_requests.php',
    ],
    'requests.mercadopago_charge' => [
        'path' => 'requests/mercadopago_charge.php',
        'handler' => 'app/Modules/Requests/mercadopago_charge.php',
    ],
    'requests.offer_submit_order' => [
        'path' => 'requests/offer_submit_order.php',
        'handler' => 'app/Modules/Requests/offer_submit_order.php',
    ],
    'requests.pause_request' => [
        'path' => 'requests/pause_request.php',
        'handler' => 'app/Modules/Requests/pause_request.php',
    ],
    'requests.paystack_charge' => [
        'path' => 'requests/paystack_charge.php',
        'handler' => 'app/Modules/Requests/paystack_charge.php',
    ],
    'requests.post_request' => [
        'path' => 'requests/post_request.php',
        'handler' => 'app/Modules/Requests/post_request.php',
    ],
    'requests.send_offer_modal' => [
        'path' => 'requests/send_offer_modal.php',
        'handler' => 'app/Modules/Requests/send_offer_modal.php',
    ],
    'requests.stripe_charge' => [
        'path' => 'requests/stripe_charge.php',
        'handler' => 'app/Modules/Requests/stripe_charge.php',
    ],
    'requests.submit_proposal_details' => [
        'path' => 'requests/submit_proposal_details.php',
        'handler' => 'app/Modules/Requests/submit_proposal_details.php',
    ],
    'requests.view_offers' => [
        'path' => 'requests/view_offers.php',
        'handler' => 'app/Modules/Requests/view_offers.php',
    ],
    'proposals.sections.edit.pricing' => [
        'path' => 'proposals/sections/edit/pricing.php',
        'handler' => 'app/Modules/Proposals/Sections/Edit/pricing.php',
    ],
    'proposals.ajax.check.pricing' => [
        'path' => 'proposals/ajax/check/pricing.php',
        'handler' => 'app/Modules/Proposals/Ajax/Check/pricing.php',
    ],
];

return array_replace($generated, $moduleOverrides);
