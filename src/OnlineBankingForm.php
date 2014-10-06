<?php

namespace Drupal\payment_forms;

use \Drupal\payment_forms\PaymentContextInterface;
/**
 *
 */
class OnlineBankingForm implements FormInterface {

  public function getForm(array &$form, array &$form_state, PaymentContextInterface $context) {
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
