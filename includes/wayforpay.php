<?php
/**
 * Fields settings PayPal Payment
 */
/*
return apply_filters(
	'learn-press/gateway-payment/wayforpay/settings',
	array(
		array(
			'type' => 'title',
		),
		array(
			'title'   => esc_html__( 'Enable/Disable', 'learnpress' ),
			'id'      => '[enable]',
			'default' => 'no',
			'type'    => 'checkbox',
			'desc'    => esc_html__( 'Увімкніть платіжний модуль WayForPay.', 
			                        'learnpress' ),
		),
		array(
			'title' => esc_html__( 'Логін продавця', 'learnpress' ),
			'id'    => '[wayforpay_login]',
			'type'  => 'text',
			'desc'  => esc_html__( '', 'learnpress' ),
		),
		array(
			'title' => esc_html__( 'Секретний ключ продавця', 'learnpress' ),
			'id'    => '[app_client_secret]',
			'type'  => 'text',
			'desc'  => esc_html__('', 'learnpress')
		),
		array(
			'type' => 'sectionend',
		),
	)
);
