<?php

namespace Drupal\payment_forms;

/**
 * Define form callbacks and their methods.
 *
 * This would be better as a trait, but due to the properties it could define
 * the default values for the callback properties. So this is just a template
 * for copy & pasting the relevant callback property and method signature.
 */
abstract class FormCallbacksMixin extends \PaymentMethodController {

  public $payment_configuration_form_elements_callback = 'payment_forms_payment_form';
  public $payment_method_configuration_form_elements_callback = 'payment_forms_method_configuration_form';

  /**
   * Create a new payment form object.
   *
   * @return \Drupal\payment_forms\PaymentFormInterface
   */
  abstract public function paymentForm(\Payment $payment);

  /**
   * Create a new method configuration form object.
   *
   * @return \Drupal\payment_forms\MethodFormInterface
   */
  abstract public function configurationForm(\PaymentMethod $method);

}
