<?php

/**
 * @file
 * Documents hooks invoked by this module.
 *
 * This file does not contain code that is executed.
 */

/**
 * Let other modules alter a payment form element.
 */
function hook_payment_forms_payment_form_alter(&$element, \Payment $payment) {
  if ($payment->method->controller instanceof PaypalPaymentECController) {
    $element['#is_paypal'] = TRUE;
  }
}
