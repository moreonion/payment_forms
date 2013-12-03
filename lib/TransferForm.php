<?php
/**
 * @file
 *
 * @author    Paul Haerle <phaer@phaer.org>
 * @copyright Copyright (c) 2013 copyright
 */

namespace Drupal\payment_forms;

/**
 *
 */
class TransferForm implements Interfaces\PaymentForm {

  public function getForm(array &$form, array &$form_state) {
    $form['send_transfer_form'] = array(
      '#type'     => 'markup',
      '#markup'    => t('The transfer form will be sent to the address you provided earlier.'),
    );

    return $form;
  }

  public function validateForm(array &$element, array &$form_state) {
    $values = drupal_array_get_nested_value($form_state['values'], $element['#parents']);

    if (!empty($values['send_transfer_form'])) {
      $form_state['payment']->method_data['send_transfer_form'] = $values['send_transfer_form'];
    }
  }
}
