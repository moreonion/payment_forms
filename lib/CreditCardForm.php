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

  public function validateForm(array &$element, array &$form_state) {
  $values = drupal_array_get_nested_value($form_state['values'], $element['#parents']);

  # validate presence of all fields:
  #   issuer, credit_card_number, secure_code, expiry_date
  foreach($values as $key => $value) {
    if(empty($value)) {
      form_error($element[$key], t('@name is required', array('@name' => $element[$key]['#title'])));
    }

    $form_state['payment']->method_data[$key] = $value;
  }

  require_once(dirname(__FILE__) . '/../creditcard_validation.inc.php');

  $credit_card_validator = new \CreditCardValidator();

  $issuer             = $form_state['payment']->method_data['issuer'];
  $credit_card_number = $form_state['payment']->method_data['credit_card_number'];
  $secure_code        = $form_state['payment']->method_data['secure_code'];
  $expiry_date        = $form_state['payment']->method_data['expiry_date'];

  # validate creditcard number
  $validation_result = $credit_card_validator->isValidCreditCard($credit_card_number, '', TRUE);
  if ($validation_result->valid == FALSE) {
    form_error($element['credit_card_number'], t('%card is not a valid credit card number.', array('%card' => $credit_card_number)));
  }
  elseif ($validation_result->issuer != $issuer) {
    form_error($element['credit_card_number'], t(
      'Credit card number %card doesn\'t appear to be from issuer %issuer.',
      array(
        '%card'   => $credit_card_number,
        '%issuer' => $element['issuer']['#options'][$issuer_index],
      )
    ));
  }

  # validate secure code (CVC)
  if ($credit_card_validator->isValidCardValidationCode($secure_code, $issuer) == FALSE) {
    switch ($issuer) {
    case 'visa':
      $cvc_labeling = 'CVV2 (Card Verification Value 2)';
      break;

    case 'amex':
      $cvc_labeling = 'CID (Card Identification Number)';
      break;

    default:
      $cvc_labeling = 'CVC2 (Card Validation Code 2)';
    }
    form_error($element['secure_code'], t('The ' . $cvc_labeling . ' %card is not valid.', array('%card' => $secure_code)));
  }

  # validate expiry date
  if (($date_object = $credit_card_validator->isValidToDate($expiry_date)) == FALSE) {
    form_error($element['expiry_date'], t('The entered expiration date is wrong or the card is expired.'));
  } else {
    # only these two formats could have passed validation
    # save a date object for further use in request to mPay24 API
    if(strlen($expiry_date) == 4) {
      $form_state['payment']->method_data['expiry_date'] = date_create_from_format("my", $expiry_date);
    } elseif (strlen($expiry_date == 5)) {
      $form_state['payment']->method_data['expiry_date'] = date_create_from_format("m/y", $expiry_date);
    } else {
      $form_state['payment']->method_data['expiry_date'] = date_create();
    }
  }
  }
}
