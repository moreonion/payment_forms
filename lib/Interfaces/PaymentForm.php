<?php
namespace Drupal\payment_forms\Interfaces;

/**
 * Interface that all payment forms provide to PaymentContexts using them.
 */
interface PaymentForm {
  public function getForm(array &$element, array &$form_state);
  public function validateForm(array &$element, array &$form_state);
}
