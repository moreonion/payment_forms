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
class OnlineBankingForm implements Interfaces\PaymentForm {

  public function getForm(array &$form, array &$form_state) {
    $form['redirection_info'] = array(
      '#type'	=> 'markup',
      '#markup'  => t('After submitting this form you will be redirected to our external payment partner to finish the transaction.'),
      '#weight'	=> 0,
    );

    return $form;
  }

  public function validateForm(array &$element, array &$form_state) {
    // safe reference to form_state, needed for later execute()
    $form_state['payment']->form_state = &$form_state;
  }
}
