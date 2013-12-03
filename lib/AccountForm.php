<?php
namespace Drupal\payment_forms;

/**
 *
 */
class AccountForm implements Interfaces\PaymentForm {
  static protected $id = 0;

  public function getForm(array &$form, array &$form_state) {
    $form['holder'] = array(
      '#type'     => 'textfield',
      '#title'    => t('Account holder'),
    );

    include_once DRUPAL_ROOT . '/includes/locale.inc';

    $id = 'payment-form-account-selector-' . self::$id++;
    $form['account_or_ibanbic'] = array(
      '#type' => 'radios',
      '#options' => array(
        'ibanbic' => 'IBAN & BIC',
        'account' => 'Account number & bank code',
      ),
      '#default_value' => 'ibanbic',
      '#id' => $id,
      '#required' => TRUE,
    );

    $form['account'] = array(
      '#type' => 'container',
      '#states' => array(
        'visible' => array(
          '#' . $id . ' input' => array('value' => 'account'),
        )
      ),
    );
    $element = &$form['account'];

    $element['country'] = array(
      '#type'          => 'select',
      '#title'         => t('Country'),
      '#options'       => country_get_list(),
      '#default_value' => 'AT',
    );

    $element['account'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Account number'),
      '#required'      => FALSE,
      '#default_value' => NULL,
      '#size'          => 16,
      '#maxlength'     => 16,
    );

    $element['bank_code'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Bank code'),
      '#required'      => FALSE,
      '#default_value' => NULL,
      '#size'          => 8,
      '#maxlength'     => 8,
    );

    $form['ibanbic'] = array(
      '#type' => 'container',
      '#states' => array(
        'visible' => array(
          '#' . $id . ' input' => array('value' => 'ibanbic'),
        )
      ),
    );
    $element =& $form['ibanbic'];

    $element['iban'] = array(
      '#type'          => 'textfield',
      '#title'         => t('IBAN'),
      '#required'      => FALSE,
      '#default_value' => NULL,
      '#size'          => 48,
      '#maxlength'     => 48,
    );

    $element['bic'] = array(
      '#type'          => 'textfield',
      '#title'         => t('BIC'),
      '#required'      => FALSE,
      '#default_value' => NULL,
      '#size'          => 16,
      '#maxlength'     => 16,
    );

    return $form;
  }

  public function validateForm(array &$element, array &$form_state) {

    $values = &drupal_array_get_nested_value($form_state['values'], $element['#parents']);
    $method_data = &$form_state['payment']->method_data;

    $method_data['holder'] = $values['holder'];
    $method_data['account_or_ibanbic'] = $values['account_or_ibanbic'];

    if (empty($values['holder']) == TRUE) {
      form_error($element['holder'], t('Please enter the name of the account holder.'));
    }

    if ($method_data['account_or_ibanbic'] == 'account') {
      $method_data['account']   = $values['account']['account'];
      $method_data['bank_code'] = $values['account']['bank_code'];
      $method_data['country']   = $values['account']['country'];

      if (preg_match('/^[0-9]{4,11}$/', $values['account']['account']) != 1) {
        form_error($element['account']['account'], t('Please enter a valid bank account number.'));
      }

      if (preg_match('/^[0-9]{3,5}$/', $values['account']['bank_code']) != 1) {
        form_error($element['account']['bank_code'], t('Please enter a valid bank code number.'));
      }

    } else {
      $method_data['iban'] = $values['ibanbic']['iban'];
      $method_data['bic']  = $values['ibanbic']['bic'];

      require_once(dirname(__FILE__) . '/../php-iban.php');
      if (verify_iban($values['ibanbic']['iban']) == FALSE) {
        form_error($element['ibanbic']['iban'], t('Please enter a valid IBAN.'));
      }

      if (preg_match('/^[a-z]{6}[2-9a-z][0-9a-np-z](|xxx|[0-9a-wyz][0-9a-z]{2})$/i', $values['ibanbic']['bic']) != 1) {
        form_error($element['ibanbic']['bic'], t('Please enter a valid BIC.'));
      }
    }
  }
}
