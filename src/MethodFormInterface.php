<?php
namespace Drupal\payment_forms;

/**
 * Interface for payment forms used by this module.
 */
interface MethodFormInterface {

  /**
   * Add form elements to the $element Form-API array.
   */
  public function form(array $element, array &$form_state, \PaymentMethod $method);

  /**
   * Validate the submitted values.
   */
  public function validate(array $element, array &$form_state, \PaymentMethod $method);

}
