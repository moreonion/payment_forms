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
class CreditCardForm implements Interfaces\PaymentForm {
  static protected $issuers = array(
    'visa' => 'Visa',
    'mastercard' => 'MasterCard',
    'amex' => 'American Express',
  );
  static protected $cvc_label = array(
    'visa' => 'CVV2 (Card Verification Value 2)',
    'amex' => 'CID (Card Identification Number)',
    'mastercard' => 'CVC2 (Card Validation Code 2)',
  );

  public function getForm(array &$form, array &$form_state) {
    $form['issuer'] = array(
      '#type'		    => 'select',
      '#options'   	=> self::$issuers,
      '#empty_value'	=> '',
      '#title'		=> t('Issuer'),
      '#weight'		=> 0,
    );

    $form['credit_card_number'] = array(
      '#type'      => 'textfield',
      '#title'     => t('Credit card number '),
      '#weight'    => 1,
      '#size'      => 32,
      '#maxlength' => 32,
    );

    $form['secure_code'] = array(
      '#type'      => 'textfield',
      '#title'     => t('Secure code'),
      '#weight'    => 2,
      '#size'      => 4,
      '#maxlength' => 4,
    );

    $form['expiry_date'] = array(
      '#type'      => 'textfield',
      '#title'     => t('Expiry date'),
      '#weight'    => 3,
      '#size'      => 5,
      '#maxlength' => 5,
    );

    return $form;
  }

  /**
   * Mockable wrapper around form_error().
   */
  protected function formError(array &$element, $error) {
    form_error($element, $error);
  }

  /**
   * Validate and data and set form errors accordingly.
   */
  public function validateValues(array &$element, array &$data) {
    $data['credit_card_number'] = preg_replace('/\s+/', '', $data['credit_card_number']);

    require_once(dirname(__FILE__) . '/../creditcard_validation.inc.php');
    $credit_card_validator = new \CreditCardValidator();

    $validation_result = $credit_card_validator->isValidCreditCard($data['credit_card_number'], '', TRUE);
    if (!$validation_result->valid) {
      $this->formError($element['credit_card_number'], t('%card is not a valid credit card number.', array('%card' => $data['credit_card_number'])));
    }
    elseif ($validation_result->issuer != $data['issuer']) {
      $this->formError($element['credit_card_number'], t(
        'Credit card number %card doesn\'t appear to be from issuer %issuer.',
        array(
          '%card'   => $data['credit_card_number'],
          '%issuer' => self::$issuers[$data['issuer']],
        )
      ));
    }

    // Validate secure code (CVC).
    if (!$credit_card_validator->isValidCardValidationCode($data['secure_code'], $data['issuer'])) {
      $msg = t('The %secure_code_label %code is not valid.', array(
        '%card' => $data['secure_code'],
        '%secure_code_label' => self::$cvc_label[$data['issuer']],
      ));
      $this->formError($element['secure_code'], $msg);
    }

    // Validate expiry date.
    if ($data['expiry_date'] = $this->parseDate($data['expiry_date'])) {
      if ($data['expiry_date']->getTimestamp() < time()) {
        $this->formError($element['expiry_date'], t('The credit card has expired.'));
      }
    } else {
      $this->formError($element['expiry_date'], t('Please enter a valid expiration date'));
    }
  }

  public function parseDate($date) {
    $dateObj = FALSE;
    $valid4 = strlen($date) == 4 && $dateObj = date_create_from_format("my", $date);
    $valid5 = strlen($date) == 5 && $dateObj = date_create_from_format("m/y", $date);
    if ($valid4 || $valid5) {
      $dateObj->setTime(0,0,0);
      $dateObj->modify('first day of this month');
    }
    return $dateObj;
  }

  public function validateForm(array &$element, array &$form_state) {
    $values = drupal_array_get_nested_value($form_state['values'], $element['#parents']);

    $this->vaidateValues($element, $values);

    // Merge in validated fields.
    foreach(array('issuer', 'credit_card_number', 'secure_code', 'expiry_date') as $key) {
      $form_state['payment']->method_data[$key] = $values[$key];
    }
  }
}
